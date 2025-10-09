<?php
// ====================================================
// Patron Content Endpoints
// ====================================================

class PatronEndpoints {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // GET /api/patron
    public function getPatronContent() {
        $language = $_GET['lang'] ?? 'bg';

        $content = $this->db->fetchAll(
            "SELECT * FROM patron_content WHERE language = ? AND is_active = 1 ORDER BY position",
            [$language]
        );

        jsonResponse(['success' => true, 'content' => $content]);
    }

    // GET /api/patron/admin
    public function getPatronContentForAdmin() {
        AuthMiddleware::requireEditorOrAdmin();

        $content = $this->db->fetchAll(
            "SELECT * FROM patron_content ORDER BY position, id"
        );

        jsonResponse(['success' => true, 'content' => $content]);
    }

    // GET /api/patron/:id
    public function getPatronContentSection($id) {
        $section = $this->db->fetchOne(
            "SELECT * FROM patron_content WHERE id = ?",
            [$id]
        );

        if (!$section) {
            errorResponse('Patron content section not found', 404);
        }

        jsonResponse(['success' => true, 'content' => $section]);
    }

    // POST /api/patron
    public function createPatronContent() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['id', 'section_type'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                errorResponse("Field '$field' is required", 400);
            }
        }

        $data = [
            'id' => $input['id'],
            'section_type' => $input['section_type'],
            'title' => $input['title'] ?? null,
            'content' => $input['content'] ?? null,
            'language' => $input['language'] ?? 'bg',
            'position' => $input['position'] ?? 0,
            'metadata' => isset($input['metadata']) ? json_encode($input['metadata']) : null,
            'is_active' => $input['is_active'] ?? true
        ];

        try {
            $this->db->insert('patron_content', $data);
            jsonResponse(['message' => 'Patron content created', 'id' => $input['id']], 201);
        } catch (Exception $e) {
            errorResponse('Failed to create patron content: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/patron/:id
    public function updatePatronContent($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $existing = $this->db->fetchOne("SELECT id FROM patron_content WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Patron content not found', 404);
        }

        $data = [];
        $allowedFields = ['section_type', 'title', 'content', 'language', 'position', 'is_active'];

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

        $this->db->update('patron_content', $data, 'id = ?', [$id]);
        jsonResponse(['message' => 'Patron content updated']);
    }

    // DELETE /api/patron/:id
    public function deletePatronContent($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $deleted = $this->db->delete('patron_content', 'id = ?', [$id]);

        if ($deleted === 0) {
            errorResponse('Patron content not found', 404);
        }

        jsonResponse(['message' => 'Patron content deleted']);
    }

    // PUT /api/patron/reorder
    public function reorderPatronContent() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);
        $content = $input['content'] ?? [];

        if (empty($content)) {
            errorResponse('No content provided', 400);
        }

        $this->db->beginTransaction();

        try {
            foreach ($content as $index => $item) {
                if (isset($item['id'])) {
                    $this->db->update('patron_content', ['position' => $index], 'id = ?', [$item['id']]);
                }
            }

            $this->db->commit();
            jsonResponse(['message' => 'Patron content reordered']);
        } catch (Exception $e) {
            $this->db->rollBack();
            errorResponse('Reorder failed: ' . $e->getMessage(), 500);
        }
    }
}
