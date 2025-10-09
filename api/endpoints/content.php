<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'getAll';

switch ($action) {
    case 'getSingle':
        if (isset($_GET['key'])) {
            $key = $conn->real_escape_string($_GET['key']);
            $sql = "SELECT * FROM content WHERE content_key = '$key'";
            $result = $conn->query($sql);
            $content_item = $result->fetch_assoc();
            header('Content-Type: application/json');
            echo json_encode($content_item);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Key is required for getSingle action']);
        }
        break;

    case 'save': // This action handles both creating and updating
        AuthMiddleware::check();
        $data = json_decode(file_get_contents('php://input'), true);
        $key = $conn->real_escape_string($data['content_key']);
        $value = $conn->real_escape_string($data['content_value']);
        // Use INSERT ... ON DUPLICATE KEY UPDATE for a single, efficient query
        $sql = "INSERT INTO content (content_key, content_value) VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'Content saved successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error saving content: ' . $conn->error]);
        }
        break;
        
    case 'delete':
        AuthMiddleware::check();
        if (isset($_GET['key'])) {
            $key = $conn->real_escape_string($_GET['key']);
            $sql = "DELETE FROM content WHERE content_key = '$key'";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Content deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error deleting content: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Key is required for delete action']);
        }
        break;

    case 'getAll':
    default:
        $sql = "SELECT * FROM content";
        $result = $conn->query($sql);
        $content = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $content[] = $row;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($content);
        break;
}
?>