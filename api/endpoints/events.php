<?php

class EventsEndpoints
{
    private const ALLOWED_TYPES = ['academic', 'extracurricular', 'meeting', 'holiday', 'other'];
    private const TABLE_NAME = 'evengs';

    /** @var Database */
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function handle(array $segments, string $method): void
    {
        // Legacy action-based API support (?action=create, etc.)
        if (isset($_GET['action'])) {
            $this->handleLegacyAction((string) $_GET['action'], $segments, $method);
            return;
        }

        $first = $segments[0] ?? '';

        if ($first === 'public') {
            $this->handlePublic(array_slice($segments, 1), $method);
            return;
        }

        if ($first !== '') {
            $this->handleSingle($first, array_slice($segments, 1), $method);
            return;
        }

        switch ($method) {
            case 'GET':
                $this->listEvents();
                return;
            case 'POST':
                AuthMiddleware::check();
                $this->createEvent();
                return;
            default:
                errorResponse('Method Not Allowed', 405);
        }
    }

    private function handleLegacyAction(string $action, array $segments, string $method): void
    {
        switch ($action) {
            case 'create':
                AuthMiddleware::check();
                $this->createEvent();
                return;

            case 'update':
                AuthMiddleware::check();
                $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                if ($id <= 0) {
                    errorResponse('ID is required for update action', 400);
                }
                $this->updateEvent($id);
                return;

            case 'delete':
                AuthMiddleware::check();
                $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                if ($id <= 0) {
                    errorResponse('ID is required for delete action', 400);
                }
                $this->deleteEvent($id);
                return;

            case 'getSingle':
                $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                if ($id <= 0) {
                    errorResponse('ID is required for getSingle action', 400);
                }
                $this->getEvent($id, true);
                return;

            case 'getAll':
            default:
                $events = $this->listEvents(true);
                jsonResponse($events);
        }
    }

    private function handlePublic(array $segments, string $method): void
    {
        if ($method !== 'GET') {
            errorResponse('Method Not Allowed', 405);
        }

        $target = $segments[0] ?? '';

        if ($target === 'upcoming') {
            $this->getUpcomingEvents();
            return;
        }

        errorResponse('Not Found', 404);
    }

    private function handleSingle(string $id, array $segments, string $method): void
    {
        if (!ctype_digit($id)) {
            errorResponse('Invalid event ID', 400);
        }

        $eventId = (int) $id;

        switch ($method) {
            case 'GET':
                $this->getEvent($eventId);
                return;

            case 'PUT':
            case 'PATCH':
                AuthMiddleware::check();
                $this->updateEvent($eventId);
                return;

            case 'DELETE':
                AuthMiddleware::check();
                $this->deleteEvent($eventId);
                return;

            default:
                errorResponse('Method Not Allowed', 405);
        }
    }

    private function listEvents(bool $returnRaw = false)
    {
        $conditions = [];
        $params = [];

        $locale = isset($_GET['locale']) ? strtolower(trim((string) $_GET['locale'])) : null;
        if ($locale) {
            $conditions[] = 'locale = :locale';
            $params['locale'] = $locale;
        }

        $start = isset($_GET['start']) ? trim((string) $_GET['start']) : null;
        $end = isset($_GET['end']) ? trim((string) $_GET['end']) : null;

        if ($start && $end) {
            $conditions[] = 'date BETWEEN :start AND :end';
            $params['start'] = $start;
            $params['end'] = $end;
        } elseif ($start) {
            $conditions[] = 'date >= :start';
            $params['start'] = $start;
        } elseif ($end) {
            $conditions[] = 'date <= :end';
            $params['end'] = $end;
        }

        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "
            SELECT 
                id,
                title,
                description,
                date,
                startTime,
                endTime,
                type,
                location,
                locale,
                created_at,
                updated_at
            FROM " . self::TABLE_NAME . "
            $whereClause
            ORDER BY date DESC, startTime ASC
        ";

        $rows = $this->db->fetchAll($sql, $params);
        $events = array_map([$this, 'formatEvent'], $rows);

        if ($returnRaw) {
            return $events;
        }

        jsonResponse(['events' => $events]);
    }

    private function getEvent(int $id, bool $legacy = false): void
    {
        $event = $this->findEvent($id);

        if (!$event) {
            errorResponse('Event not found', 404);
        }

        if ($legacy) {
            jsonResponse($event);
            return;
        }

        jsonResponse(['event' => $event]);
    }

    private function createEvent(): void
    {
        $payload = $this->getJsonInput();
        $data = $this->validateEventPayload($payload, true);

        $insertData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'date' => $data['date'],
            'startTime' => $data['startTime'],
            'endTime' => $data['endTime'],
            'type' => $data['type'],
            'location' => $data['location'],
            'locale' => $data['locale'],
        ];

        $eventId = (int) $this->db->insert(self::TABLE_NAME, $insertData);
        $event = $this->findEvent($eventId);

        if (!$event) {
            errorResponse('Failed to fetch created event', 500);
        }

