<?php

class HistoryEndpoints
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            errorResponse('Database connection failed: ' . $this->conn->connect_error, 500);
        }

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

        if ($method === 'GET') {
            $this->getPublicSections();
            return;
        }

        if ($method === 'POST') {
            AuthMiddleware::check();
            $this->createSection();
            return;
        }

        errorResponse('Method Not Allowed', 405);
    }

    private function handleAdmin(array $segments, string $method): void
    {
        AuthMiddleware::check();
        $target = $segments[0] ?? '';

        if ($method !== 'GET') {
            errorResponse('Method Not Allowed', 405);
        }

        if ($target === '') {
            $this->getAdminSections();
        } else {
            $this->getSection((int) $target, true);
        }
    }

    private function handleReorder(string $method): void
    {
        if ($method !== 'PUT') {
            errorResponse('Method Not Allowed', 405);
        }

        AuthMiddleware::check();
        $data = $this->readJson();

        if (!isset($data['sections']) || !is_array($data['sections'])) {
            errorResponse('Invalid payload supplied', 400);
        }

        foreach ($data['sections'] as $section) {
            if (!isset($section['id'])) {
                continue;
            }

            $position = isset($section['position']) ? (int) $section['position'] : null;
            if ($position === null) {
                continue;
            }

            $stmt = $this->conn->prepare("UPDATE history_sections SET position = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('ii', $position, $section['id']);
                $stmt->execute();
                $stmt->close();
            }
        }

        jsonResponse(['success' => true, 'message' => 'History sections reordered successfully']);
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

    private function getPublicSections(): void
    {
        $language = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : 'bg';
        $stmt = $this->conn->prepare("SELECT * FROM history_sections WHERE is_active = 1 ORDER BY position ASC, id ASC");
        $sections = $this->fetchAll($stmt);
        if ($stmt) {
            $stmt->close();
        }

        $content = array_map(function ($row) use ($language) {
            return $this->transformForPublic($row, $language);
        }, $sections);

        jsonResponse(['success' => true, 'content' => $content]);
    }

    private function getAdminSections(): void
    {
        $stmt = $this->conn->prepare("SELECT * FROM history_sections ORDER BY position ASC, id ASC");
        $sections = $this->fetchAll($stmt);
        if ($stmt) {
            $stmt->close();
        }

        $content = array_map([$this, 'transformForAdmin'], $sections);
        jsonResponse(['success' => true, 'content' => $content]);
    }

    private function getSection(int $id, bool $adminView): void
    {
        $stmt = $this->conn->prepare("SELECT * FROM history_sections WHERE id = ?");
        $stmt->bind_param('i', $id);
        $rows = $this->fetchAll($stmt);
        $stmt->close();

        if (empty($rows)) {
            errorResponse('History section not found', 404);
        }

        $row = $rows[0];

        if ($adminView) {
            jsonResponse(['success' => true, 'content' => $this->transformForAdmin($row)]);
            return;
        }

        $language = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : 'bg';
        jsonResponse(['success' => true, 'content' => $this->transformForPublic($row, $language)]);
    }

    private function createSection(): void
    {
        $data = $this->readJson();

        $sectionKey = isset($data['section_key']) ? trim((string) $data['section_key']) : '';
        if ($sectionKey === '') {
            errorResponse('section_key is required', 422);
        }

        $stmt = $this->conn->prepare("SELECT id FROM history_sections WHERE section_key = ?");
        $stmt->bind_param('s', $sectionKey);
        $existing = $this->fetchAll($stmt);
        $stmt->close();

        if (!empty($existing)) {
            errorResponse('Section key already exists', 409);
        }

        $position = isset($data['position']) ? (int) $data['position'] : $this->determineNextPosition();

        $stmt = $this->conn->prepare(
            "INSERT INTO history_sections (
                section_key, title_bg, title_en, content_bg, content_en, image_url, position, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $titleBg = $this->nullableString($data['title_bg'] ?? null);
        $titleEn = $this->nullableString($data['title_en'] ?? null);
        $contentBg = $this->nullableString($data['content_bg'] ?? null);
        $contentEn = $this->nullableString($data['content_en'] ?? null);
        $imageUrl = $this->nullableString($data['image_url'] ?? null);
        $isActive = $this->boolToInt($data['is_active'] ?? true);

        $stmt->bind_param(
            'ssssssii',
            $sectionKey,
            $titleBg,
            $titleEn,
            $contentBg,
            $contentEn,
            $imageUrl,
            $position,
            $isActive
        );
        $stmt->execute();
        $stmt->close();

        $this->getSection((int) $this->conn->insert_id, true);
    }

    private function updateSection(int $id): void
    {
        $stmt = $this->conn->prepare("SELECT * FROM history_sections WHERE id = ?");
        $stmt->bind_param('i', $id);
        $existing = $this->fetchAll($stmt);
        $stmt->close();

        if (empty($existing)) {
            errorResponse('History section not found', 404);
        }

        $data = $this->readJson();

        $updates = [];
        $params = [];
        $types = '';

        $fields = [
            'section_key' => 's',
            'title_bg' => 's',
            'title_en' => 's',
            'content_bg' => 's',
            'content_en' => 's',
            'image_url' => 's',
            'position' => 'i',
            'is_active' => 'i',
        ];

        foreach ($fields as $field => $type) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if (in_array($field, ['title_bg', 'title_en', 'content_bg', 'content_en', 'image_url', 'section_key'], true)) {
                    $value = $this->nullableString($value);
                }
                if ($field === 'position' && $value !== null) {
                    $value = (int) $value;
                }
                if ($field === 'is_active') {
                    $value = $this->boolToInt($value);
                }

                $updates[] = "$field = ?";
                $params[] = $value;
                $types .= $type;
            }
        }

        if (empty($updates)) {
            $this->getSection($id, true);
            return;
        }

        $types .= 'i';
        $params[] = $id;

        $sql = "UPDATE history_sections SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();

        $this->getSection($id, true);
    }

    private function deleteSection(int $id): void
    {
        $stmt = $this->conn->prepare("DELETE FROM history_sections WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            $stmt->close();
            errorResponse('History section not found', 404);
        }

        $stmt->close();
        jsonResponse(['success' => true, 'message' => 'History section deleted successfully']);
    }

    private function ensureSchema(): void
    {
        $this->conn->query(
            "CREATE TABLE IF NOT EXISTS history_sections (
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
    }

    private function seedDefaultSections(): void
    {
        $result = $this->conn->query("SELECT COUNT(*) AS count FROM history_sections");
        $row = $result ? $result->fetch_assoc() : ['count' => 0];
        if ((int) $row['count'] > 0) {
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

        $stmt = $this->conn->prepare(
            "INSERT INTO history_sections (
                section_key, title_bg, title_en, content_bg, content_en, image_url, position, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($defaults as $section) {
            $stmt->bind_param(
                'ssssssii',
                $section['section_key'],
                $section['title_bg'],
                $section['title_en'],
                $section['content_bg'],
                $section['content_en'],
                $section['image_url'],
                $section['position'],
                $section['is_active']
            );
            $stmt->execute();
        }

        $stmt->close();
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
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    private function determineNextPosition(): int
    {
        $result = $this->conn->query("SELECT MAX(position) AS max_position FROM history_sections");
        if (!$result) {
            return 1;
        }

        $row = $result->fetch_assoc();
        $result->free();

        return $row && $row['max_position'] !== null ? ((int) $row['max_position']) + 1 : 1;
    }

    private function fetchAll(?mysqli_stmt $stmt): array
    {
        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        if ($result) {
            $result->free();
        }

        return $rows;
    }

    private function readJson(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            errorResponse('Invalid JSON payload', 400);
        }

        return $data;
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
}
