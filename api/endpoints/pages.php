<?php
// ====================================================
// Pages Management Endpoints
// ====================================================

class PagesEndpoints {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // GET /api/pages
    public function getPages() {
        $pages = $this->db->fetchAll(
            "SELECT * FROM pages WHERE is_active = 1 ORDER BY position, id"
        );
        jsonResponse($pages);
    }

    // GET /api/pages/all
    public function getAllPages() {
        $pages = $this->db->fetchAll(
            "SELECT * FROM pages ORDER BY position, id"
        );
        jsonResponse($pages);
    }

    // POST /api/pages
    public function createPage() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['id', 'title', 'slug'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                errorResponse("Field '$field' is required", 400);
            }
        }

        $data = [
            'id' => $input['id'],
            'title' => $input['title'],
            'slug' => $input['slug'],
            'description' => $input['description'] ?? null,
            'template' => $input['template'] ?? null,
            'parent_id' => $input['parent_id'] ?? null,
            'position' => $input['position'] ?? 0,
            'is_active' => $input['is_active'] ?? true,
            'is_published' => $input['is_published'] ?? true,
            'meta_title' => $input['meta_title'] ?? null,
            'meta_description' => $input['meta_description'] ?? null,
            'meta_keywords' => $input['meta_keywords'] ?? null
        ];

        try {
            $this->db->insert('pages', $data);
            jsonResponse(['message' => 'Page created', 'id' => $input['id']], 201);
        } catch (Exception $e) {
            errorResponse('Failed to create page: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/pages/:id
    public function updatePage($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        // Check if page exists
        $existing = $this->db->fetchOne("SELECT id FROM pages WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Page not found', 404);
        }

        $data = [];
        $allowedFields = ['title', 'slug', 'description', 'template', 'parent_id', 'position',
                          'is_active', 'is_published', 'meta_title', 'meta_description', 'meta_keywords'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (empty($data)) {
            errorResponse('No valid fields to update', 400);
        }

        $this->db->update('pages', $data, 'id = ?', [$id]);
        jsonResponse(['message' => 'Page updated']);
    }

    // DELETE /api/pages/:id
    public function deletePage($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $permanent = $_GET['permanent'] ?? false;

        if ($permanent) {
            $deleted = $this->db->delete('pages', 'id = ?', [$id]);
        } else {
            $deleted = $this->db->update('pages', ['is_active' => false], 'id = ?', [$id]);
        }

        if ($deleted === 0) {
            errorResponse('Page not found', 404);
        }

        jsonResponse(['message' => 'Page deleted']);
    }

    // POST /api/pages/reorder
    public function reorderPages() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);
        $pages = $input['pages'] ?? [];

        if (empty($pages)) {
            errorResponse('No pages provided', 400);
        }

        $this->db->beginTransaction();

        try {
            foreach ($pages as $index => $page) {
                if (isset($page['id'])) {
                    $this->db->update('pages', ['position' => $index], 'id = ?', [$page['id']]);
                }
            }

            $this->db->commit();
            jsonResponse(['message' => 'Pages reordered']);
        } catch (Exception $e) {
            $this->db->rollBack();
            errorResponse('Reorder failed: ' . $e->getMessage(), 500);
        }
    }
}
