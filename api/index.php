<?php

// Include necessary files
require_once 'cors.php';
require_once 'config.php';
require_once 'database.php';
require_once 'middleware.php';

// Establish a database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Simple router
$request_uri = $_SERVER['REQUEST_URI'];
$endpoint = str_replace('/api/', '', $request_uri);
$endpoint = strtok($endpoint, '?'); // Remove query string

// Route requests to the appropriate endpoint
switch ($endpoint) {
    case 'health':
        echo json_encode(['status' => 'ok']);
        break;
    case 'login':
    case 'register':
        require_once 'endpoints/auth.php';
        break;
    case 'news':
        require_once 'endpoints/news.php';
        break;
    case 'events':
        require_once 'endpoints/events.php';
        break;
    case 'staff':
        require_once 'endpoints/staff.php';
        break;
    case 'patron':
        require_once 'endpoints/patron.php';
        break;
    case 'pages':
        require_once 'endpoints/pages.php';
        break;
    case 'content':
        require_once 'endpoints/content.php';
        break;
    case 'useful-links':
        require_once 'endpoints/useful-links.php';
        break;
    // New route for posts
    case 'posts':
        require_once 'endpoints/posts.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
}

// Close the database connection
$conn->close();
?>