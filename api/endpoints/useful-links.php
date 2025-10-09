<?php
// ====================================================
// Useful Links Endpoints
// ====================================================

class UsefulLinksEndpoints {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // GET /api/useful-links
    public function getUsefulLinksContent() {
        $language = $_GET['lang'] ?? 'bg';

        $links = $this->db->fetchAll(
            "SELECT * FROM useful_links WHERE language = ? AND is_active = 1 ORDER BY position",
            [$language]
        );

        $content = $this->db->fetchAll(
            "SELECT * FROM useful_links_content WHERE language = ? AND is_active = 1 ORDER BY position",
            [$language]
        );

        jsonResponse(['success' => true, 'links' => $links, 'content' => $content]);
    }

    // GET /api/useful-links/admin
    public function getUsefulLinksForAdmin() {
        AuthMiddleware::requireEditorOrAdmin();

        $links = $this->db->fetchAll(
            "SELECT * FROM useful_links ORDER BY position, id"
        );

        $content = $this->db->fetchAll(
            "SELECT * FROM useful_links_content ORDER BY position, id"
        );

        jsonResponse(['success' => true, 'links' => $links, 'content' => $content]);
    }

    // POST /api/useful-links/link
    public function createUsefulLink() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['id', 'title', 'url'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                errorResponse("Field '$field' is required", 400);
            }
        }

        $data = [
            'id' => $input['id'],
            'title' => $input['title'],
            'url' => $input['url'],
            'description' => $input['description'] ?? null,
            'category' => $input['category'] ?? null,
            'language' => $input['language'] ?? 'bg',
            'position' => $input['position'] ?? 0,
            'is_active' => $input['is_active'] ?? true
        ];

        try {
            $this->db->insert('useful_links', $data);
            jsonResponse(['message' => 'Useful link created', 'id' => $input['id']], 201);
        } catch (Exception $e) {
            errorResponse('Failed to create useful link: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/useful-links/link/:id
    public function updateUsefulLink($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $existing = $this->db->fetchOne("SELECT id FROM useful_links WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Useful link not found', 404);
        }

        $data = [];
        $allowedFields = ['title', 'url', 'description', 'category', 'language', 'position', 'is_active'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (empty($data)) {
            errorResponse('No valid fields to update', 400);
        }

        $this->db->update('useful_links', $data, 'id = ?', [$id]);
        jsonResponse(['message' => 'Useful link updated']);
    }

    // DELETE /api/useful-links/link/:id
    public function deleteUsefulLink($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $deleted = $this->db->delete('useful_links', 'id = ?', [$id]);

        if ($deleted === 0) {
            errorResponse('Useful link not found', 404);
        }

        jsonResponse(['message' => 'Useful link deleted']);
    }

    // POST /api/useful-links/content
    public function createUsefulLinksContent() {
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
            $this->db->insert('useful_links_content', $data);
            jsonResponse(['message' => 'Content created', 'id' => $input['id']], 201);
        } catch (Exception $e) {
            errorResponse('Failed to create content: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/useful-links/content/:id
    public function updateUsefulLinksContent($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $existing = $this->db->fetchOne("SELECT id FROM useful_links_content WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Content not found', 404);
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

        $this->db->update('useful_links_content', $data, 'id = ?', [$id]);
        jsonResponse(['message' => 'Content updated']);
    }

    // PUT /api/useful-links/reorder
    public function reorderUsefulLinks() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);
        $links = $input['links'] ?? null;
        $content = $input['content'] ?? null;

        $this->db->beginTransaction();

        try {
            if ($links) {
                foreach ($links as $index => $link) {
                    if (isset($link['id'])) {
                        $this->db->update('useful_links', ['position' => $index], 'id = ?', [$link['id']]);
                    }
                }
            }

            if ($content) {
                foreach ($content as $index => $item) {
                    if (isset($item['id'])) {
                        $this->db->update('useful_links_content', ['position' => $index], 'id = ?', [$item['id']]);
                    }
                }
            }

            $this->db->commit();
            jsonResponse(['message' => 'Useful links reordered']);
        } catch (Exception $e) {
            $this->db->rollBack();
            errorResponse('Reorder failed: ' . $e->getMessage(), 500);
        }
    }
}
