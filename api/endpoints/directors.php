<?php

class DirectorsEndpoints
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
            $this->createDirector();
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
                    $this->getDirector((int) $target, true);
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
                $this->getDirector($id, false);
                return;
            case 'PUT':
                AuthMiddleware::check();
                $this->updateDirector($id);
                return;
            case 'DELETE':
                AuthMiddleware::check();
                $this->deleteDirector($id);
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

        if (!isset($payload['directors']) || !is_array($payload['directors'])) {
            errorResponse('directors payload is required', 400);
        }

        foreach ($payload['directors'] as $item) {
            if (!isset($item['id'])) {
                continue;
            }

            $position = isset($item['position']) ? (int) $item['position'] : null;
            if ($position === null) {
                continue;
            }

            $this->db->query(
                "UPDATE directors SET position = :position, updated_at = NOW() WHERE id = :id",
                [
                    'position' => $position,
                    'id' => (int) $item['id'],
                ]
            );
        }

        jsonResponse(['success' => true, 'message' => 'Directors reordered successfully']);
    }

    private function getPublicList(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM directors WHERE is_active = 1 ORDER BY position ASC, id ASC"
        );

        $directors = array_map([$this, 'mapPublic'], $rows);
        jsonResponse(['success' => true, 'directors' => $directors]);
    }

    private function getAdminList(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM directors ORDER BY position ASC, id ASC"
        );

        $directors = array_map([$this, 'mapAdmin'], $rows);
        jsonResponse(['success' => true, 'directors' => $directors]);
    }

    private function getDirector(int $id, bool $admin): void
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM directors WHERE id = ?",
            [$id]
        );

        if (!$row) {
            errorResponse('Director not found', 404);
        }

        jsonResponse([
            'success' => true,
            'director' => $admin ? $this->mapAdmin($row) : $this->mapPublic($row),
        ]);
    }

    private function createDirector(): void
    {
        $payload = $this->readJson();
        $name = isset($payload['name']) ? trim((string) $payload['name']) : '';
        if ($name === '') {
            errorResponse('name is required', 422);
        }

        $data = [
            'name' => $name,
            'tenure_start' => $this->nullable($payload['tenure_start'] ?? null),
            'tenure_end' => $this->nullable($payload['tenure_end'] ?? null),
            'description' => $this->nullable($payload['description'] ?? null),
            'position' => isset($payload['position']) ? (int) $payload['position'] : $this->nextPosition(),
            'is_active' => $this->boolToInt($payload['is_active'] ?? true),
        ];

        $id = $this->db->insert('directors', $data);

        $row = $this->db->fetchOne("SELECT * FROM directors WHERE id = ?", [$id]);

        jsonResponse([
            'success' => true,
            'message' => 'Director created successfully',
            'director' => $this->mapAdmin($row),
        ], 201);
    }

    private function updateDirector(int $id): void
    {
        $existing = $this->db->fetchOne("SELECT * FROM directors WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Director not found', 404);
        }

        $payload = $this->readJson();
        $updates = [];

        if (array_key_exists('name', $payload)) {
            $name = trim((string) $payload['name']);
            if ($name === '') {
                errorResponse('name cannot be empty', 422);
            }
            $updates['name'] = $name;
        }
        if (array_key_exists('tenure_start', $payload)) {
            $updates['tenure_start'] = $this->nullable($payload['tenure_start']);
        }
        if (array_key_exists('tenure_end', $payload)) {
            $updates['tenure_end'] = $this->nullable($payload['tenure_end']);
        }
        if (array_key_exists('description', $payload)) {
            $updates['description'] = $this->nullable($payload['description']);
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
                'director' => $this->mapAdmin($existing),
            ]);
        }

        $setParts = [];
        foreach ($updates as $col => $value) {
            $setParts[] = "{$col} = :{$col}";
        }
        $updates['id'] = $id;

        $this->db->query(
            "UPDATE directors SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = :id",
            $updates
        );

        $row = $this->db->fetchOne("SELECT * FROM directors WHERE id = ?", [$id]);

        jsonResponse([
            'success' => true,
            'message' => 'Director updated successfully',
            'director' => $this->mapAdmin($row),
        ]);
    }

    private function deleteDirector(int $id): void
    {
        $deleted = $this->db->delete('directors', 'id = :id', ['id' => $id]);
        if ($deleted === 0) {
            errorResponse('Director not found', 404);
        }

        jsonResponse(['success' => true, 'message' => 'Director deleted successfully']);
    }

    private function mapPublic(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'tenure_start' => $row['tenure_start'] ?? null,
            'tenure_end' => $row['tenure_end'] ?? null,
            'description' => $row['description'] ?? null,
            'position' => isset($row['position']) ? (int) $row['position'] : 0,
        ];
    }

    private function mapAdmin(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'tenure_start' => $row['tenure_start'] ?? null,
            'tenure_end' => $row['tenure_end'] ?? null,
            'description' => $row['description'] ?? null,
            'position' => isset($row['position']) ? (int) $row['position'] : 0,
            'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    private function ensureSchema(): void
    {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'directors'"
        );

        if ((int) $count === 0) {
            $this->db->query(
                "CREATE TABLE directors (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    tenure_start VARCHAR(50) NULL,
                    tenure_end VARCHAR(50) NULL,
                    description TEXT NULL,
                    position INT NOT NULL DEFAULT 0,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_position (position),
                    INDEX idx_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
    }

    private function nextPosition(): int
    {
        $max = $this->db->fetchColumn("SELECT MAX(position) FROM directors");
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
