<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'getAll';

switch ($action) {
    case 'getSingle':
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);
            $sql = "SELECT * FROM staff WHERE id = $id";
            $result = $conn->query($sql);
            $staff_member = $result->fetch_assoc();
            header('Content-Type: application/json');
            echo json_encode($staff_member);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for getSingle action']);
        }
        break;

    case 'create':
        AuthMiddleware::check();
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $conn->real_escape_string($data['name']);
        $position = $conn->real_escape_string($data['position']);
        $image_url = isset($data['image_url']) ? $conn->real_escape_string($data['image_url']) : null;
        $bio = isset($data['bio']) ? $conn->real_escape_string($data['bio']) : null;
        $sql = "INSERT INTO staff (name, position, image_url, bio) VALUES ('$name', '$position', '$image_url', '$bio')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'Staff member created successfully', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error creating staff member: ' . $conn->error]);
        }
        break;

    case 'update':
        AuthMiddleware::check();
        if (isset($_GET['id'])) {
            $id = $conn->real_escape_string($_GET['id']);
            $data = json_decode(file_get_contents('php://input'), true);
            $name = $conn->real_escape_string($data['name']);
            $position = $conn->real_escape_string($data['position']);
            $image_url = isset($data['image_url']) ? $conn->real_escape_string($data['image_url']) : null;
            $bio = isset($data['bio']) ? $conn->real_escape_string($data['bio']) : null;
            $sql = "UPDATE staff SET name = '$name', position = '$position', image_url = '$image_url', bio = '$bio' WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Staff member updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error updating staff member: ' . $conn->error]);
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
            $sql = "DELETE FROM staff WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['message' => 'Staff member deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error deleting staff member: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for delete action']);
        }
        break;

    case 'getAll':
    default:
        $sql = "SELECT * FROM staff ORDER BY name ASC";
        $result = $conn->query($sql);
        $staff = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $staff[] = $row;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($staff);
        break;
}
?>