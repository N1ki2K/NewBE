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

        $columns = [];
        if ($result = $conn->query("SHOW COLUMNS FROM content")) {
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = true;
            }
            $result->free();
        }

        $keyColumn = isset($columns['content_key']) ? 'content_key' : (isset($columns['id']) ? 'id' : null);
        $valueColumn = isset($columns['content_value']) ? 'content_value' : (isset($columns['content']) ? 'content' : null);
        $pageColumn = isset($columns['page_id']) ? 'page_id' : null;
        $labelColumn = isset($columns['label']) ? 'label' : null;
        $typeColumn = isset($columns['type']) ? 'type' : null;

        if ($keyColumn === null || $valueColumn === null) {
            http_response_code(500);
            echo json_encode(['error' => 'Content table schema not supported']);
            break;
        }

        $providedKey = $data['content_key'] ?? $data['id'] ?? null;
        $providedValue = $data['content_value'] ?? $data['content'] ?? null;

        if ($providedKey === null || $providedValue === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing content key or value']);
            break;
        }

        $keyValue = $conn->real_escape_string((string) $providedKey);
        $valueRaw = $providedValue;
        $valueEscaped = $conn->real_escape_string(is_array($valueRaw) ? json_encode($valueRaw) : (string) $valueRaw);

        $fields = [$keyColumn, $valueColumn];
        $values = ["'$keyValue'", "'$valueEscaped'"];
        $updates = ["$valueColumn = VALUES($valueColumn)"];

        if ($pageColumn !== null) {
            $pageValue = $data['page_id'] ?? null;
            $fields[] = $pageColumn;
            $values[] = $pageValue !== null ? "'" . $conn->real_escape_string((string) $pageValue) . "'" : "NULL";
            $updates[] = "$pageColumn = VALUES($pageColumn)";
        }

        if ($labelColumn !== null) {
            $labelValue = $data['label'] ?? $providedKey;
            $fields[] = $labelColumn;
            $values[] = "'" . $conn->real_escape_string((string) $labelValue) . "'";
            $updates[] = "$labelColumn = VALUES($labelColumn)";
        }

        if ($typeColumn !== null) {
            $typeValue = $data['type'] ?? 'text';
            $fields[] = $typeColumn;
            $values[] = "'" . $conn->real_escape_string((string) $typeValue) . "'";
            $updates[] = "$typeColumn = VALUES($typeColumn)";
        }

        $sql = "INSERT INTO content (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
        if (!empty($updates)) {
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
        }

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
