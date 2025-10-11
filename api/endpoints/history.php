<?php

class HistoryEndpoints
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
        $this->seedDefaultSections();
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
            $this->handleSingle((int) $first, $method);
            return;
        }

        switch ($method) {
            case 'GET':
                $this->getPublicSections();
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
                    $this->getAdminSections();
                } else {
                    $this->getSection((int) $target, true);
                }
                return;
            default:
                errorResponse('Method Not Allowed', 405);
        }
    }

    private function handleSingle(int $id, string $method): void
    {
        switch ($method) {
            case 'GET':
                $this->getSection($id, false);
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

    private function handleReorder(string $method): void
    {
        if ($method !== 'PUT') {
            errorResponse('Method Not Allowed', 405);
        }

        AuthMiddleware::check();
        $payload = $this->readJson();
        if (!isset($payload['sections']) || !is_array($payload['sections'])) {
            errorResponse('sections payload is required', 400);
        }

        foreach ($payload['sections'] as $section) {
            if (!isset($section['id'])) {
                continue;
            }

            $position = isset($section['position']) ? (int) $section['position'] : null;
            if ($position === null) {
                continue;
            }

            $this->db->query(
                "UPDATE history_sections SET position = :position, updated_at = NOW() WHERE id = :id",
                [
                    'position' => $position,
                    'id' => (int) $section['id'],
                ]
            );
        }

        jsonResponse(['success' => true, 'message' => 'Sections reordered successfully']);
    }

    private function getPublicSections(): void
    {
        $lang = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : 'bg';

        $rows = $this->db->fetchAll(
            "SELECT * FROM history_sections WHERE is_active = 1 ORDER BY position ASC, id ASC"
        );

        $sections = array_map(function ($row) use ($lang) {
            return $this->transformForPublic($row, $lang);
        }, $rows);

        jsonResponse(['success' => true, 'sections' => $sections]);
    }

    private function getAdminSections(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM history_sections ORDER BY position ASC, id ASC"
        );

        $sections = array_map([$this, 'transformForAdmin'], $rows);

        jsonResponse(['success' => true, 'sections' => $sections]);
    }

    private function getSection(int $id, bool $adminView): void
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM history_sections WHERE id = ?",
            [$id]
        );

        if (!$row) {
            errorResponse('History section not found', 404);
        }

        if ($adminView) {
            jsonResponse(['success' => true, 'section' => $this->transformForAdmin($row)]);
            return;
        }

        $lang = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : 'bg';
        jsonResponse(['success' => true, 'section' => $this->transformForPublic($row, $lang)]);
    }

    private function createSection(): void
    {
        $payload = $this->readJson();
        $sectionKey = isset($payload['section_key']) ? trim((string) $payload['section_key']) : '';
        if ($sectionKey === '') {
            errorResponse('section_key is required', 422);
        }

        $exists = $this->db->fetchOne(
            "SELECT id FROM history_sections WHERE section_key = ?",
            [$sectionKey]
        );

        if ($exists) {
            errorResponse('Section key already exists', 409);
        }

        $position = isset($payload['position']) ? (int) $payload['position'] : $this->nextPosition();

        $data = [
            'section_key' => $sectionKey,
            'title_bg' => $this->nullable($payload['title_bg'] ?? null),
            'title_en' => $this->nullable($payload['title_en'] ?? null),
            'content_bg' => $this->nullable($payload['content_bg'] ?? null),
            'content_en' => $this->nullable($payload['content_en'] ?? null),
            'image_url' => $this->nullable($payload['image_url'] ?? null),
            'position' => $position,
            'is_active' => $this->boolToInt($payload['is_active'] ?? true),
        ];

        $id = $this->db->insert('history_sections', $data);
        $row = $this->db->fetchOne("SELECT * FROM history_sections WHERE id = ?", [$id]);

        jsonResponse([
            'success' => true,
            'message' => 'History section created successfully',
            'section' => $this->transformForAdmin($row),
        ], 201);
    }

    private function updateSection(int $id): void
    {
        $existing = $this->db->fetchOne(
            "SELECT * FROM history_sections WHERE id = ?",
            [$id]
        );

        if (!$existing) {
            errorResponse('History section not found', 404);
        }

        $payload = $this->readJson();
        $updates = [];

        $fields = ['section_key', 'title_bg', 'title_en', 'content_bg', 'content_en', 'image_url', 'position', 'is_active'];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }
            $value = $payload[$field];
            if (in_array($field, ['title_bg', 'title_en', 'content_bg', 'content_en', 'image_url', 'section_key'], true)) {
                $value = $this->nullable($value);
            }
            if ($field === 'is_active') {
                $value = $this->boolToInt($value);
            }
            if ($field === 'position' && $value !== null) {
                $value = (int) $value;
            }

            if ($field === 'section_key' && $value !== null) {
                $dup = $this->db->fetchOne(
                    "SELECT id FROM history_sections WHERE section_key = ? AND id <> ?",
                    [$value, $id]
                );
                if ($dup) {
                    errorResponse('Another section already uses this key', 409);
                }
            }

            $updates[$field] = $value;
        }

        if (empty($updates)) {
            jsonResponse([
                'success' => true,
                'message' => 'No changes supplied',
                'section' => $this->transformForAdmin($existing),
            ]);
        }

        $setParts = [];
        foreach ($updates as $col => $value) {
            $setParts[] = "{$col} = :{$col}";
        }
        $updates['id'] = $id;

        $this->db->query(
            "UPDATE history_sections SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = :id",
            $updates
        );

        $row = $this->db->fetchOne("SELECT * FROM history_sections WHERE id = ?", [$id]);

        jsonResponse([
            'success' => true,
            'message' => 'History section updated successfully',
            'section' => $this->transformForAdmin($row),
        ]);
    }

    private function deleteSection(int $id): void
    {
        $deleted = $this->db->delete('history_sections', 'id = :id', ['id' => $id]);

        if ($deleted === 0) {
            errorResponse('History section not found', 404);
        }

        jsonResponse(['success' => true, 'message' => 'History section deleted successfully']);
    }

    private function transformForPublic(array $row, string $lang): array
    {
        $isEnglish = $lang === 'en';

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
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    private function ensureSchema(): void
    {
        $exists = $this->db->fetchColumn(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'history_sections'"
        );

        if ((int) $exists === 0) {
            $this->db->query(
                "CREATE TABLE history_sections (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    section_key VARCHAR(100) NOT NULL UNIQUE,
                    title_bg VARCHAR(500) NULL,
                    title_en VARCHAR(500) NULL,
                    content_bg LONGTEXT NULL,
                    content_en LONGTEXT NULL,
                    image_url VARCHAR(500) NULL,
                    position INT NOT NULL DEFAULT 0,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_position (position),
                    INDEX idx_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } else {
            $columns = $this->db->fetchAll("SHOW COLUMNS FROM history_sections");
            $existing = [];
            foreach ($columns as $column) {
                $existing[$column['Field']] = true;
            }

            $required = [
                'section_key' => "ALTER TABLE history_sections ADD COLUMN section_key VARCHAR(100) NOT NULL UNIQUE",
                'title_bg' => "ALTER TABLE history_sections ADD COLUMN title_bg VARCHAR(500) NULL",
                'title_en' => "ALTER TABLE history_sections ADD COLUMN title_en VARCHAR(500) NULL",
                'content_bg' => "ALTER TABLE history_sections ADD COLUMN content_bg LONGTEXT NULL",
                'content_en' => "ALTER TABLE history_sections ADD COLUMN content_en LONGTEXT NULL",
                'image_url' => "ALTER TABLE history_sections ADD COLUMN image_url VARCHAR(500) NULL",
                'position' => "ALTER TABLE history_sections ADD COLUMN position INT NOT NULL DEFAULT 0",
                'is_active' => "ALTER TABLE history_sections ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1",
                'created_at' => "ALTER TABLE history_sections ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
                'updated_at' => "ALTER TABLE history_sections ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            ];

            foreach ($required as $column => $sql) {
                if (!isset($existing[$column])) {
                    $this->db->query($sql);
                }
            }
        }
    }

    private function seedDefaultSections(): void
    {
        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM history_sections");
        if ((int) $count > 0) {
            return;
        }

        $defaults = [
            [
                'section_key' => 'history-intro',
                'title_bg' => 'История на училището',
                'title_en' => 'School History',
                'content_bg' => 'Нашето училище има богата история и традиции в образованието.',
                'content_en' => 'Our school has a rich history and tradition in education.',
                'image_url' => null,
                'position' => 1,
                'is_active' => 1,
            ],
            [
                'section_key' => 'history-mission',
                'title_bg' => 'Мисия',
                'title_en' => 'Mission',
                'content_bg' => 'Да възпитаваме отговорни граждани с любов към знанието и родината.',
                'content_en' => 'To educate responsible citizens with love for knowledge and homeland.',
                'image_url' => null,
                'position' => 2,
                'is_active' => 1,
            ],
        ];

        foreach ($defaults as $section) {
            $this->db->insert('history_sections', $section);
        }
    }

    private function nextPosition(): int
    {
        $max = $this->db->fetchColumn("SELECT MAX(position) FROM history_sections");
        return $max !== null ? ((int) $max) + 1 : 1;
    }

    private function readJson(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            errorResponse('Invalid JSON payload', 400);
        }

        return $decoded;
    }

    private function nullable($value): ?string
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
}
