<?php

class ImagesEndpoints {
    private $db;
    private $columns = null;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function handle($segments, $method) {
        $sub = isset($segments[1]) ? $segments[1] : '';

        if ($sub === '') {
            $this->handleCollection($method);
            return;
        }

        if ($sub === 'page' && isset($segments[2])) {
            $this->handlePageImages($method, $segments[2]);
            return;
        }

        $id = $sub;
        $this->handleSingle($method, $id);
    }

    private function handleCollection($method) {
        switch ($method) {
            case 'GET':
                $this->listImages();
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
    }

    private function handlePageImages($method, $pageId) {
        if ($method !== 'GET') {
            errorResponse('Method not allowed', 405);
        }

        $rows = $this->db->fetchAll(
            "SELECT * FROM images WHERE page_id = ? ORDER BY created_at DESC",
            array($pageId)
        );

        $result = array_map(function($row) {
            return $this->mapRow($row);
        }, $rows);

        jsonResponse($result);
    }

    private function handleSingle($method, $id) {
        switch ($method) {
            case 'GET':
                $this->getImage($id);
                break;
            case 'POST':
                AuthMiddleware::requireEditorOrAdmin();
                $this->createOrReplaceImage($id);
                break;
            case 'PUT':
                AuthMiddleware::requireEditorOrAdmin();
                $this->updateImage($id);
                break;
            case 'DELETE':
                AuthMiddleware::requireEditorOrAdmin();
                $this->deleteImage($id);
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
    }

    private function listImages() {
        $rows = $this->db->fetchAll("SELECT * FROM images ORDER BY created_at DESC");
        $result = array_map(function($row) {
            return $this->mapRow($row);
        }, $rows);

        jsonResponse($result);
    }

    private function getImage($id) {
        $row = $this->db->fetchOne("SELECT * FROM images WHERE id = ?", array($id));
        if (!$row) {
            errorResponse('Image not found', 404);
        }

        jsonResponse($this->mapRow($row));
    }

    private function createOrReplaceImage($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            errorResponse('Invalid payload', 400);
        }

        $data = $this->prepareData($input);
        $data['id'] = $id;

        $existing = $this->db->fetchOne("SELECT id FROM images WHERE id = ?", array($id));

        if ($existing) {
            $this->db->update('images', $data, 'id = :id', array('id' => $id));
        } else {
            $this->db->insert('images', $data);
        }

        $row = $this->db->fetchOne("SELECT * FROM images WHERE id = ?", array($id));
        jsonResponse($this->mapRow($row));
    }

    private function updateImage($id) {
        $existing = $this->db->fetchOne("SELECT * FROM images WHERE id = ?", array($id));
        if (!$existing) {
            errorResponse('Image not found', 404);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            errorResponse('Invalid payload', 400);
        }

        $data = $this->prepareData($input, true);
        if (empty($data)) {
            jsonResponse($this->mapRow($existing));
        }

        $this->db->update('images', $data, 'id = :id', array('id' => $id));
        $row = $this->db->fetchOne("SELECT * FROM images WHERE id = ?", array($id));
        jsonResponse($this->mapRow($row));
    }

    private function deleteImage($id) {
        $deleted = $this->db->delete('images', 'id = ?', array($id));
        if ($deleted === 0) {
            errorResponse('Image not found', 404);
        }

        jsonResponse(['message' => 'Image deleted successfully']);
    }

    private function prepareData($input, $skipNull = false) {
        $data = array();

        if (array_key_exists('filename', $input) && $this->columnExists('filename')) {
            $data['filename'] = $input['filename'];
        }
        if (array_key_exists('original_name', $input) && $this->columnExists('original_name')) {
            $data['original_name'] = $input['original_name'];
        }
        if (array_key_exists('url', $input) && $this->columnExists('url')) {
            $data['url'] = $input['url'];
        }
        if (array_key_exists('alt_text', $input) && $this->columnExists('alt_text')) {
            $data['alt_text'] = $input['alt_text'];
        }
        if (array_key_exists('page_id', $input) && $this->columnExists('page_id')) {
            $data['page_id'] = $input['page_id'];
        }
        if (array_key_exists('description', $input) && $this->columnExists('description')) {
            $data['description'] = $input['description'];
        }
        if (array_key_exists('file_size', $input) && $this->columnExists('file_size')) {
            $data['file_size'] = $input['file_size'];
        }
        if (array_key_exists('mime_type', $input) && $this->columnExists('mime_type')) {
            $data['mime_type'] = $input['mime_type'];
        }
        if (array_key_exists('width', $input) && $this->columnExists('width')) {
            $data['width'] = $input['width'];
        }
        if (array_key_exists('height', $input) && $this->columnExists('height')) {
            $data['height'] = $input['height'];
        }

        if ($skipNull) {
            $data = array_filter($data, function($value) {
                return $value !== null;
            });
        }

        return $data;
    }

    private function mapRow($row) {
        return array(
            'id' => isset($row['id']) ? $row['id'] : null,
            'filename' => $this->columnExists('filename') && isset($row['filename']) ? $row['filename'] : null,
            'original_name' => $this->columnExists('original_name') && isset($row['original_name']) ? $row['original_name'] : null,
            'url' => $this->columnExists('url') && isset($row['url']) ? $row['url'] : null,
            'alt_text' => $this->columnExists('alt_text') && isset($row['alt_text']) ? $row['alt_text'] : null,
            'page_id' => $this->columnExists('page_id') && isset($row['page_id']) ? $row['page_id'] : null,
            'description' => $this->columnExists('description') && isset($row['description']) ? $row['description'] : null,
            'file_size' => $this->columnExists('file_size') && isset($row['file_size']) ? (int)$row['file_size'] : null,
            'mime_type' => $this->columnExists('mime_type') && isset($row['mime_type']) ? $row['mime_type'] : null,
            'width' => $this->columnExists('width') && isset($row['width']) ? (int)$row['width'] : null,
            'height' => $this->columnExists('height') && isset($row['height']) ? (int)$row['height'] : null,
            'created_at' => $this->columnExists('created_at') && isset($row['created_at']) ? $row['created_at'] : null,
            'updated_at' => $this->columnExists('updated_at') && isset($row['updated_at']) ? $row['updated_at'] : null,
        );
    }

    private function getColumns() {
        if ($this->columns === null) {
            $rows = $this->db->fetchAll("SHOW COLUMNS FROM images");
            $this->columns = array_map(function($row) {
                return $row['Field'];
            }, $rows);
        }
        return $this->columns;
    }

    private function columnExists($column) {
        return in_array($column, $this->getColumns(), true);
    }
}
