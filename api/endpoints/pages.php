<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'getAll';

switch ($action) {
    case 'getSingle':
         if (isset($_GET['slug'])) {
            $slug = $conn->real_escape_string($_GET['slug']);
            $sql = "SELECT * FROM pages WHERE slug = '$slug'";
            $result = $conn->query($sql);
            $page = $result->fetch_assoc();
            header('Content-Type: application/json');
            echo json_encode($page);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Slug is required for getSingle action']);
        }
        break;
        
    case 'create':
        AuthMiddleware::check();
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $conn->real_escape_string($data['title']);
        $slug = $conn->real_escape_string($data['slug']);
        $content = $conn->real_escape_string($data['content']);
        $sql = "INSERT INTO pages (title, slug, content) VALUES ('$title', '$slug', '$content')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'Page created successfully', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error creating page: ' . $conn->error]);
        }
        break;

    case 'update':
        AuthMiddleware::check();
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);
            $data = json_decode(file_get_contents('php://input'), true);
            $title = $conn->real_escape_string($data['title']);
            $slug = $conn->real_escape_string($data['slug']);
            $content = $conn->real_escape_string($data['content']);
            $sql = "UPDATE pages SET title = '$title', slug = '$slug', content = '$content' WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Page updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error updating page: ' . $conn->error]);
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
            $sql = "DELETE FROM pages WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Page deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error deleting page: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for delete action']);
        }
        break;

    case 'getAll':
    default:
        $sql = "SELECT id, title, slug FROM pages ORDER BY title ASC";
        $result = $conn->query($sql);
        $pages = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $pages[] = $row;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($pages);
        break;
}
?>