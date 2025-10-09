<?php
// ====================================================
// Staff Management Endpoints
// ====================================================

class StaffEndpoints {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // GET /api/staff
    public function getStaffMembers() {
        $staff = $this->db->fetchAll(
            "SELECT * FROM staff WHERE is_active = 1 ORDER BY display_order, name"
        );
        jsonResponse($staff);
    }

    // GET /api/staff/:id
    public function getStaffMember($id) {
        $member = $this->db->fetchOne(
            "SELECT * FROM staff WHERE id = ?",
            [$id]
        );

        if (!$member) {
            errorResponse('Staff member not found', 404);
        }

        jsonResponse($member);
    }

    // POST /api/staff
    public function createStaffMember() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['id', 'name'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                errorResponse("Field '$field' is required", 400);
            }
        }

        $data = [
            'id' => $input['id'],
            'name' => $input['name'],
            'position' => $input['position'] ?? null,
            'bio' => $input['bio'] ?? null,
            'email' => $input['email'] ?? null,
            'phone' => $input['phone'] ?? null,
            'image_url' => $input['image_url'] ?? null,
            'is_director' => $input['is_director'] ?? false,
            'display_order' => $input['display_order'] ?? 0,
            'is_active' => $input['is_active'] ?? true
        ];

        try {
            $this->db->insert('staff', $data);
            jsonResponse(['message' => 'Staff member created', 'id' => $input['id']], 201);
        } catch (Exception $e) {
            errorResponse('Failed to create staff member: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/staff/:id
    public function updateStaffMember($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $existing = $this->db->fetchOne("SELECT id FROM staff WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Staff member not found', 404);
        }

        $data = [];
        $allowedFields = ['name', 'position', 'bio', 'email', 'phone', 'image_url',
                          'is_director', 'display_order', 'is_active'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (empty($data)) {
            errorResponse('No valid fields to update', 400);
        }

        $this->db->update('staff', $data, 'id = ?', [$id]);
        jsonResponse(['message' => 'Staff member updated']);
    }

    // DELETE /api/staff/:id
    public function deleteStaffMember($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $deleted = $this->db->delete('staff', 'id = ?', [$id]);

        if ($deleted === 0) {
            errorResponse('Staff member not found', 404);
        }

        jsonResponse(['message' => 'Staff member deleted']);
    }

    // POST /api/staff/reorder
    public function reorderStaffMembers() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);
        $staffMembers = $input['staffMembers'] ?? [];

        if (empty($staffMembers)) {
            errorResponse('No staff members provided', 400);
        }

        $this->db->beginTransaction();

        try {
            foreach ($staffMembers as $index => $member) {
                if (isset($member['id'])) {
                    $this->db->update('staff', ['display_order' => $index], 'id = ?', [$member['id']]);
                }
            }

            $this->db->commit();
            jsonResponse(['message' => 'Staff members reordered']);
        } catch (Exception $e) {
            $this->db->rollBack();
            errorResponse('Reorder failed: ' . $e->getMessage(), 500);
        }
    }

    // ===== SCHOOL STAFF ENDPOINTS =====

    // GET /api/schoolstaff
    public function getSchoolStaff() {
        $staff = $this->db->fetchAll(
            "SELECT * FROM school_staff WHERE is_active = 1 ORDER BY display_order, name"
        );
        jsonResponse($staff);
    }

    // GET /api/schoolstaff/:id
    public function getSchoolStaffMember($id) {
        $member = $this->db->fetchOne(
            "SELECT * FROM school_staff WHERE id = ?",
            [$id]
        );

        if (!$member) {
            errorResponse('School staff member not found', 404);
        }

        jsonResponse($member);
    }

    // POST /api/schoolstaff
    public function createSchoolStaffMember() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['name'])) {
            errorResponse('Name is required', 400);
        }

        $data = [
            'name' => $input['name'],
            'position' => $input['position'] ?? null,
            'bio' => $input['bio'] ?? null,
            'email' => $input['email'] ?? null,
            'phone' => $input['phone'] ?? null,
            'department' => $input['department'] ?? null,
            'display_order' => $input['display_order'] ?? 0,
            'is_active' => $input['is_active'] ?? true
        ];

        try {
            $id = $this->db->insert('school_staff', $data);
            jsonResponse(['message' => 'School staff member created', 'id' => $id], 201);
        } catch (Exception $e) {
            errorResponse('Failed to create school staff member: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/schoolstaff/:id
    public function updateSchoolStaffMember($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $existing = $this->db->fetchOne("SELECT id FROM school_staff WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('School staff member not found', 404);
        }

        $data = [];
        $allowedFields = ['name', 'position', 'bio', 'email', 'phone', 'department',
                          'display_order', 'is_active'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (empty($data)) {
            errorResponse('No valid fields to update', 400);
        }

        $this->db->update('school_staff', $data, 'id = ?', [$id]);
        jsonResponse(['message' => 'School staff member updated']);
    }

    // DELETE /api/schoolstaff/:id
    public function deleteSchoolStaffMember($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $deleted = $this->db->delete('school_staff', 'id = ?', [$id]);

        if ($deleted === 0) {
            errorResponse('School staff member not found', 404);
        }

        jsonResponse(['message' => 'School staff member deleted']);
    }

    // PUT /api/schoolstaff/bulk/positions
    public function bulkUpdateSchoolStaff() {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);
        $staffList = $input['staffList'] ?? [];

        if (empty($staffList)) {
            errorResponse('No staff list provided', 400);
        }

        $this->db->beginTransaction();

        try {
            foreach ($staffList as $index => $member) {
                if (isset($member['id'])) {
                    $this->db->update('school_staff', ['display_order' => $index], 'id = ?', [$member['id']]);
                }
            }

            $this->db->commit();
            jsonResponse(['message' => 'School staff positions updated']);
        } catch (Exception $e) {
            $this->db->rollBack();
            errorResponse('Bulk update failed: ' . $e->getMessage(), 500);
        }
    }

    // GET /api/schoolstaff/:staffId/image
    public function getStaffImage($staffId) {
        $image = $this->db->fetchOne(
            "SELECT * FROM staff_images WHERE staff_id = ?",
            [$staffId]
        );

        if (!$image) {
            errorResponse('Staff image not found', 404);
        }

        jsonResponse($image);
    }

    // POST /api/schoolstaff/:staffId/image
    public function setStaffImage($staffId) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        // Verify staff member exists
        $staff = $this->db->fetchOne("SELECT id FROM school_staff WHERE id = ?", [$staffId]);
        if (!$staff) {
            errorResponse('Staff member not found', 404);
        }

        // Check if image already exists
        $existing = $this->db->fetchOne("SELECT id FROM staff_images WHERE staff_id = ?", [$staffId]);

        $data = [
            'staff_id' => $staffId,
            'image_filename' => $input['image_filename'],
            'image_url' => $input['image_url'],
            'alt_text' => $input['alt_text'] ?? null
        ];

        try {
            if ($existing) {
                // Update existing
                unset($data['staff_id']);
                $this->db->update('staff_images', $data, 'staff_id = ?', [$staffId]);
            } else {
                // Insert new
                $this->db->insert('staff_images', $data);
            }

            jsonResponse(['message' => 'Staff image set']);
        } catch (Exception $e) {
            errorResponse('Failed to set staff image: ' . $e->getMessage(), 500);
        }
    }

    // DELETE /api/schoolstaff/:staffId/image
    public function deleteStaffImage($staffId) {
        AuthMiddleware::requireEditorOrAdmin();

        $deleted = $this->db->delete('staff_images', 'staff_id = ?', [$staffId]);

        if ($deleted === 0) {
            errorResponse('Staff image not found', 404);
        }

        jsonResponse(['message' => 'Staff image deleted']);
    }
}
