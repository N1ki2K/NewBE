<?php

class PatronEndpoints
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
        $this->seedDefaultContent();
    }

    public function handle(array $segments, string $method): void
    {
        $first = $segments[0] ?? '';

        if ($first === 'admin') {
            $this->handleAdmin(array_slice($segments, 1), $method);
            return;
        }

        if ($first === 'reorder') {
            $this->handleReorder($method);
            return;
        }

        if ($first !== '') {
            $this->handleSingle($first, array_slice($segments, 1), $method);
            return;
        }

        switch ($method) {
            case 'GET':
                $this->getPublicContent();
                return;
            case 'POST':
                AuthMiddleware::check();
                $this->createSection();
                return;
            default:
                errorResponse('Method Not Allowed', 405);
        }
    }

    private function handleAdmin(array $segments, string $method): void
    {
        AuthMiddleware::check();
        $target = $segments[0] ?? '';

        switch ($method) {
            case 'GET':
                if ($target === '') {
                    $this->getAdminContent();
                } else {
                    $this->getSection((int) $target, true);
                }
                return;
            default:
                errorResponse('Method Not Allowed', 405);
        }
    }

    private function handleReorder(string $method): void
    {
        if ($method !== 'PUT') {
            errorResponse('Method Not Allowed', 405);
        }

        AuthMiddleware::check();
        $payload = $this->readJsonBody();

        if (!isset($payload['content']) || !is_array($payload['content'])) {
            errorResponse('Invalid payload supplied', 400);
        }

        foreach ($payload['content'] as $item) {
            if (!isset($item['id'])) {
                continue;
            }
            $position = isset($item['position']) ? (int) $item['position'] : null;
            if ($position === null) {
                continue;
            }

            $this->db->query(
                "UPDATE patron_sections SET position = :position, updated_at = NOW() WHERE id = :id",
                [
                    'position' => $position,
                    'id' => (int) $item['id'],
                ]
            );
        }

        jsonResponse(['success' => true, 'message' => 'Patron sections reordered successfully']);
    }

    private function handleSingle(string $identifier, array $segments, string $method): void
    {
        $id = (int) $identifier;

        switch ($method) {
            case 'GET':
                // Allow optional lang query for a language-specific view
                $adminView = isset($_GET['admin']) ? filter_var($_GET['admin'], FILTER_VALIDATE_BOOLEAN) : false;
                $this->getSection($id, $adminView);
                return;

            case 'PUT':
                AuthMiddleware::check();
                $this->updateSection($id);
                return;

            case 'DELETE':
                AuthMiddleware::check();
                $this->deleteSection($id);
                return;

            default:
                errorResponse('Method Not Allowed', 405);
        }
    }

    private function getPublicContent(): void
    {
        $language = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : 'bg';

        $rows = $this->db->fetchAll(
            "SELECT * FROM patron_sections WHERE is_active = 1 ORDER BY position ASC, id ASC"
        );

        $content = array_map(function ($row) use ($language) {
            return $this->transformForPublic($row, $language);
        }, $rows);

        jsonResponse([
            'success' => true,
            'content' => $content,
        ]);
    }

    private function getAdminContent(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM patron_sections ORDER BY position ASC, id ASC"
        );

        $content = array_map([$this, 'transformForAdmin'], $rows);

        jsonResponse([
            'success' => true,
            'content' => $content,
        ]);
    }

    private function getSection(int $id, bool $adminView = false): void
    {
        $row = $this->db->fetchOne("SELECT * FROM patron_sections WHERE id = ?", [$id]);

        if (!$row) {
            errorResponse('Patron section not found', 404);
        }

        $language = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : 'bg';
        $data = $adminView ? $this->transformForAdmin($row) : $this->transformForPublic($row, $language);

        jsonResponse([
            'success' => true,
            'content' => $data,
        ]);
    }

    private function createSection(): void
    {
        $payload = $this->readJsonBody();

        $sectionKey = isset($payload['section_key']) ? trim((string) $payload['section_key']) : '';
        if ($sectionKey === '') {
            errorResponse('section_key is required', 422);
        }

        $existing = $this->db->fetchOne(
            "SELECT id FROM patron_sections WHERE section_key = ?",
            [$sectionKey]
        );

        if ($existing) {
            errorResponse('Section key already exists', 409);
        }

        $position = isset($payload['position']) ? (int) $payload['position'] : $this->determineNextPosition();

        $data = [
            'section_key' => $sectionKey,
            'title_bg' => $this->nullableString($payload['title_bg'] ?? null),
            'title_en' => $this->nullableString($payload['title_en'] ?? null),
            'content_bg' => $this->nullableString($payload['content_bg'] ?? null),
            'content_en' => $this->nullableString($payload['content_en'] ?? null),
            'image_url' => $this->nullableString($payload['image_url'] ?? null),
            'position' => $position,
            'is_active' => $this->boolToInt($payload['is_active'] ?? true),
        ];

        $this->db->insert('patron_sections', $data);

        $row = $this->db->fetchOne(
            "SELECT * FROM patron_sections WHERE section_key = ?",
            [$sectionKey]
        );

        jsonResponse([
            'success' => true,
            'message' => 'Patron section created successfully',
            'content' => $this->transformForAdmin($row),
        ], 201);
    }

    private function updateSection(int $id): void
    {
        $existing = $this->db->fetchOne("SELECT * FROM patron_sections WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Patron section not found', 404);
        }

        $payload = $this->readJsonBody();
        if (empty($payload)) {
            jsonResponse([
                'success' => true,
                'message' => 'No changes supplied',
                'content' => $this->transformForAdmin($existing),
            ]);
        }

        $allowedFields = [
            'section_key',
            'title_bg',
            'title_en',
            'content_bg',
            'content_en',
            'image_url',
            'position',
            'is_active',
        ];

        $updateData = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $payload)) {
                $updateData[$field] = $payload[$field];
            }
        }

        if (isset($updateData['section_key'])) {
            $updateData['section_key'] = trim((string) $updateData['section_key']);
            if ($updateData['section_key'] === '') {
                errorResponse('section_key cannot be empty', 422);
            }

            $duplicate = $this->db->fetchOne(
                "SELECT id FROM patron_sections WHERE section_key = ? AND id != ?",
                [$updateData['section_key'], $id]
            );

            if ($duplicate) {
                errorResponse('Another section already uses this key', 409);
            }
        }

        if (isset($updateData['title_bg'])) {
            $updateData['title_bg'] = $this->nullableString($updateData['title_bg']);
        }
        if (isset($updateData['title_en'])) {
            $updateData['title_en'] = $this->nullableString($updateData['title_en']);
        }
        if (isset($updateData['content_bg'])) {
            $updateData['content_bg'] = $this->nullableString($updateData['content_bg']);
        }
        if (isset($updateData['content_en'])) {
            $updateData['content_en'] = $this->nullableString($updateData['content_en']);
        }
        if (isset($updateData['image_url'])) {
            $updateData['image_url'] = $this->nullableString($updateData['image_url']);
        }
        if (isset($updateData['position'])) {
            $updateData['position'] = (int) $updateData['position'];
        }
        if (isset($updateData['is_active'])) {
            $updateData['is_active'] = $this->boolToInt($updateData['is_active']);
        }

        if (empty($updateData)) {
            jsonResponse([
                'success' => true,
                'message' => 'No changes supplied',
                'content' => $this->transformForAdmin($existing),
            ]);
        }

        $setParts = [];
        foreach (array_keys($updateData) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }

        $updateData['id'] = $id;

        $this->db->query(
            "UPDATE patron_sections SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = :id",
            $updateData
        );

        $updated = $this->db->fetchOne("SELECT * FROM patron_sections WHERE id = ?", [$id]);

        jsonResponse([
            'success' => true,
            'message' => 'Patron section updated successfully',
            'content' => $this->transformForAdmin($updated),
        ]);
    }

    private function deleteSection(int $id): void
    {
        $deleted = $this->db->delete('patron_sections', 'id = :id', ['id' => $id]);

        if ($deleted === 0) {
            errorResponse('Patron section not found', 404);
        }

        jsonResponse([
            'success' => true,
            'message' => 'Patron section deleted successfully',
        ]);
    }

    private function ensureSchema(): void
    {
        $tableCount = (int) $this->db->fetchColumn(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'patron_sections'"
        );

        if ($tableCount === 0) {
            $this->createPatronSectionsTable();
        } else {
            $this->upgradePatronSectionsTable();
        }
    }

    private function createPatronSectionsTable(): void
    {
        $this->db->query(
            "CREATE TABLE patron_sections (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_key VARCHAR(100) NOT NULL,
                title_bg VARCHAR(500) NULL,
                title_en VARCHAR(500) NULL,
                content_bg LONGTEXT NULL,
                content_en LONGTEXT NULL,
                image_url VARCHAR(500) NULL,
                position INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_section_key (section_key),
                INDEX idx_position (position),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function upgradePatronSectionsTable(): void
    {
        $columns = $this->db->fetchAll('SHOW COLUMNS FROM patron_sections');
        $existing = [];
        foreach ($columns as $column) {
            if (isset($column['Field'])) {
                $existing[$column['Field']] = true;
            }
        }

        $alterStatements = [
            'section_key' => "ALTER TABLE patron_sections ADD COLUMN section_key VARCHAR(100) NOT NULL",
            'title_bg' => "ALTER TABLE patron_sections ADD COLUMN title_bg VARCHAR(500) NULL",
            'title_en' => "ALTER TABLE patron_sections ADD COLUMN title_en VARCHAR(500) NULL",
            'content_bg' => "ALTER TABLE patron_sections ADD COLUMN content_bg LONGTEXT NULL",
            'content_en' => "ALTER TABLE patron_sections ADD COLUMN content_en LONGTEXT NULL",
            'image_url' => "ALTER TABLE patron_sections ADD COLUMN image_url VARCHAR(500) NULL",
            'position' => "ALTER TABLE patron_sections ADD COLUMN position INT NOT NULL DEFAULT 0",
            'is_active' => "ALTER TABLE patron_sections ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1",
            'created_at' => "ALTER TABLE patron_sections ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
            'updated_at' => "ALTER TABLE patron_sections ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        ];

        foreach ($alterStatements as $column => $sql) {
            if (!isset($existing[$column])) {
                $this->db->query($sql);
            }
        }

        $indexes = $this->db->fetchAll('SHOW INDEX FROM patron_sections');
        $existingIndexes = [];
        foreach ($indexes as $index) {
            if (isset($index['Key_name'])) {
                $existingIndexes[$index['Key_name']] = true;
            }
        }

        if (!isset($existingIndexes['uniq_section_key'])) {
            $this->db->query("CREATE UNIQUE INDEX uniq_section_key ON patron_sections (section_key)");
        }
        if (!isset($existingIndexes['idx_position'])) {
            $this->db->query("CREATE INDEX idx_position ON patron_sections (position)");
        }
        if (!isset($existingIndexes['idx_active'])) {
            $this->db->query("CREATE INDEX idx_active ON patron_sections (is_active)");
        }
    }

    private function seedDefaultContent(): void
    {
        $count = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM patron_sections");
        if ($count > 0) {
            return;
        }

        $defaults = $this->getDefaultSections();
        foreach ($defaults as $item) {
            $this->db->insert('patron_sections', $item);
        }
    }

    private function getDefaultSections(): array
    {
        return [
            [
                'section_key' => 'quote',
                'title_bg' => null,
                'title_en' => null,
                'content_bg' => '"Образованието е най-мощното оръжие, което можете да използвате, за да промените света." - Нелсън Мандела',
                'content_en' => '"Education is the most powerful weapon which you can use to change the world." - Nelson Mandela',
                'image_url' => null,
                'position' => 1,
                'is_active' => 1,
            ],
            [
                'section_key' => 'biography_p1',
                'title_bg' => null,
                'title_en' => null,
                'content_bg' => 'Нашето училище носи името на изтъкнатия български просветител и общественик Васил Левски (1837-1873). Известен като Апостолът на свободата, Васил Левски посвети целия си живот на борбата за освобождението на България от османско владичество.',
                'content_en' => 'Our school bears the name of the prominent Bulgarian educator and public figure Vasil Levski (1837-1873). Known as the Apostle of Freedom, Vasil Levski dedicated his entire life to the struggle for the liberation of Bulgaria from Ottoman rule.',
                'image_url' => null,
                'position' => 2,
                'is_active' => 1,
            ],
            [
                'section_key' => 'biography_p2',
                'title_bg' => 'Ранен живот',
                'title_en' => 'Early Life',
                'content_bg' => 'Роден в град Карлово като Васил Иванов Кунчев, той получава начално образование в родния си град, а по-късно учи в манастира в Сопот. Още от млади години показва извънредни способности и страст към знанието.',
                'content_en' => 'Born in the town of Karlovo as Vasil Ivanov Kunchev, he received his primary education in his hometown, and later studied at the monastery in Sopot. From a young age, he showed extraordinary abilities and passion for knowledge.',
                'image_url' => null,
                'position' => 3,
                'is_active' => 1,
            ],
            [
                'section_key' => 'biography_p3',
                'title_bg' => 'Революционна дейност',
                'title_en' => 'Revolutionary Activity',
                'content_bg' => 'Васил Левски създава мрежа от революционни комитети из цялата страна, работейки неуморно за организирането на въстание срещу османската власт. Неговата визия беше за свободна и демократична България, където всички граждани биха имали равни права.',
                'content_en' => 'Vasil Levski created a network of revolutionary committees throughout the country, working tirelessly to organize an uprising against Ottoman rule. His vision was for a free and democratic Bulgaria, where all citizens would have equal rights.',
                'image_url' => null,
                'position' => 4,
                'is_active' => 1,
            ],
            [
                'section_key' => 'biography_p4',
                'title_bg' => 'Принципи и идеали',
                'title_en' => 'Principles and Ideals',
                'content_bg' => 'Левски вярваше в чистата република и в равенството на всички български граждани, независимо от етническия им произход или религия. Той отхвърляше всякаква чуждестранна намеса и настояваше, че България трябва да бъде освободена чрез собствени усилия.',
                'content_en' => 'Levski believed in a pure republic and in the equality of all Bulgarian citizens, regardless of their ethnic origin or religion. He rejected any foreign intervention and insisted that Bulgaria must be liberated through its own efforts.',
                'image_url' => null,
                'position' => 5,
                'is_active' => 1,
            ],
            [
                'section_key' => 'biography_p5',
                'title_bg' => null,
                'title_en' => null,
                'content_bg' => 'На 19 февруари 1873 година, Васил Левски беше заловен от османските власти и обесен близо до София. Неговата саможертва и принципност го превърнаха в най-почитаната фигура в българската история. Днес той е символ на свободата, справедливостта и отдадеността на високи идеали.',
                'content_en' => 'On February 19, 1873, Vasil Levski was captured by Ottoman authorities and hanged near Sofia. His self-sacrifice and principles made him the most revered figure in Bulgarian history. Today he is a symbol of freedom, justice, and dedication to high ideals.',
                'image_url' => null,
                'position' => 6,
                'is_active' => 1,
            ],
            [
                'section_key' => 'legacy_title',
                'title_bg' => null,
                'title_en' => null,
                'content_bg' => 'Наследство и значение за училището',
                'content_en' => 'Legacy and Significance for the School',
                'image_url' => null,
                'position' => 7,
                'is_active' => 1,
            ],
            [
                'section_key' => 'legacy_content',
                'title_bg' => null,
                'title_en' => null,
                'content_bg' => 'Като носим името на Васил Левски, ние се стремим да възпитаваме в нашите ученици същите ценности, които той отстояваше: свобода, равенство, справедливост и любов към родината. Неговият пример ни вдъхновява да работим за създаването на по-добро бъдеще за нашата страна и да възпитаваме граждани с високи морални принципи и патриотичен дух.',
                'content_en' => 'By bearing the name of Vasil Levski, we strive to instill in our students the same values he upheld: freedom, equality, justice, and love for the homeland. His example inspires us to work towards creating a better future for our country and to educate citizens with high moral principles and patriotic spirit.',
                'image_url' => null,
                'position' => 8,
                'is_active' => 1,
            ],
            [
                'section_key' => 'image_main',
                'title_bg' => null,
                'title_en' => null,
                'content_bg' => null,
                'content_en' => null,
                'image_url' => '/public/uplods/hardcode/Kolio_Ganchev.jpg',
                'position' => 9,
                'is_active' => 1,
            ],
            [
                'section_key' => 'image_caption',
                'title_bg' => null,
                'title_en' => null,
                'content_bg' => 'Васил Левски - Апостолът на свободата',
                'content_en' => 'Vasil Levski - The Apostle of Freedom',
                'image_url' => null,
                'position' => 10,
                'is_active' => 1,
            ],
        ];
    }

    private function transformForPublic(array $row, string $language): array
    {
        $isEnglish = $language === 'en';

        $title = $isEnglish
            ? ($row['title_en'] ?? $row['title_bg'])
            : ($row['title_bg'] ?? $row['title_en']);

        $content = $isEnglish
            ? ($row['content_en'] ?? $row['content_bg'])
            : ($row['content_bg'] ?? $row['content_en']);

        return [
            'id' => (int) $row['id'],
            'section_key' => $row['section_key'],
            'title' => $title,
            'content' => $content,
            'image_url' => $row['image_url'] ?? null,
            'position' => (int) ($row['position'] ?? 0),
        ];
    }

    private function transformForAdmin(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'section_key' => $row['section_key'],
            'title_bg' => $row['title_bg'] ?? null,
            'title_en' => $row['title_en'] ?? null,
            'content_bg' => $row['content_bg'] ?? null,
            'content_en' => $row['content_en'] ?? null,
            'image_url' => $row['image_url'] ?? null,
            'position' => (int) ($row['position'] ?? 0),
            'is_active' => (bool) ($row['is_active'] ?? 1),
            'created_at' => $this->formatDate($row['created_at'] ?? null),
            'updated_at' => $this->formatDate($row['updated_at'] ?? null),
        ];
    }

    private function determineNextPosition(): int
    {
        $value = $this->db->fetchColumn("SELECT MAX(position) FROM patron_sections");
        if ($value === false || $value === null) {
            return 1;
        }

        return ((int) $value) + 1;
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function boolToInt($value): int
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    private function formatDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime((string) $value);
        if ($timestamp === false) {
            return null;
        }

        return date(DATE_ATOM, $timestamp);
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            errorResponse('Invalid JSON payload supplied', 400);
        }

        return $payload;
    }
}
