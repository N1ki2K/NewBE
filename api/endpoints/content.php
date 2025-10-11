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
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON payload']);
            break;
        }

        if (isset($data['content_key']) && isset($data['content_value'])) {
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
        }

        if (isset($data['id']) && isset($data['content'])) {
            $key = $conn->real_escape_string($data['id']);
            $valueRaw = $data['content'];
            $value = $conn->real_escape_string(is_array($valueRaw) ? json_encode($valueRaw) : (string) $valueRaw);
            $pageId = isset($data['page_id']) ? $conn->real_escape_string($data['page_id']) : null;
            $label = isset($data['label']) ? $conn->real_escape_string($data['label']) : $key;
            $type = isset($data['type']) ? $conn->real_escape_string($data['type']) : 'text';

            $sql = "INSERT INTO content (content_key, content_value, page_id, label, type)
                    VALUES ('$key', '$value', " . ($pageId ? "'$pageId'" : "NULL") . ", '$label', '$type')
                    ON DUPLICATE KEY UPDATE
                        content_value = VALUES(content_value),
                        page_id = VALUES(page_id),
                        label = VALUES(label),
                        type = VALUES(type)";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Content saved successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error saving content: ' . $conn->error]);
            }
            break;
        }

        http_response_code(400);
        echo json_encode(['error' => 'Missing content_key/content_value or id/content in payload']);
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
