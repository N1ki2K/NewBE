<?php

class SchoolStaffEndpoints {
    private $db;
    private $columns = null;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function getHeaders() {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    private function optionalAuthenticate() {
        $headers = $this->getHeaders();
        if (isset($headers['Authorization']) || isset($headers['authorization'])) {
            return AuthMiddleware::authenticate();
        }
        return null;
    }

    private function optionalRequireEditorOrAdmin() {
        $headers = $this->getHeaders();
        if (isset($headers['Authorization']) || isset($headers['authorization'])) {
            return AuthMiddleware::requireEditorOrAdmin();
        }
        return null;
    }

    public function handle($segments, $method) {
        $idSegment = isset($segments[1]) ? $segments[1] : '';

        if ($idSegment === '') {
            $this->handleCollection($method);
            return;
        }

        if ($idSegment === 'bulk' && isset($segments[2]) && $segments[2] === 'positions') {
            $this->handleBulkPositions($method);
            return;
        }

        if (!ctype_digit($idSegment)) {
            errorResponse('Not Found', 404);
        }

        $id = (int)$idSegment;
        $subSegment = isset($segments[2]) ? $segments[2] : '';

        if ($subSegment === 'image') {
            $this->handleImage($method, $id);
            return;
        }

        $this->handleSingle($method, $id);
    }

    private function handleCollection($method) {
        switch ($method) {
            case 'GET':
                $this->listStaff();
                break;
            case 'POST':
                $this->optionalRequireEditorOrAdmin();
                $this->createStaff();
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
    }

    private function handleSingle($method, $id) {
        switch ($method) {
            case 'GET':
                $this->getStaffMember((int)$id);
                break;
            case 'PUT':
                $this->optionalRequireEditorOrAdmin();
                $this->updateStaffMember((int)$id);
                break;
            case 'DELETE':
                $this->optionalRequireEditorOrAdmin();
                $this->deleteStaffMember((int)$id);
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
    }

    private function handleBulkPositions($method) {
        if ($method !== 'PUT') {
            errorResponse('Method not allowed', 405);
        }

        $this->optionalRequireEditorOrAdmin();
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['staffList']) || !is_array($input['staffList'])) {
            errorResponse('staffList array is required', 400);
        }

        $orderColumn = $this->getOrderColumn();

        foreach ($input['staffList'] as $item) {
            if (!isset($item['id'])) {
                continue;
            }

            $orderValue = isset($item['position']) ? (int)$item['position'] : (isset($item[$orderColumn]) ? (int)$item[$orderColumn] : 0);

            $data = array($orderColumn => $orderValue);

            $this->db->update(
                'school_staff',
                $data,
                'id = :id',
                array('id' => (int)$item['id'])
            );
        }

        jsonResponse(['message' => 'Positions updated successfully']);
    }

    private function handleImage($method, $id) {
        switch ($method) {
            case 'GET':
                $this->optionalAuthenticate();
                $this->getStaffImage($id);
                break;
            case 'POST':
                $this->optionalRequireEditorOrAdmin();
                $this->setStaffImage($id);
                break;
            case 'DELETE':
                $this->optionalRequireEditorOrAdmin();
                $this->deleteStaffImage($id);
                break;
            default:
                errorResponse('Method not allowed', 405);
        }
    }

    private function listStaff() {
        $orderColumn = $this->getOrderColumn();
        $rows = $this->db->fetchAll("SELECT * FROM school_staff ORDER BY {$orderColumn} ASC, name ASC");

        $result = array_map(function($row) {
            return $this->mapRow($row);
        }, $rows);

        jsonResponse($result);
    }

    private function getStaffMember($id) {
        $row = $this->db->fetchOne("SELECT * FROM school_staff WHERE id = ?", array($id));

        if (!$row) {
            errorResponse('Staff member not found', 404);
        }

        jsonResponse($this->mapRow($row));
    }

    private function createStaff() {
        $input = json_decode(file_get_contents('php://input'), true);

        $name = isset($input['name']) ? trim($input['name']) : '';
        if ($name === '') {
            errorResponse('Name is required', 400);
        }

        $orderColumn = $this->getOrderColumn();
        $data = array(
            'name' => $name,
            'position' => isset($input['role']) ? trim($input['role']) : (isset($input['positionTitle']) ? trim($input['positionTitle']) : ''),
            'department' => isset($input['department']) ? trim($input['department']) : null,
            'email' => isset($input['email']) ? trim($input['email']) : null,
            'phone' => isset($input['phone']) ? trim($input['phone']) : null,
            'bio' => isset($input['bio']) ? $input['bio'] : null,
            $orderColumn => isset($input['position']) ? (int)$input['position'] : 0,
            'image_filename' => isset($input['image_filename']) ? $input['image_filename'] : null,
            'image_url' => isset($input['image_url']) ? $input['image_url'] : null,
            'image_alt_text' => isset($input['alt_text']) ? $input['alt_text'] : null,
            'is_active' => isset($input['is_active']) ? (int)!!$input['is_active'] : 1
        );

        $filtered = $this->filterColumns($data);

        if (empty($filtered['position']) && !isset($filtered['position'])) {
            unset($filtered['position']);
        }

        $id = $this->db->insert('school_staff', $filtered);
        $newRow = $this->db->fetchOne("SELECT * FROM school_staff WHERE id = ?", array($id));

        jsonResponse($this->mapRow($newRow), 201);
    }

    private function updateStaffMember($id) {
        $existing = $this->db->fetchOne("SELECT * FROM school_staff WHERE id = ?", array($id));
        if (!$existing) {
            errorResponse('Staff member not found', 404);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $orderColumn = $this->getOrderColumn();
        $data = array(
            'name' => isset($input['name']) ? trim($input['name']) : null,
            'position' => isset($input['role']) ? trim($input['role']) : (isset($input['positionTitle']) ? trim($input['positionTitle']) : null),
            'department' => array_key_exists('department', $input) ? trim((string)$input['department']) : null,
            'email' => array_key_exists('email', $input) ? trim((string)$input['email']) : null,
            'phone' => array_key_exists('phone', $input) ? trim((string)$input['phone']) : null,
            'bio' => array_key_exists('bio', $input) ? $input['bio'] : null,
            $orderColumn => array_key_exists('position', $input) ? (int)$input['position'] : null,
            'image_filename' => array_key_exists('image_filename', $input) ? $input['image_filename'] : null,
            'image_url' => array_key_exists('image_url', $input) ? $input['image_url'] : null,
            'image_alt_text' => array_key_exists('alt_text', $input) ? $input['alt_text'] : null,
            'is_active' => array_key_exists('is_active', $input) ? (int)!!$input['is_active'] : null
        );

        $filtered = $this->filterColumns($data, true);

        if (empty($filtered)) {
            jsonResponse($this->mapRow($existing));
        }

        $this->db->update('school_staff', $filtered, 'id = :id', array('id' => $id));
        $updated = $this->db->fetchOne("SELECT * FROM school_staff WHERE id = ?", array($id));
        jsonResponse($this->mapRow($updated));
    }

    private function deleteStaffMember($id) {
        $deleted = $this->db->delete('school_staff', 'id = ?', array($id));

        if ($deleted === 0) {
            errorResponse('Staff member not found', 404);
        }

        jsonResponse(['message' => 'Staff member deleted successfully']);
    }

    private function getStaffImage($id) {
        // If schema has inline image columns, use them
        if ($this->columnExists('image_url') || $this->columnExists('image_filename')) {
            $row = $this->db->fetchOne("SELECT * FROM school_staff WHERE id = ?", array($id));
            if (!$row) {
                errorResponse('Staff member not found', 404);
            }

            $image = array(
                'image_filename' => isset($row['image_filename']) ? $row['image_filename'] : null,
                'image_url' => isset($row['image_url']) ? $row['image_url'] : null,
                'alt_text' => isset($row['image_alt_text']) ? $row['image_alt_text'] : (isset($row['alt_text']) ? $row['alt_text'] : null)
            );
            jsonResponse($image);
            return;
        }

        // Fallback to staff_images table
        $img = $this->db->fetchOne("SELECT image_filename, image_url, alt_text FROM staff_images WHERE staff_id = ?", array($id));
        if (!$img) {
            // Return nulls rather than 404 so UI can decide
            jsonResponse(array('image_filename' => null, 'image_url' => null, 'alt_text' => null));
            return;
        }
        jsonResponse($img);
    }

    private function setStaffImage($id) {
        $row = $this->db->fetchOne("SELECT id FROM school_staff WHERE id = ?", array($id));
        if (!$row) {
            errorResponse('Staff member not found', 404);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        // If inline columns are available, update them directly
        if ($this->columnExists('image_url') || $this->columnExists('image_filename')) {
            $data = array(
                'image_filename' => isset($input['image_filename']) ? $input['image_filename'] : null,
                'image_url' => isset($input['image_url']) ? $input['image_url'] : null,
                'image_alt_text' => isset($input['alt_text']) ? $input['alt_text'] : null
            );

            $filtered = $this->filterColumns($data, true);

            if (empty($filtered)) {
                errorResponse('Image columns not available in schema', 500);
            }

            $this->db->update('school_staff', $filtered, 'id = :id', array('id' => $id));
            jsonResponse(['message' => 'Image updated successfully']);
            return;
        }

        // Otherwise, upsert into staff_images table
        $data = array(
            'staff_id' => $id,
            'image_filename' => isset($input['image_filename']) ? $input['image_filename'] : null,
            'image_url' => isset($input['image_url']) ? $input['image_url'] : null,
            'alt_text' => isset($input['alt_text']) ? $input['alt_text'] : null
        );

        $existing = $this->db->fetchOne("SELECT id FROM staff_images WHERE staff_id = ?", array($id));
        if ($existing) {
            $this->db->update('staff_images', array(
                'image_filename' => $data['image_filename'],
                'image_url' => $data['image_url'],
                'alt_text' => $data['alt_text']
            ), 'staff_id = :staff_id', array('staff_id' => $id));
        } else {
            $this->db->insert('staff_images', $data);
        }

        jsonResponse(['message' => 'Image updated successfully']);
    }

    private function deleteStaffImage($id) {
        $row = $this->db->fetchOne("SELECT id FROM school_staff WHERE id = ?", array($id));
        if (!$row) {
            errorResponse('Staff member not found', 404);
        }

        if ($this->columnExists('image_url') || $this->columnExists('image_filename')) {
            $data = array(
                'image_filename' => null,
                'image_url' => null,
                'image_alt_text' => null
            );

            $filtered = $this->filterColumns($data, true);

            if (empty($filtered)) {
                errorResponse('Image columns not available in schema', 500);
            }

            $this->db->update('school_staff', $filtered, 'id = :id', array('id' => $id));
        } else {
            // Remove from staff_images table if present
            $this->db->delete('staff_images', 'staff_id = ?', array($id));
        }

        jsonResponse(['message' => 'Image removed successfully']);
    }

    private function mapRow($row) {
        $orderColumn = $this->getOrderColumn();
        $positionValue = isset($row[$orderColumn]) ? (int)$row[$orderColumn] : 0;

        return array(
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'role' => isset($row['position']) ? $row['position'] : '',
            'department' => isset($row['department']) ? $row['department'] : null,
            'email' => isset($row['email']) ? $row['email'] : null,
            'phone' => isset($row['phone']) ? $row['phone'] : null,
            'bio' => isset($row['bio']) ? $row['bio'] : null,
            'position' => $positionValue,
            'sort_order' => $positionValue,
            'image_filename' => isset($row['image_filename']) ? $row['image_filename'] : null,
            'image_url' => isset($row['image_url']) ? $row['image_url'] : null,
            'alt_text' => isset($row['image_alt_text']) ? $row['image_alt_text'] : (isset($row['alt_text']) ? $row['alt_text'] : null),
            'is_active' => isset($row['is_active']) ? (bool)$row['is_active'] : true
        );
    }

    private function getOrderColumn() {
        if ($this->columnExists('sort_order')) {
            return 'sort_order';
        }
        if ($this->columnExists('display_order')) {
            return 'display_order';
        }
        return 'id';
    }

    private function getColumns() {
        if ($this->columns === null) {
            $rows = $this->db->fetchAll("SHOW COLUMNS FROM school_staff");
            $this->columns = array_map(function($row) {
                return $row['Field'];
            }, $rows);
        }
        return $this->columns;
    }

    private function columnExists($column) {
        return in_array($column, $this->getColumns(), true);
    }

    private function filterColumns($data, $skipNull = false) {
        $columns = array_flip($this->getColumns());
        $filtered = array_intersect_key($data, $columns);

        if ($skipNull) {
            $filtered = array_filter($filtered, function($value) {
                return $value !== null;
            });
        }

        return $filtered;
    }
}
