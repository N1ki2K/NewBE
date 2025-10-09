<?php
// ====================================================
// Content Management Endpoints
// ====================================================

class ContentEndpoints {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // GET /api/content
    public function getAllContentSections() {
        $sections = $this->db->fetchAll(
            "SELECT * FROM content_sections WHERE is_active = 1 ORDER BY position, id"
        );
        jsonResponse($sections);
    }

    // GET /api/content/page/:pageId
    public function getContentByPage($pageId) {
        $sections = $this->db->fetchAll(
            "SELECT * FROM content_sections WHERE page_id = ? AND is_active = 1 ORDER BY position, id",
            [$pageId]
        );
        jsonResponse($sections);
    }

    // GET /api/content/:id
    public function getContentSection($id) {
        $section = $this->db->fetchOne(
            "SELECT * FROM content_sections WHERE id = ?",
            [$id]
        );

        if (!$section) {
            errorResponse('Content section not found', 404);
        }

        jsonResponse($section);
    }

    // POST /api/content
    public function createContentSection() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['id', 'page_id', 'section_type'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                errorResponse("Field '$field' is required", 400);
            }
        }

        $data = [
            'id' => $input['id'],
            'page_id' => $input['page_id'],
            'section_type' => $input['section_type'],
            'title' => $input['title'] ?? null,
            'content' => $input['content'] ?? null,
            'language' => $input['language'] ?? 'bg',
            'position' => $input['position'] ?? 0,
            'metadata' => isset($input['metadata']) ? json_encode($input['metadata']) : null,
            'is_active' => $input['is_active'] ?? true
        ];

        try {
            $this->db->insert('content_sections', $data);
            jsonResponse(['message' => 'Content section created', 'id' => $input['id']], 201);
        } catch (Exception $e) {
            errorResponse('Failed to create content section: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/content/:id
    public function updateContentSection($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        // Check if section exists
        $existing = $this->db->fetchOne("SELECT id FROM content_sections WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Content section not found', 404);
        }

        $data = [];
        $allowedFields = ['page_id', 'section_type', 'title', 'content', 'language', 'position', 'is_active'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (isset($input['metadata'])) {
            $data['metadata'] = json_encode($input['metadata']);
        }

        if (empty($data)) {
            errorResponse('No valid fields to update', 400);
        }

        $this->db->update('content_sections', $data, 'id = ?', [$id]);
        jsonResponse(['message' => 'Content section updated']);
    }

    // DELETE /api/content/:id
    public function deleteContentSection($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $deleted = $this->db->delete('content_sections', 'id = ?', [$id]);

        if ($deleted === 0) {
            errorResponse('Content section not found', 404);
        }

        jsonResponse(['message' => 'Content section deleted']);
    }

    // POST /api/content/bulk-update
    public function bulkUpdateContentSections() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);
        $sections = $input['sections'] ?? [];

        if (empty($sections)) {
            errorResponse('No sections provided', 400);
        }

        $this->db->beginTransaction();

        try {
            foreach ($sections as $section) {
                if (!isset($section['id'])) {
                    continue;
                }

                $data = [];
                $allowedFields = ['page_id', 'section_type', 'title', 'content', 'language', 'position', 'is_active'];

                foreach ($allowedFields as $field) {
                    if (isset($section[$field])) {
                        $data[$field] = $section[$field];
                    }
                }

                if (isset($section['metadata'])) {
                    $data['metadata'] = json_encode($section['metadata']);
                }

                if (!empty($data)) {
                    $this->db->update('content_sections', $data, 'id = ?', [$section['id']]);
                }
            }

            $this->db->commit();
            jsonResponse(['message' => 'Content sections updated']);
        } catch (Exception $e) {
            $this->db->rollBack();
            errorResponse('Bulk update failed: ' . $e->getMessage(), 500);
        }
    }
}