        successResponse(['event' => $event], 'Event created successfully');
    }

    private function updateEvent(int $id): void
    {
        $existing = $this->findEvent($id);

        if (!$existing) {
            errorResponse('Event not found', 404);
        }

        $payload = $this->getJsonInput();
        if (empty($payload)) {
            errorResponse('No data provided for update', 400);
        }

        $data = $this->validateEventPayload($payload, false, $existing);

        $updateData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'date' => $data['date'],
            'startTime' => $data['startTime'],
            'endTime' => $data['endTime'],
            'type' => $data['type'],
            'location' => $data['location'],
            'locale' => $data['locale'],
        ];

        $this->db->update(self::TABLE_NAME, $updateData, 'id = :id', ['id' => $id]);

        $event = $this->findEvent($id);

        if (!$event) {
            errorResponse('Failed to fetch updated event', 500);
        }

        successResponse(['event' => $event], 'Event updated successfully');
    }

    private function deleteEvent(int $id): void
    {
        $existing = $this->findEvent($id);

        if (!$existing) {
            errorResponse('Event not found', 404);
        }

        $this->db->delete(self::TABLE_NAME, 'id = :id', ['id' => $id]);

        successResponse(null, 'Event deleted successfully');
    }

    private function getUpcomingEvents(): void
    {
        $locale = isset($_GET['locale']) ? strtolower(trim((string) $_GET['locale'])) : null;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        if ($limit <= 0) {
            $limit = 10;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $conditions = ['date >= CURDATE()'];
        $params = [];

        if ($locale) {
            $conditions[] = 'locale = :locale';
            $params['locale'] = $locale;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT 
                id,
                title,
                description,
                date,
                startTime,
                endTime,
                type,
                location,
                locale,
                created_at,
                updated_at
            FROM " . self::TABLE_NAME . "
            $whereClause
            ORDER BY date ASC, startTime ASC
            LIMIT $limit
        ";

        $rows = $this->db->fetchAll($sql, $params);
        $events = array_map([$this, 'formatEvent'], $rows);

        jsonResponse(['events' => $events]);
    }

    private function findEvent(int $id): ?array
    {
        $sql = "
            SELECT 
                id,
                title,
                description,
                date,
                startTime,
                endTime,
                type,
                location,
                locale,
                created_at,
                updated_at
            FROM " . self::TABLE_NAME . "
            WHERE id = :id
        ";

        $event = $this->db->fetchOne($sql, ['id' => $id]);

        if (!$event) {
            return null;
        }

        return $this->formatEvent($event);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            errorResponse('Invalid JSON payload', 400);
        }

        return $data;
    }

    private function validateEventPayload(array $data, bool $isCreate = false, array $existing = []): array
    {
        $title = array_key_exists('title', $data) ? trim((string) $data['title']) : ($existing['title'] ?? '');
        $description = array_key_exists('description', $data) ? trim((string) $data['description']) : ($existing['description'] ?? '');
        $date = array_key_exists('date', $data) ? trim((string) $data['date']) : ($existing['date'] ?? '');
        $startTime = array_key_exists('startTime', $data) ? trim((string) $data['startTime']) : ($existing['startTime'] ?? '');
        $endTime = array_key_exists('endTime', $data) ? trim((string) $data['endTime']) : ($existing['endTime'] ?? null);
        $type = array_key_exists('type', $data) ? strtolower(trim((string) $data['type'])) : ($existing['type'] ?? 'other');
        $location = array_key_exists('location', $data) ? trim((string) $data['location']) : ($existing['location'] ?? null);
        $locale = array_key_exists('locale', $data) ? strtolower(trim((string) $data['locale'])) : ($existing['locale'] ?? 'bg');

        if ($isCreate && $title === '') {
            errorResponse('Event title is required', 422);
        }

        if ($isCreate && $date === '') {
            errorResponse('Event date is required', 422);
        }

        if ($isCreate && $startTime === '') {
            errorResponse('Event start time is required', 422);
        }

        if ($date !== '') {
            $dateObj = DateTime::createFromFormat('Y-m-d', $date);
            if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
                errorResponse('Event date must be in YYYY-MM-DD format', 422);
            }
        }

        if ($startTime !== '') {
            $startObj = DateTime::createFromFormat('H:i', $startTime);
            if (!$startObj || $startObj->format('H:i') !== $startTime) {
                errorResponse('Event start time must be in HH:MM format', 422);
            }
        }

        if ($endTime !== null && $endTime !== '') {
            $endObj = DateTime::createFromFormat('H:i', $endTime);
            if (!$endObj || $endObj->format('H:i') !== $endTime) {
                errorResponse('Event end time must be in HH:MM format', 422);
            }
        } else {
            $endTime = null;
        }

        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            $type = 'other';
        }

        if ($locale === '') {
            $locale = 'bg';
        }

        return [
            'title' => $title,
            'description' => $description,
            'date' => $date,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'type' => $type,
            'location' => $location ?? null,
            'locale' => $locale,
        ];
    }

    private function formatEvent(array $event): array
    {
        return [
            'id' => isset($event['id']) ? (int) $event['id'] : null,
            'title' => $event['title'] ?? '',
            'description' => $event['description'] ?? '',
            'date' => $event['date'] ?? '',
            'startTime' => $event['startTime'] ?? '',
            'endTime' => $event['endTime'] ?? null,
            'type' => $event['type'] ?? 'other',
            'location' => $event['location'] ?? null,
            'locale' => $event['locale'] ?? 'bg',
            'createdAt' => $event['created_at'] ?? null,
            'updatedAt' => $event['updated_at'] ?? null,
        ];
    }
}

$segments = isset($segments) && is_array($segments) ? $segments : [];
$requestMethod = isset($requestMethod) ? $requestMethod : ($_SERVER['REQUEST_METHOD'] ?? 'GET');

$eventsEndpoint = new EventsEndpoints();
$eventsEndpoint->handle(array_slice($segments, 1), $requestMethod);
