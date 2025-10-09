<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'getAll';

switch ($action) {
    case 'getSingle':
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);
            $sql = "SELECT * FROM events WHERE id = $id";
            $result = $conn->query($sql);
            $event = $result->fetch_assoc();
            header('Content-Type: application/json');
            echo json_encode($event);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for getSingle action']);
        }
        break;

    case 'create':
        AuthMiddleware::check();
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $conn->real_escape_string($data['title']);
        $description = $conn->real_escape_string($data['description']);
        $event_date = $conn->real_escape_string($data['event_date']);
        $image_url = isset($data['image_url']) ? $conn->real_escape_string($data['image_url']) : null;
        $sql = "INSERT INTO events (title, description, event_date, image_url) VALUES ('$title', '$description', '$event_date', '$image_url')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'Event created successfully', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error creating event: ' . $conn->error]);
        }
        break;

    case 'update':
        AuthMiddleware::check();
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);
            $data = json_decode(file_get_contents('php://input'), true);
            $title = $conn->real_escape_string($data['title']);
            $description = $conn->real_escape_string($data['description']);
            $event_date = $conn->real_escape_string($data['event_date']);
            $image_url = isset($data['image_url']) ? $conn->real_escape_string($data['image_url']) : null;
            $sql = "UPDATE events SET title = '$title', description = '$description', event_date = '$event_date', image_url = '$image_url' WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Event updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error updating event: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for update action']);
        }
        break;

    case 'delete':
        AuthMiddleware::check();
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);
            $sql = "DELETE FROM events WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Event deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error deleting event: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for delete action']);
        }
        break;

    case 'getAll':
    default:
        $sql = "SELECT * FROM events ORDER BY event_date DESC";
        $result = $conn->query($sql);
        $events = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($events);
        break;
}
?>