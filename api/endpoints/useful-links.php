<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'getAll';

switch ($action) {
    case 'create':
        AuthMiddleware::check();
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $conn->real_escape_string($data['title']);
        $url = $conn->real_escape_string($data['url']);
        $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : null;
        $sql = "INSERT INTO useful_links (title, url, description) VALUES ('$title', '$url', '$description')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'Link created successfully', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error creating link: ' . $conn->error]);
        }
        break;
        
    case 'update':
        AuthMiddleware::check();
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);
            $data = json_decode(file_get_contents('php://input'), true);
            $title = $conn->real_escape_string($data['title']);
            $url = $conn->real_escape_string($data['url']);
            $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : null;
            $sql = "UPDATE useful_links SET title = '$title', url = '$url', description = '$description' WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Link updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error updating link: ' . $conn->error]);
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
            $sql = "DELETE FROM useful_links WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Link deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error deleting link: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for delete action']);
        }
        break;

    case 'getAll':
    default:
        $sql = "SELECT * FROM useful_links ORDER BY title ASC";
        $result = $conn->query($sql);
        $links = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $links[] = $row;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($links);
        break;
}
?>