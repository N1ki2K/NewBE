<?php
// ====================================================
// News Management Endpoints
// ====================================================

class NewsEndpoints {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // GET /api/news
    public function getNews() {
        $language = $_GET['lang'] ?? 'bg';
        $publishedOnly = $_GET['published'] ?? 'true';

        $sql = "SELECT * FROM news WHERE language = ?";
        $params = [$language];

        if ($publishedOnly === 'true') {
            $sql .= " AND is_published = 1";
        }

        $sql .= " ORDER BY published_at DESC, created_at DESC";

        $news = $this->db->fetchAll($sql, $params);
        jsonResponse($news);
    }

    // GET /api/news/featured/latest
    public function getFeaturedNews() {
        $language = $_GET['lang'] ?? 'bg';
        $limit = (int)($_GET['limit'] ?? 3);

        $news = $this->db->fetchAll(
            "SELECT * FROM news
             WHERE language = ? AND is_published = 1 AND is_featured = 1
             ORDER BY published_at DESC, created_at DESC
             LIMIT ?",
            [$language, $limit]
        );

        jsonResponse($news);
    }

    // GET /api/news/:id
    public function getNewsArticle($id) {
        $language = $_GET['lang'] ?? 'bg';

        $article = $this->db->fetchOne(
            "SELECT * FROM news WHERE id = ? AND language = ?",
            [$id, $language]
        );

        if (!$article) {
            errorResponse('News article not found', 404);
        }

        // Increment view count
        $this->db->query(
            "UPDATE news SET view_count = view_count + 1 WHERE id = ?",
            [$id]
        );

        jsonResponse($article);
    }

    // GET /api/news/admin/all
    public function getAllNewsForAdmin() {
        AuthMiddleware::requireEditorOrAdmin();

        $news = $this->db->fetchAll(
            "SELECT n.*, u.username as author_name
             FROM news n
             LEFT JOIN users u ON n.author_id = u.id
             ORDER BY created_at DESC"
        );

        jsonResponse($news);
    }

    // POST /api/news/admin
    public function createNewsArticle() {
        $user = AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['title', 'slug'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                errorResponse("Field '$field' is required", 400);
            }
        }

        $data = [
            'title' => $input['title'],
            'slug' => $input['slug'],
            'excerpt' => $input['excerpt'] ?? null,
            'content' => $input['content'] ?? null,
            'language' => $input['language'] ?? 'bg',
            'author_id' => $user['id'],
            'category' => $input['category'] ?? null,
            'featured_image' => $input['featured_image'] ?? null,
            'is_published' => $input['is_published'] ?? false,
            'is_featured' => $input['is_featured'] ?? false,
            'published_at' => isset($input['published_at']) ? $input['published_at'] : null
        ];

        try {
            $id = $this->db->insert('news', $data);
            jsonResponse(['message' => 'News article created', 'id' => $id], 201);
        } catch (Exception $e) {
            errorResponse('Failed to create news article: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/news/admin/:id
    public function updateNewsArticle($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        // Check if article exists
        $existing = $this->db->fetchOne("SELECT id FROM news WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('News article not found', 404);
        }

        $data = [];
        $allowedFields = ['title', 'slug', 'excerpt', 'content', 'language', 'category',
                          'featured_image', 'is_published', 'is_featured', 'published_at'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (empty($data)) {
            errorResponse('No valid fields to update', 400);
        }

        $this->db->update('news', $data, 'id = ?', [$id]);
        jsonResponse(['message' => 'News article updated']);
    }

    // DELETE /api/news/admin/:id
    public function deleteNewsArticle($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $deleted = $this->db->delete('news', 'id = ?', [$id]);

        if ($deleted === 0) {
            errorResponse('News article not found', 404);
        }

        jsonResponse(['message' => 'News article deleted']);
    }

    // GET /api/news/:newsId/attachments
    public function getNewsAttachments($newsId) {
        $attachments = $this->db->fetchAll(
            "SELECT * FROM news_attachments WHERE news_id = ? ORDER BY display_order",
            [$newsId]
        );

        jsonResponse($attachments);
    }

    // POST /api/news/:newsId/attachments (file upload handled in upload.php)
    // DELETE /api/news/:newsId/attachments/:attachmentId
    public function deleteNewsAttachment($newsId, $attachmentId) {
        AuthMiddleware::requireEditorOrAdmin();

        // Get attachment info
        $attachment = $this->db->fetchOne(
            "SELECT * FROM news_attachments WHERE id = ? AND news_id = ?",
            [$attachmentId, $newsId]
        );

        if (!$attachment) {
            errorResponse('Attachment not found', 404);
        }

        // Delete file from filesystem
        if (file_exists($attachment['file_path'])) {
            unlink($attachment['file_path']);
        }

        // Delete from database
        $this->db->delete('news_attachments', 'id = ?', [$attachmentId]);

        jsonResponse(['message' => 'Attachment deleted']);
    }
}
