<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'getAll';

switch ($action) {
    case 'getSingle':
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);
            $sql = "SELECT n.*, u.name as author_name 
                    FROM news n
                    LEFT JOIN users u ON n.author_id = u.id
                    WHERE n.id = $id";
            $result = $conn->query($sql);
            $article = $result->fetch_assoc();
            header('Content-Type: application/json');
            echo json_encode($article);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for getSingle action']);
        }
        break;

    case 'create':
        AuthMiddleware::check();
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $conn->real_escape_string($data['title']);
        $content = $conn->real_escape_string($data['content']);
        $author_id = $conn->real_escape_string($data['author_id']);
        $image_url = isset($data['image_url']) ? $conn->real_escape_string($data['image_url']) : null;
        $sql = "INSERT INTO news (title, content, author_id, image_url) VALUES ('$title', '$content', '$author_id', '$image_url')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'News article created successfully', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error creating news article: ' . $conn->error]);
        }
        break;

    case 'update':
        AuthMiddleware::check();
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);
            $data = json_decode(file_get_contents('php://input'), true);
            $title = $conn->real_escape_string($data['title']);
            $content = $conn->real_escape_string($data['content']);
            $image_url = isset($data['image_url']) ? $conn->real_escape_string($data['image_url']) : null;
            $sql = "UPDATE news SET title = '$title', content = '$content', image_url = '$image_url' WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'News article updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error updating news article: ' . $conn->error]);
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
            $sql = "DELETE FROM news WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'News article deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error deleting news article: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for delete action']);
        }
        break;

    case 'getAll':
    default:
        // This is the original code from your file for fetching all news
        $sql = "SELECT n.*, u.name as author_name 
                FROM news n
                LEFT JOIN users u ON n.author_id = u.id
                ORDER BY n.created_at DESC";
        $result = $conn->query($sql);
        $news = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $news[] = $row;
            }
        }
        // NOTE: The large fallback data array has been removed as it should now come from the database.
        header('Content-Type: application/json');
        echo json_encode($news);
        break;
}
?>