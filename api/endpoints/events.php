<?php
// ====================================================
// Events and Calendar Endpoints
// ====================================================

class EventsEndpoints {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // GET /api/events
    public function getEvents() {
        $locale = $_GET['locale'] ?? 'en';
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;

        $sql = "SELECT * FROM events WHERE language = ? AND is_published = 1";
        $params = [$locale];

        if ($start && $end) {
            $sql .= " AND start_date >= ? AND end_date <= ?";
            $params[] = $start;
            $params[] = $end;
        }

        $sql .= " ORDER BY start_date ASC";

        $events = $this->db->fetchAll($sql, $params);
        jsonResponse(['events' => $events]);
    }

    // GET /api/events/public/upcoming
    public function getUpcomingEvents() {
        $locale = $_GET['locale'] ?? 'en';
        $limit = (int)($_GET['limit'] ?? 10);

        $events = $this->db->fetchAll(
            "SELECT * FROM events
             WHERE language = ? AND is_published = 1 AND start_date >= NOW()
             ORDER BY start_date ASC
             LIMIT ?",
            [$locale, $limit]
        );

        jsonResponse(['events' => $events]);
    }

    // GET /api/events/:id
    public function getEvent($id) {
        $event = $this->db->fetchOne(
            "SELECT * FROM events WHERE id = ?",
            [$id]
        );

        if (!$event) {
            errorResponse('Event not found', 404);
        }

        jsonResponse(['event' => $event]);
    }

    // POST /api/events
    public function createEvent() {
        $user = AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['title', 'start_date'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                errorResponse("Field '$field' is required", 400);
            }
        }

        $data = [
            'title' => $input['title'],
            'description' => $input['description'] ?? null,
            'content' => $input['content'] ?? null,
            'language' => $input['language'] ?? 'bg',
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'] ?? null,
            'all_day' => $input['all_day'] ?? false,
            'location' => $input['location'] ?? null,
            'color' => $input['color'] ?? null,
            'category' => $input['category'] ?? null,
            'is_published' => $input['is_published'] ?? true,
            'created_by' => $user['id']
        ];

        try {
            $id = $this->db->insert('events', $data);
            jsonResponse(['message' => 'Event created', 'id' => $id], 201);
        } catch (Exception $e) {
            errorResponse('Failed to create event: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/events/:id
    public function updateEvent($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $input = json_decode(file_get_contents('php://input'), true);

        // Check if event exists
        $existing = $this->db->fetchOne("SELECT id FROM events WHERE id = ?", [$id]);
        if (!$existing) {
            errorResponse('Event not found', 404);
        }

        $data = [];
        $allowedFields = ['title', 'description', 'content', 'language', 'start_date', 'end_date',
                          'all_day', 'location', 'color', 'category', 'is_published'];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (empty($data)) {
            errorResponse('No valid fields to update', 400);
        }

        $this->db->update('events', $data, 'id = ?', [$id]);
        jsonResponse(['message' => 'Event updated']);
    }

    // DELETE /api/events/:id
    public function deleteEvent($id) {
        AuthMiddleware::requireEditorOrAdmin();

        $deleted = $this->db->delete('events', 'id = ?', [$id]);

        if ($deleted === 0) {
            errorResponse('Event not found', 404);
        }

        jsonResponse(['message' => 'Event deleted']);
    }
}
