<?php

class NewsEndpoints
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    /**
     * Entry point for all /api/news requests
     */
    public function handle(array $segments, string $method): void
    {
        // Backwards compatibility for legacy action-based calls
        if (isset($_GET['action'])) {
            $this->handleLegacyAction($_GET['action'], $segments, $method);
            return;
        }

        $first = $segments[0] ?? '';

        if ($first === 'admin') {
            $this->handleAdmin(array_slice($segments, 1), $method);
            return;
        }

        if ($first === 'featured') {
            $this->handleFeatured(array_slice($segments, 1), $method);
            return;
        }

        if ($first !== '') {
            $this->handleSingle($first, array_slice($segments, 1), $method);
            return;
        }

        if ($method === 'GET') {
            $this->getPublicNews();
            return;
        }

        errorResponse('Not Found', 404);
    }

    /**
     * Support older query-based API calls
     */
    private function handleLegacyAction(string $action, array $segments, string $method): void
    {
        switch ($action) {
            case 'create':
                $this->createNewsArticle();
                return;
            case 'update':
                $id = isset($_GET['id']) ? (string) $_GET['id'] : null;
                if (!$id) {
                    errorResponse('ID is required for update action', 400);
                }
                $this->updateNewsArticle($id);
                return;
            case 'delete':
                $id = isset($_GET['id']) ? (string) $_GET['id'] : null;
                if (!$id) {
                    errorResponse('ID is required for delete action', 400);
                }
                $this->deleteNewsArticle($id);
                return;
            case 'getSingle':
                $id = isset($_GET['id']) ? (string) $_GET['id'] : null;
                if (!$id) {
                    errorResponse('ID is required for getSingle action', 400);
                }
                $this->handleSingle($id, [], 'GET');
                return;
            case 'getAll':
            default:
                $this->getAdminNewsList();
                return;
        }
    }

    /**
     * Handle /news/admin routes
     */
    private function handleAdmin(array $segments, string $method): void
    {
        // All admin endpoints require editor or admin access
        AuthMiddleware::check();

        $target = $segments[0] ?? '';

        switch ($method) {
            case 'GET':
                if ($target === '' || $target === 'all') {
                    $this->getAdminNewsList();
                    return;
                }
                $this->getAdminNewsArticle($target);
                return;

            case 'POST':
                $this->createNewsArticle();
                return;

            case 'PUT':
                if ($target === '') {
                    errorResponse('News ID is required for update', 400);
                }
                $this->updateNewsArticle($target);
                return;

            case 'DELETE':
                if ($target === '') {
                    errorResponse('News ID is required for delete', 400);
                }
                $this->deleteNewsArticle($target);
                return;

            default:
                errorResponse('Method Not Allowed', 405);
        }
    }

    /**
     * Handle /news/featured routes
     */
    private function handleFeatured(array $segments, string $method): void
    {
        if ($method !== 'GET') {
            errorResponse('Method Not Allowed', 405);
        }

        $target = $segments[0] ?? '';

        if ($target === 'latest') {
            $this->getFeaturedNews();
            return;
        }

        errorResponse('Not Found', 404);
    }

    /**
     * Handle /news/{id} routes (including attachments)
     */
    private function handleSingle(string $id, array $segments, string $method): void
    {
        $id = trim($id);
        if ($id === '') {
            errorResponse('News ID is required', 400);
        }

        $target = $segments[0] ?? '';

        if ($target === 'attachments') {
            $this->handleAttachments($id, array_slice($segments, 1), $method);
            return;
        }

        if ($method !== 'GET') {
            errorResponse('Method Not Allowed', 405);
        }

        $this->getPublicNewsArticle($id);
    }

    /**
     * Handle /news/{id}/attachments routes
     */
    private function handleAttachments(string $newsId, array $segments, string $method): void
    {
        switch ($method) {
            case 'GET':
                $this->getNewsAttachments($newsId);
                return;
            default:
                errorResponse('Method Not Allowed', 405);
        }
    }

    /**
     * GET /api/news (public list)
     */
    private function getPublicNews(): void
    {
        $language = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : 'bg';
        $publishedOnly = isset($_GET['published']) ? filter_var($_GET['published'], FILTER_VALIDATE_BOOLEAN) : true;

        $conditions = [];
        $params = [];

        if ($publishedOnly) {
            $conditions[] = 'is_published = 1';
        }

        $sql = 'SELECT * FROM news';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY COALESCE(published_date, created_at) DESC';

        $rows = $this->db->fetchAll($sql, $params);
        $articles = array_map(function ($row) use ($language) {
            return $this->transformForPublic($row, $language);
        }, $rows);

        jsonResponse($articles);
    }

    /**
     * GET /api/news/{id} (public single article)
     */
    private function getPublicNewsArticle(string $id): void
    {
        $language = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : 'bg';

        $row = $this->db->fetchOne('SELECT * FROM news WHERE id = ?', [$id]);

        if (!$row) {
            errorResponse('News article not found', 404);
        }

        jsonResponse($this->transformForPublic($row, $language));
    }

    /**
     * GET /api/news/featured/latest
     */
    private function getFeaturedNews(): void
    {
        $language = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : 'bg';
        $limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 3;

        $sql = 'SELECT * FROM news WHERE is_published = 1 AND is_featured = 1 ORDER BY COALESCE(published_date, created_at) DESC LIMIT ' . $limit;
        $rows = $this->db->fetchAll($sql);

        $articles = array_map(function ($row) use ($language) {
            return $this->transformForPublic($row, $language);
        }, $rows);

        jsonResponse($articles);
    }

    /**
     * GET /api/news/admin/all
     */
    private function getAdminNewsList(): void
    {
        $rows = $this->db->fetchAll('SELECT * FROM news ORDER BY COALESCE(published_date, created_at) DESC');
        $articles = array_map([$this, 'transformForAdmin'], $rows);
        jsonResponse($articles);
    }

    /**
     * GET /api/news/admin/{id}
     */
    private function getAdminNewsArticle(string $id): void
    {
        $row = $this->db->fetchOne('SELECT * FROM news WHERE id = ?', [$id]);

        if (!$row) {
            errorResponse('News article not found', 404);
        }

        jsonResponse($this->transformForAdmin($row));
    }

    /**
     * POST /api/news/admin
     * Legacy: POST /api/news?action=create
     */
    private function createNewsArticle(): void
    {
        AuthMiddleware::check();
        $data = $this->getRequestBody();

        $this->validateNewsPayload($data, false);

        $id = isset($data['id']) && is_string($data['id']) && trim($data['id']) !== ''
            ? trim($data['id'])
            : $this->generateId($data);

        $params = $this->preparePersistenceParams($data, $id);

        $this->db->query(
            'INSERT INTO news (
                id,
                title_bg,
                title_en,
                excerpt_bg,
                excerpt_en,
                content_bg,
                content_en,
                featured_image_url,
                featured_image_alt,
                is_published,
                is_featured,
                published_date,
                created_at,
                updated_at
            ) VALUES (
                :id,
                :title_bg,
                :title_en,
                :excerpt_bg,
                :excerpt_en,
                :content_bg,
                :content_en,
                :featured_image_url,
                :featured_image_alt,
                :is_published,
                :is_featured,
                :published_date,
                NOW(),
                NOW()
            )',
            $params
        );

        $article = $this->db->fetchOne('SELECT * FROM news WHERE id = ?', [$id]);

        jsonResponse([
            'message' => 'News article created successfully',
            'id' => $id,
            'article' => $this->transformForAdmin($article),
        ], 201);
    }

    /**
     * PUT /api/news/admin/{id}
     * Legacy: POST /api/news?action=update&id={id}
     */
    private function updateNewsArticle(string $id): void
    {
        AuthMiddleware::check();
        $id = trim($id);

        if ($id === '') {
            errorResponse('News ID is required', 400);
        }

        $existing = $this->db->fetchOne('SELECT id FROM news WHERE id = ?', [$id]);
        if (!$existing) {
            errorResponse('News article not found', 404);
        }

        $data = $this->getRequestBody();
        $this->validateNewsPayload($data, true);

        $params = $this->preparePersistenceParams($data, $id);
        $params['id'] = $id;

        $this->db->query(
            'UPDATE news SET
                title_bg = :title_bg,
                title_en = :title_en,
                excerpt_bg = :excerpt_bg,
                excerpt_en = :excerpt_en,
                content_bg = :content_bg,
                content_en = :content_en,
                featured_image_url = :featured_image_url,
                featured_image_alt = :featured_image_alt,
                is_published = :is_published,
                is_featured = :is_featured,
                published_date = :published_date,
                updated_at = NOW()
            WHERE id = :id',
            $params
        );

        $article = $this->db->fetchOne('SELECT * FROM news WHERE id = ?', [$id]);

        jsonResponse([
            'message' => 'News article updated successfully',
            'article' => $this->transformForAdmin($article),
        ]);
    }

    /**
     * DELETE /api/news/admin/{id}
     * Legacy: POST /api/news?action=delete&id={id}
     */
    private function deleteNewsArticle(string $id): void
    {
        AuthMiddleware::check();
        $id = trim($id);

        if ($id === '') {
            errorResponse('News ID is required', 400);
        }

        $deleted = $this->db->delete('news', 'id = :id', ['id' => $id]);

        if ($deleted === 0) {
            errorResponse('News article not found', 404);
        }

        // Also remove attachments associated with the news article
        $this->db->delete('news_attachments', 'news_id = :news_id', ['news_id' => $id]);

        jsonResponse(['message' => 'News article deleted successfully']);
    }

    /**
     * GET /api/news/{id}/attachments
     */
    private function getNewsAttachments(string $newsId): void
    {
        $rows = $this->db->fetchAll(
            'SELECT id, news_id, filename, original_name, url, file_type, file_size, created_at
             FROM news_attachments
             WHERE news_id = ?
             ORDER BY created_at DESC',
            [$newsId]
        );

        jsonResponse($rows);
    }

    /**
     * Ensure required tables & columns exist
     */
    private function ensureSchema(): void
    {
        try {
            $tableCount = (int) $this->db->fetchColumn(
                "SELECT COUNT(*)
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'news'"
            );
        } catch (Exception $e) {
            error_log('Failed to check news table existence: ' . $e->getMessage());
            throw $e;
        }

        if ($tableCount === 0) {
            $this->createNewsTable();
        } else {
            $this->upgradeNewsTable();
        }

        $attachmentsCount = (int) $this->db->fetchColumn(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'news_attachments'"
        );

        if ($attachmentsCount === 0) {
            $this->createNewsAttachmentsTable();
        } else {
            $this->upgradeNewsAttachmentsTable();
        }
    }

    private function createNewsTable(): void
    {
        $this->db->query(
            "CREATE TABLE news (
                id VARCHAR(100) PRIMARY KEY,
                title_bg VARCHAR(255) NULL,
                title_en VARCHAR(255) NULL,
                excerpt_bg TEXT NULL,
                excerpt_en TEXT NULL,
                content_bg LONGTEXT NULL,
                content_en LONGTEXT NULL,
                featured_image_url VARCHAR(500) NULL,
                featured_image_alt VARCHAR(255) NULL,
                is_published TINYINT(1) NOT NULL DEFAULT 0,
                is_featured TINYINT(1) NOT NULL DEFAULT 0,
                published_date DATETIME NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_published (is_published, published_date),
                INDEX idx_featured (is_featured, published_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function upgradeNewsTable(): void
    {
        $columns = $this->db->fetchAll('SHOW COLUMNS FROM news');
        $existing = [];
        foreach ($columns as $column) {
            if (isset($column['Field'])) {
                $existing[$column['Field']] = true;
            }
        }

        $alterStatements = [
            'title_bg' => "ALTER TABLE news ADD COLUMN title_bg VARCHAR(255) NULL",
            'title_en' => "ALTER TABLE news ADD COLUMN title_en VARCHAR(255) NULL",
            'excerpt_bg' => "ALTER TABLE news ADD COLUMN excerpt_bg TEXT NULL",
            'excerpt_en' => "ALTER TABLE news ADD COLUMN excerpt_en TEXT NULL",
            'content_bg' => "ALTER TABLE news ADD COLUMN content_bg LONGTEXT NULL",
            'content_en' => "ALTER TABLE news ADD COLUMN content_en LONGTEXT NULL",
            'featured_image_url' => "ALTER TABLE news ADD COLUMN featured_image_url VARCHAR(500) NULL",
            'featured_image_alt' => "ALTER TABLE news ADD COLUMN featured_image_alt VARCHAR(255) NULL",
            'is_published' => "ALTER TABLE news ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 0",
            'is_featured' => "ALTER TABLE news ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0",
            'published_date' => "ALTER TABLE news ADD COLUMN published_date DATETIME NULL",
            'created_at' => "ALTER TABLE news ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
            'updated_at' => "ALTER TABLE news ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        ];

        foreach ($alterStatements as $column => $sql) {
            if (!isset($existing[$column])) {
                $this->db->query($sql);
            }
        }
    }

    private function createNewsAttachmentsTable(): void
    {
        $this->db->query(
            "CREATE TABLE news_attachments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                news_id VARCHAR(100) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                url VARCHAR(500) NOT NULL,
                file_type VARCHAR(100) NULL,
                file_size INT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_news_id (news_id),
                CONSTRAINT fk_news_attachments_news FOREIGN KEY (news_id)
                    REFERENCES news(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function upgradeNewsAttachmentsTable(): void
    {
        $columns = $this->db->fetchAll('SHOW COLUMNS FROM news_attachments');
        $existing = [];
        foreach ($columns as $column) {
            if (isset($column['Field'])) {
                $existing[$column['Field']] = true;
            }
        }

        $alterStatements = [
            'url' => "ALTER TABLE news_attachments ADD COLUMN url VARCHAR(500) NOT NULL AFTER original_name",
            'file_type' => "ALTER TABLE news_attachments ADD COLUMN file_type VARCHAR(100) NULL",
            'file_size' => "ALTER TABLE news_attachments ADD COLUMN file_size INT NULL",
            'created_at' => "ALTER TABLE news_attachments ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ];

        foreach ($alterStatements as $column => $sql) {
            if (!isset($existing[$column])) {
                $this->db->query($sql);
            }
        }
    }

    /**
     * Read and decode JSON request body
     */
    private function getRequestBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        $data = json_decode($raw, true);

        if (!is_array($data)) {
            errorResponse('Invalid JSON payload supplied', 400);
        }

        return $data;
    }

    /**
     * Ensure request payload is valid
     */
    private function validateNewsPayload(array $data, bool $isUpdate): void
    {
        $titleBg = isset($data['title_bg']) ? trim((string) $data['title_bg']) : '';
        $titleEn = isset($data['title_en']) ? trim((string) $data['title_en']) : '';

        if ($titleBg === '' && $titleEn === '') {
            errorResponse('Please provide at least one title (BG or EN)', 422);
        }
    }

    /**
     * Prepare data array for insert/update queries
     */
    private function preparePersistenceParams(array $data, string $id): array
    {
        $publishedDate = null;
        if (!empty($data['published_date'])) {
            $publishedDate = $this->formatDateTimeForStorage($data['published_date']);
        }

        return [
            'id' => $id,
            'title_bg' => $this->nullIfEmpty($data['title_bg'] ?? null),
            'title_en' => $this->nullIfEmpty($data['title_en'] ?? null),
            'excerpt_bg' => $this->nullIfEmpty($data['excerpt_bg'] ?? null),
            'excerpt_en' => $this->nullIfEmpty($data['excerpt_en'] ?? null),
            'content_bg' => $this->nullIfEmpty($data['content_bg'] ?? null),
            'content_en' => $this->nullIfEmpty($data['content_en'] ?? null),
            'featured_image_url' => $this->nullIfEmpty($data['featured_image_url'] ?? null),
            'featured_image_alt' => $this->nullIfEmpty($data['featured_image_alt'] ?? null),
            'is_published' => $this->boolToInt($data['is_published'] ?? false),
            'is_featured' => $this->boolToInt($data['is_featured'] ?? false),
            'published_date' => $publishedDate,
        ];
    }

    /**
     * Generate deterministic ID based on the provided data
     */
    private function generateId(array $data): string
    {
        $baseTitle = $data['title_bg'] ?? $data['title_en'] ?? '';
        $slug = $this->slugify($baseTitle);

        if ($slug === '') {
            $slug = 'news';
        }

        $candidate = $slug;
        $counter = 1;

        while (true) {
            $exists = $this->db->fetchColumn('SELECT COUNT(*) FROM news WHERE id = ?', [$candidate]);
            if ((int) $exists === 0) {
                return $candidate;
            }
            $candidate = $slug . '-' . $counter;
            $counter++;
        }
    }

    private function transformForPublic(array $row, string $language): array
    {
        $isEnglish = $language === 'en';
        $titleKeyPrimary = $isEnglish ? 'title_en' : 'title_bg';
        $titleKeyFallback = $isEnglish ? 'title_bg' : 'title_en';
        $excerptPrimary = $isEnglish ? 'excerpt_en' : 'excerpt_bg';
        $excerptFallback = $isEnglish ? 'excerpt_bg' : 'excerpt_en';
        $contentPrimary = $isEnglish ? 'content_en' : 'content_bg';
        $contentFallback = $isEnglish ? 'content_bg' : 'content_en';

        $title = $row[$titleKeyPrimary] ?? $row[$titleKeyFallback] ?? '';
        $excerpt = $row[$excerptPrimary] ?? $row[$excerptFallback] ?? '';
        $content = $row[$contentPrimary] ?? $row[$contentFallback] ?? '';
        $publishedDate = $this->formatDateTimeForResponse($row['published_date'] ?? null, $row['created_at'] ?? null);

        return [
            'id' => $row['id'],
            'title' => $title !== '' ? $title : ($row['title'] ?? ''),
            'excerpt' => $excerpt !== '' ? $excerpt : ($row['excerpt'] ?? ''),
            'content' => $content !== '' ? $content : ($row['content'] ?? ''),
            'featured_image_url' => $row['featured_image_url'] ?? null,
            'featured_image_alt' => $row['featured_image_alt'] ?? null,
            'published_date' => $publishedDate,
            'is_published' => $this->intToBool($row['is_published'] ?? 0),
            'is_featured' => $this->intToBool($row['is_featured'] ?? 0),
        ];
    }

    private function transformForAdmin(array $row): array
    {
        return [
            'id' => $row['id'],
            'title_bg' => $row['title_bg'] ?? '',
            'title_en' => $row['title_en'] ?? '',
            'excerpt_bg' => $row['excerpt_bg'] ?? '',
            'excerpt_en' => $row['excerpt_en'] ?? '',
            'content_bg' => $row['content_bg'] ?? '',
            'content_en' => $row['content_en'] ?? '',
            'featured_image_url' => $row['featured_image_url'] ?? '',
            'featured_image_alt' => $row['featured_image_alt'] ?? '',
            'is_published' => $this->intToBool($row['is_published'] ?? 0),
            'is_featured' => $this->intToBool($row['is_featured'] ?? 0),
            'published_date' => $this->formatDateTimeForResponse($row['published_date'] ?? null, $row['created_at'] ?? null),
            'created_at' => $this->formatDateTimeForResponse($row['created_at'] ?? null),
            'updated_at' => $this->formatDateTimeForResponse($row['updated_at'] ?? null),
        ];
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));

        // Transliterate Cyrillic to Latin where possible
        $translitTable = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y',
            'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh',
            'щ' => 'sht', 'ъ' => 'a', 'ь' => '', 'ю' => 'yu', 'я' => 'ya',
        ];

        $value = strtr($value, $translitTable);
        $value = preg_replace('/[^a-z0-9]+/u', '-', $value);
        $value = trim($value ?? '', '-');

        return $value ?? '';
    }

    private function nullIfEmpty($value)
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

    private function intToBool($value): bool
    {
        return (bool) ((int) $value);
    }

    private function formatDateTimeForStorage($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime((string) $value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function formatDateTimeForResponse($value, $fallback = null): ?string
    {
        $timestamp = null;

        if ($value) {
            $timestamp = strtotime((string) $value);
        }

        if ($timestamp === false || $timestamp === null) {
            if ($fallback) {
                $timestamp = strtotime((string) $fallback);
            }
        }

        if ($timestamp === false || $timestamp === null) {
            return null;
        }

        return date(DATE_ATOM, $timestamp);
    }
}
