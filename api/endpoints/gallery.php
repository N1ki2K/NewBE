<?php

class GalleryEndpoints
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    public function handle(array $segments, string $method): void
    {
        $primary = $segments[0] ?? '';
        $nextSegments = array_slice($segments, 1);

        if ($primary !== 'gallery') {
            errorResponse('Not Found', 404);
        }

        $first = $nextSegments[0] ?? '';

        if ($first === 'admin') {
            $this->handleAdmin(array_slice($nextSegments, 1), $method);
            return;
        }

        if ($first !== '') {
            $this->handlePublicSingle($first, array_slice($nextSegments, 1), $method);
            return;
        }

        if ($method === 'GET') {
            $this->getPublicGallery();
            return;
        }

        errorResponse('Method Not Allowed', 405);
    }

    private function handleAdmin(array $segments, string $method): void
    {
        $user = AuthMiddleware::check();
        $first = $segments[0] ?? '';

        switch ($method) {
            case 'GET':
                if ($first === '' || $first === 'all') {
                    $this->getAdminGallery();
                    return;
                }
                $this->getAdminGalleryImage($first);
                return;

            case 'POST':
                if ($first === 'reorder') {
                    $this->reorderGalleryImages();
                    return;
                }
                $this->createGalleryImage();
                return;

            case 'PUT':
                if ($first === '') {
                    errorResponse('Gallery image ID is required', 400);
                }
                $this->updateGalleryImage($first);
                return;

            case 'DELETE':
                if ($first === '') {
                    errorResponse('Gallery image ID is required', 400);
                }
                $this->deleteGalleryImage($first);
                return;

            default:
                errorResponse('Method Not Allowed', 405);
        }
    }

    private function handlePublicSingle(string $id, array $segments, string $method): void
    {
        if ($method !== 'GET') {
            errorResponse('Method Not Allowed', 405);
        }

        $this->getPublicGalleryImage($id);
    }

    private function getPublicGallery(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM gallery_images WHERE is_published = 1 ORDER BY display_order ASC, created_at DESC"
        );

        $images = array_map([$this, 'transformRow'], $rows);
        jsonResponse($images);
    }

    private function getPublicGalleryImage(string $id): void
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM gallery_images WHERE id = ? AND is_published = 1",
            [$id]
        );

        if (!$row) {
            errorResponse('Gallery image not found', 404);
        }

        jsonResponse($this->transformRow($row));
    }

    private function getAdminGallery(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM gallery_images ORDER BY display_order ASC, created_at DESC"
        );

        $images = array_map(function ($row) {
            return $this->transformRow($row, true);
        }, $rows);

        jsonResponse($images);
    }

    private function getAdminGalleryImage(string $id): void
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM gallery_images WHERE id = ?",
            [$id]
        );

        if (!$row) {
            errorResponse('Gallery image not found', 404);
        }

        jsonResponse($this->transformRow($row, true));
    }

    private function createGalleryImage(): void
    {
        $data = $this->readRequestBody();
        $validated = $this->validatePayload($data);

        $displayOrder = $validated['display_order'];
        if ($displayOrder === null) {
            $displayOrder = $this->determineNextDisplayOrder();
        }

        $params = [
            'title_bg' => $validated['title_bg'],
            'title_en' => $validated['title_en'],
            'description_bg' => $validated['description_bg'],
            'description_en' => $validated['description_en'],
            'image_url' => $validated['image_url'],
            'image_alt_bg' => $validated['image_alt_bg'],
            'image_alt_en' => $validated['image_alt_en'],
            'display_order' => $displayOrder,
            'is_published' => $validated['is_published'],
        ];

        $this->db->query(
            "INSERT INTO gallery_images (
                title_bg,
                title_en,
                description_bg,
                description_en,
                image_url,
                image_alt_bg,
                image_alt_en,
                display_order,
                is_published,
                created_at,
                updated_at
            ) VALUES (
                :title_bg,
                :title_en,
                :description_bg,
                :description_en,
                :image_url,
                :image_alt_bg,
                :image_alt_en,
                :display_order,
                :is_published,
                NOW(),
                NOW()
            )",
            $params
        );

        $id = (int) $this->db->lastInsertId();
        $row = $this->db->fetchOne("SELECT * FROM gallery_images WHERE id = ?", [$id]);

        jsonResponse([
            'message' => 'Gallery image created successfully',
            'image' => $this->transformRow($row, true),
        ], 201);
    }

    private function updateGalleryImage(string $id): void
    {
        $row = $this->db->fetchOne("SELECT * FROM gallery_images WHERE id = ?", [$id]);
        if (!$row) {
            errorResponse('Gallery image not found', 404);
        }

        $data = $this->readRequestBody();
        $validated = $this->validatePayload($data, true);

        if (!empty($validated)) {
            if (array_key_exists('display_order', $validated) && $validated['display_order'] === null) {
                unset($validated['display_order']);
            }

            if (array_key_exists('display_order', $validated)) {
                $validated['display_order'] = (int) $validated['display_order'];
            }

            if (array_key_exists('is_published', $validated)) {
                $validated['is_published'] = $validated['is_published'] ? 1 : 0;
            }

            $setClauses = [];
            foreach ($validated as $column => $value) {
                $setClauses[] = "{$column} = :{$column}";
            }

            if (!empty($setClauses)) {
                $sql = "UPDATE gallery_images SET " . implode(', ', $setClauses) . ", updated_at = NOW() WHERE id = :id";
                $validated['id'] = $id;
                $this->db->query($sql, $validated);
            }
        }

        $updated = $this->db->fetchOne("SELECT * FROM gallery_images WHERE id = ?", [$id]);
        jsonResponse([
            'message' => 'Gallery image updated successfully',
            'image' => $this->transformRow($updated, true),
        ]);
    }

    private function deleteGalleryImage(string $id): void
    {
        $deleted = $this->db->delete('gallery_images', 'id = :id', ['id' => $id]);
        if ($deleted === 0) {
            errorResponse('Gallery image not found', 404);
        }

        jsonResponse(['message' => 'Gallery image deleted successfully']);
    }

    private function reorderGalleryImages(): void
    {
        $data = $this->readRequestBody();
        $items = isset($data['images']) && is_array($data['images']) ? $data['images'] : null;

        if ($items === null || empty($items)) {
            errorResponse('Images payload is required', 400);
        }

        foreach ($items as $item) {
            if (!isset($item['id']) || !isset($item['display_order'])) {
                continue;
            }

            $this->db->query(
                "UPDATE gallery_images SET display_order = ?, updated_at = NOW() WHERE id = ?",
                [(int) $item['display_order'], $item['id']]
            );
        }

        jsonResponse(['message' => 'Gallery images reordered successfully']);
    }

    private function transformRow(array $row, bool $includeInternal = false): array
    {
        $result = [
            'id' => isset($row['id']) ? (int) $row['id'] : null,
            'title_bg' => $row['title_bg'] ?? null,
            'title_en' => $row['title_en'] ?? null,
            'description_bg' => $row['description_bg'] ?? null,
            'description_en' => $row['description_en'] ?? null,
            'image_url' => $row['image_url'] ?? null,
            'image_alt_bg' => $row['image_alt_bg'] ?? null,
            'image_alt_en' => $row['image_alt_en'] ?? null,
            'display_order' => isset($row['display_order']) ? (int) $row['display_order'] : 0,
            'is_published' => isset($row['is_published']) ? (bool) $row['is_published'] : false,
        ];

        if ($includeInternal) {
            $result['created_at'] = isset($row['created_at']) ? $this->formatDate($row['created_at']) : null;
            $result['updated_at'] = isset($row['updated_at']) ? $this->formatDate($row['updated_at']) : null;
        }

        return $result;
    }

    private function determineNextDisplayOrder(): int
    {
        $value = $this->db->fetchColumn("SELECT MAX(display_order) FROM gallery_images");
        if ($value === false || $value === null) {
            return 1;
        }

        return ((int) $value) + 1;
    }

    private function validatePayload(array $data, bool $isUpdate = false): array
    {
        $result = [];

        if (!$isUpdate || array_key_exists('title_bg', $data)) {
            $result['title_bg'] = $this->nullableString($data['title_bg'] ?? null);
        }
        if (!$isUpdate || array_key_exists('title_en', $data)) {
            $result['title_en'] = $this->nullableString($data['title_en'] ?? null);
        }
        if (!$isUpdate || array_key_exists('description_bg', $data)) {
            $result['description_bg'] = $this->nullableString($data['description_bg'] ?? null);
        }
        if (!$isUpdate || array_key_exists('description_en', $data)) {
            $result['description_en'] = $this->nullableString($data['description_en'] ?? null);
        }

        if (!$isUpdate || array_key_exists('image_url', $data)) {
            $imageUrl = $this->nullableString($data['image_url'] ?? null);
            if (!$isUpdate && ($imageUrl === null || $imageUrl === '')) {
                errorResponse('image_url is required', 422);
            }
            if ($imageUrl !== null) {
                $result['image_url'] = $imageUrl;
            } elseif ($isUpdate) {
                $result['image_url'] = null;
            }
        }

        if (!$isUpdate || array_key_exists('image_alt_bg', $data)) {
            $result['image_alt_bg'] = $this->nullableString($data['image_alt_bg'] ?? null);
        }
        if (!$isUpdate || array_key_exists('image_alt_en', $data)) {
            $result['image_alt_en'] = $this->nullableString($data['image_alt_en'] ?? null);
        }
        if (!$isUpdate || array_key_exists('display_order', $data)) {
            if (array_key_exists('display_order', $data)) {
                $order = $data['display_order'];
                if ($order === null || $order === '') {
                    $result['display_order'] = null;
                } else {
                    $result['display_order'] = (int) $order;
                }
            } else {
                $result['display_order'] = null;
            }
        }
        if (!$isUpdate || array_key_exists('is_published', $data)) {
            if (array_key_exists('is_published', $data)) {
                $result['is_published'] = filter_var($data['is_published'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            } elseif (!$isUpdate) {
                $result['is_published'] = 1;
            }
        }

        if ($isUpdate) {
            $result = array_filter($result, function ($value) {
                return $value !== null;
            });
        }

        return $result;
    }

    private function ensureSchema(): void
    {
        $tableCount = (int) $this->db->fetchColumn(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'gallery_images'"
        );

        if ($tableCount === 0) {
            $this->createGalleryImagesTable();
        } else {
            $this->upgradeGalleryImagesTable();
        }
    }

    private function createGalleryImagesTable(): void
    {
        $this->db->query(
            "CREATE TABLE gallery_images (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title_bg VARCHAR(255) NULL,
                title_en VARCHAR(255) NULL,
                description_bg TEXT NULL,
                description_en TEXT NULL,
                image_url VARCHAR(500) NOT NULL,
                image_alt_bg VARCHAR(255) NULL,
                image_alt_en VARCHAR(255) NULL,
                display_order INT DEFAULT 0,
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_display_order (display_order),
                INDEX idx_is_published (is_published)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function upgradeGalleryImagesTable(): void
    {
        $columns = $this->db->fetchAll('SHOW COLUMNS FROM gallery_images');
        $existing = [];
        foreach ($columns as $column) {
            if (isset($column['Field'])) {
                $existing[$column['Field']] = true;
            }
        }

        $alterStatements = [
            'title_bg' => "ALTER TABLE gallery_images ADD COLUMN title_bg VARCHAR(255) NULL",
            'title_en' => "ALTER TABLE gallery_images ADD COLUMN title_en VARCHAR(255) NULL",
            'description_bg' => "ALTER TABLE gallery_images ADD COLUMN description_bg TEXT NULL",
            'description_en' => "ALTER TABLE gallery_images ADD COLUMN description_en TEXT NULL",
            'image_url' => "ALTER TABLE gallery_images ADD COLUMN image_url VARCHAR(500) NOT NULL",
            'image_alt_bg' => "ALTER TABLE gallery_images ADD COLUMN image_alt_bg VARCHAR(255) NULL",
            'image_alt_en' => "ALTER TABLE gallery_images ADD COLUMN image_alt_en VARCHAR(255) NULL",
            'display_order' => "ALTER TABLE gallery_images ADD COLUMN display_order INT DEFAULT 0",
            'is_published' => "ALTER TABLE gallery_images ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 1",
            'created_at' => "ALTER TABLE gallery_images ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
            'updated_at' => "ALTER TABLE gallery_images ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        ];

        foreach ($alterStatements as $column => $sql) {
            if (!isset($existing[$column])) {
                $this->db->query($sql);
            }
        }

        $indexes = $this->db->fetchAll('SHOW INDEX FROM gallery_images');
        $existingIndexes = [];
        foreach ($indexes as $index) {
            if (isset($index['Key_name'])) {
                $existingIndexes[$index['Key_name']] = true;
            }
        }

        if (!isset($existingIndexes['idx_display_order'])) {
            $this->db->query("CREATE INDEX idx_display_order ON gallery_images (display_order)");
        }
        if (!isset($existingIndexes['idx_is_published'])) {
            $this->db->query("CREATE INDEX idx_is_published ON gallery_images (is_published)");
        }
    }

    private function readRequestBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            errorResponse('Invalid JSON payload', 400);
        }

        return $payload;
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
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
}
