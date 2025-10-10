<?php

// Include necessary files
require_once 'cors.php';
require_once 'config.php';
require_once 'database.php';
require_once 'middleware.php';

// Helper to create mysqli connection when needed
function create_mysqli_connection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        errorResponse('Database connection failed: ' . $conn->connect_error, 500);
    }
    return $conn;
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestPath = preg_replace('#^/api/?#', '', $requestPath);
$requestPath = trim($requestPath, '/');
$segments = $requestPath === '' ? [] : explode('/', $requestPath);
$primary = isset($segments[0]) ? $segments[0] : '';

switch ($primary) {
    case '':
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;

    case 'health':
        echo json_encode(['status' => 'ok']);
        break;

    case 'auth':
        require_once 'endpoints/auth.php';
        $auth = new AuthEndpoints();
        $action = isset($segments[1]) ? $segments[1] : '';

        if ($action === 'login' && $requestMethod === 'POST') {
            $auth->login();
        } elseif ($action === 'logout' && $requestMethod === 'POST') {
            $auth->logout();
        } elseif ($action === 'me' && $requestMethod === 'GET') {
            $auth->getCurrentUser();
        } elseif ($action === 'change-password' && $requestMethod === 'POST') {
            $auth->changePassword();
        } else {
            errorResponse('Not Found', 404);
        }
        break;

    case 'login':
        // Legacy route support for older clients
        if ($requestMethod === 'POST') {
            require_once 'endpoints/auth.php';
            $auth = new AuthEndpoints();
            $auth->login();
        } else {
            errorResponse('Not Found', 404);
        }
        break;

    case 'news':
    case 'events':
    case 'staff':
    case 'patron':
    case 'pages':
    case 'content':
    case 'useful-links':
    case 'posts':
        $conn = create_mysqli_connection();
        require_once "endpoints/{$primary}.php";
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close();
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
}
