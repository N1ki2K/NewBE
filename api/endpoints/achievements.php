<?php

class AchievementsEndpoints
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
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
            $this->getPublicList();
            return;
        }

        if ($method === 'POST') {
            AuthMiddleware::check();
            $this->createAchievement();
            return;
        }

        errorResponse('Method Not Allowed', 405);
    }

    private function handleAdmin(array $segments, string $method): void
    {
        AuthMiddleware::check();
        $target = $segments[0] ?? '';

        switch ($method) {
            case 'GET':
                if ($target === '') {
                    $this->getAdminList();
                } else {
                    $this->getAchievement((int) $target, true);
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
                $this->getAchievement($id, false);
                return;
            case 'PUT':
                AuthMiddleware::check();
                $this->updateAchievement($id);
                return;
            case 'DELETE':
                AuthMiddleware::check();
                $this->deleteAchievement($id);
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
        if (!isset($payload['achievements']) || !is_array($payload['achievements'])) {
            errorResponse('achievements payload is required', 400);
        }

        foreach ($payload['achievements'] as $item) {
            if (!isset($item['id'])) {
                continue;
            }
            $position = isset($item['position']) ? (int) $item['position'] : null;
            if ($position === null) {
                continue;
            }
            $this->db->query(
                "UPDATE achievements SET position = :position, updated_at = NOW() WHERE id = :id",
                ['position' => $position, 'id' => (int) $item['id']]
            );
        }

        jsonResponse(['success' => true, 'message' => 'Achievements reordered successfully']);
    }

    private function getPublicList(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM achievements WHERE is_active = 1 ORDER BY position ASC, id ASC"
        );

        $achievements = array_map([$this, 'mapPublic'], $rows);
        jsonResponse(['success' => true, 'achievements' => $achievements]);
    }

    private function getAdminList(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM achievements ORDER BY position ASC, id ASC"
        );

        $achievements = array_map([$this, 'mapAdmin'], $rows);
        jsonResponse(['success' => true, 'achievements' => $achievements]);
    }

    private function getAchievement(int $id, bool $admin): void
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM achievements WHERE id = ?",
            [$id]
        );

        if (!$row) {
            errorResponse('Achievement not found', 404);
        }

        jsonResponse([
            'success' => true,
            'achievement' => $admin ? $this->mapAdmin($row) : $this->mapPublic($row),
        ]);
    }

    private function createAchievement(): void
    {
        $payload = $this->readJson();
        $title = isset($payload['title']) ? trim((string) $payload['title']) : '';
        if ($title === '') {
            errorResponse('title is required', 422);
        }

        $data = [
            'title' => $title,
            'description' => $this->nullable($payload['description'] ?? null),
            'year' => isset($payload['year']) ? (int) $payload['year'] : null,
            'position' => isset($payload['position']) ? (int) $payload['position'] : $this->nextPosition(),
            'is_active' => $this->boolToInt($payload['is_active'] ?? true),
        ];

        $id = $this->db->insert('achievements', $data);
        $row = $this->db->fetchOne("SELECT * FROM achievements WHERE id = ?", [$id]);

        jsonResponse([
            'success' => true,
            'message' => 'Achievement created successfully',
            'achievement' => $this->mapAdmin($row),
        ], 201);
    }

    private function updateAchievement(int $id): void
    {
        $existing = $this->db->fetchOne("SELECT * FROM achievements WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Achievement not found', 404);
        }

        $payload = $this->readJson();
        $updates = [];

        if (array_key_exists('title', $payload)) {
            $title = trim((string) $payload['title']);
            if ($title === '') {
                errorResponse('title cannot be empty', 422);
            }
            $updates['title'] = $title;
        }
        if (array_key_exists('description', $payload)) {
            $updates['description'] = $this->nullable($payload['description']);
        }
        if (array_key_exists('year', $payload)) {
            $updates['year'] = $payload['year'] !== null ? (int) $payload['year'] : null;
        }
        if (array_key_exists('position', $payload)) {
            $updates['position'] = $payload['position'] !== null ? (int) $payload['position'] : null;
        }
        if (array_key_exists('is_active', $payload)) {
            $updates['is_active'] = $this->boolToInt($payload['is_active']);
        }

        if (empty($updates)) {
            jsonResponse([
                'success' => true,
                'message' => 'No changes supplied',
                'achievement' => $this->mapAdmin($existing),
            ]);
        }

        $setParts = [];
        foreach ($updates as $col => $value) {
            $setParts[] = "{$col} = :{$col}";
        }
        $updates['id'] = $id;

        $this->db->query(
            "UPDATE achievements SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = :id",
            $updates
        );

        $row = $this->db->fetchOne("SELECT * FROM achievements WHERE id = ?", [$id]);

        jsonResponse([
            'success' => true,
            'message' => 'Achievement updated successfully',
            'achievement' => $this->mapAdmin($row),
        ]);
    }

    private function deleteAchievement(int $id): void
    {
        $deleted = $this->db->delete('achievements', 'id = :id', ['id' => $id]);
        if ($deleted === 0) {
            errorResponse('Achievement not found', 404);
        }

        jsonResponse(['success' => true, 'message' => 'Achievement deleted successfully']);
    }

    private function mapPublic(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'description' => $row['description'] ?? null,
            'year' => isset($row['year']) ? (int) $row['year'] : null,
            'position' => isset($row['position']) ? (int) $row['position'] : 0,
        ];
    }

    private function mapAdmin(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'description' => $row['description'] ?? null,
            'year' => isset($row['year']) ? (int) $row['year'] : null,
            'position' => isset($row['position']) ? (int) $row['position'] : 0,
            'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    private function ensureSchema(): void
    {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'achievements'"
        );

        if ((int) $count === 0) {
            $this->db->query(
                "CREATE TABLE achievements (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(500) NOT NULL,
                    description TEXT NULL,
                    year INT NULL,
                    position INT NOT NULL DEFAULT 0,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_year (year),
                    INDEX idx_position (position),
                    INDEX idx_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
    }

    private function nextPosition(): int
    {
        $max = $this->db->fetchColumn("SELECT MAX(position) FROM achievements");
        return $max !== null ? ((int) $max) + 1 : 1;
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
