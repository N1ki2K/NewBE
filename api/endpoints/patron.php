<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'get';

switch ($action) {
    case 'update':
        AuthMiddleware::check();
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $conn->real_escape_string($data['name']);
        $bio = $conn->real_escape_string($data['bio']);
        $image_url = isset($data['image_url']) ? $conn->real_escape_string($data['image_url']) : null;
        // The patron table is expected to have only one row, so we update it without a WHERE clause or with LIMIT 1.
        $sql = "UPDATE patron SET name = '$name', bio = '$bio', image_url = '$image_url' LIMIT 1";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['message' => 'Patron updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error updating patron: ' . $conn->error]);
        }
        break;

    case 'get':
    default:
        $sql = "SELECT * FROM patron LIMIT 1";
        $result = $conn->query($sql);
        $patron = null;
        if ($result && $result->num_rows > 0) {
            $patron = $result->fetch_assoc();
        }
        header('Content-Type: application/json');
        echo json_encode($patron);
        break;
}
?>