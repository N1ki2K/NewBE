<?php

if (!function_exists('news_has_author_column')) {
    function news_has_author_column($conn) {
        static $hasAuthor = null;

        if ($hasAuthor !== null) {
            return $hasAuthor;
        }

        $result = @$conn->query("SHOW COLUMNS FROM news LIKE 'author_id'");
        if ($result instanceof mysqli_result) {
            $hasAuthor = $result->num_rows > 0;
            $result->free();
        } else {
            $hasAuthor = false;
        }

        return $hasAuthor;
    }
}

$hasAuthorColumn = news_has_author_column($conn);
$action = isset($_GET['action']) ? $_GET['action'] : 'getAll';

switch ($action) {
    case 'getSingle':
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);

            if ($hasAuthorColumn) {
                $sql = "SELECT n.*, u.name AS author_name
                        FROM news n
                        LEFT JOIN users u ON n.author_id = u.id
                        WHERE n.id = $id";
            } else {
                $sql = "SELECT * FROM news WHERE id = $id";
            }

            $result = $conn->query($sql);
            $article = $result ? $result->fetch_assoc() : null;
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
        $image_url = isset($data['image_url']) ? $conn->real_escape_string($data['image_url']) : null;
        $imageValue = $image_url !== null && $image_url !== '' ? "'" . $image_url . "'" : "NULL";

        if ($hasAuthorColumn) {
            $author_id = isset($data['author_id']) ? $conn->real_escape_string($data['author_id']) : null;
            $authorValue = $author_id !== null && $author_id !== '' ? "'" . $author_id . "'" : "NULL";
            $sql = "INSERT INTO news (title, content, author_id, image_url) VALUES ('$title', '$content', $authorValue, $imageValue)";
        } else {
            $sql = "INSERT INTO news (title, content, image_url) VALUES ('$title', '$content', $imageValue)";
        }

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
            $imageValue = $image_url !== null && $image_url !== '' ? "'" . $image_url . "'" : "NULL";

            $sql = "UPDATE news SET title = '$title', content = '$content', image_url = $imageValue";

            if ($hasAuthorColumn && isset($data['author_id'])) {
                $author_id = $conn->real_escape_string($data['author_id']);
                $authorValue = $author_id !== '' ? "'" . $author_id . "'" : "NULL";
                $sql .= ", author_id = $authorValue";
            }

            $sql .= " WHERE id = $id";

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
        if ($hasAuthorColumn) {
            $sql = "SELECT n.*, u.name as author_name 
                    FROM news n
                    LEFT JOIN users u ON n.author_id = u.id
                    ORDER BY n.created_at DESC";
        } else {
            $sql = "SELECT * FROM news ORDER BY created_at DESC";
        }
        $result = $conn->query($sql);
        $news = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $news[] = $row;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($news);
        break;
}
?>
