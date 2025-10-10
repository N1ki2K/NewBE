<?php
// ====================================================
// Configuration File
// ====================================================

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nukgszco_3ou_Cms');
define('DB_USER', 'nukgszco_nukgszc');
define('DB_PASS', 'hk~Gn-EG7f8J');
define('DB_CHARSET', 'utf8mb4');

$backendPublicEnv = getenv('BACKEND_PUBLIC_PATH');
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';

if ($backendPublicEnv !== false && $backendPublicEnv !== '') {
    $backendPublicPath = '/' . ltrim(trim($backendPublicEnv), '/');
} elseif (!empty($scriptName)) {
    $scriptDir = rtrim(dirname($scriptName), '/\\');
    $cleanDir = preg_replace('#/api$#', '', $scriptDir);
    $backendPublicPath = $cleanDir === '' ? '' : '/' . ltrim($cleanDir, '/');
} else {
    $backendPublicPath = '/backend';
}

define('BACKEND_PUBLIC_PATH', $backendPublicPath);

$uploadPublicBase = ($backendPublicPath === '' ? '' : $backendPublicPath) . '/uploads';
$uploadPicturesPublicPath = $uploadPublicBase . '/pictures';
$uploadDocumentsPublicPath = $uploadPublicBase . '/documents';
$uploadPresentationsPublicPath = $uploadPublicBase . '/presentations';

define('UPLOAD_PUBLIC_BASE', $uploadPublicBase);
define('UPLOAD_PICTURES_PUBLIC_PATH', $uploadPicturesPublicPath);
define('UPLOAD_DOCUMENTS_PUBLIC_PATH', $uploadDocumentsPublicPath);
define('UPLOAD_PRESENTATIONS_PUBLIC_PATH', $uploadPresentationsPublicPath);

// JWT Configuration
define('JWT_SECRET', 'gramatikovkazacheshtedoidevofisavpetnajseineshtoanieoshtegochakamgevosemnaiseipolovina');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 86400); // 24 hours in seconds

// CORS Configuration (compatibility for older PHP versions)
if (!function_exists('get_allowed_origins')) {
    function get_allowed_origins() {
        return array(
            'http://localhost:3000',
            'http://localhost:5173',
            'https://nukgsz.com',
            'https://www.nukgsz.com'
        );
    }
}

// Upload Configuration
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('UPLOAD_PICTURES_DIR', UPLOAD_DIR . 'pictures/');
define('UPLOAD_DOCUMENTS_DIR', UPLOAD_DIR . 'documents/');
define('UPLOAD_PRESENTATIONS_DIR', UPLOAD_DIR . 'presentations/');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB

// Allowed file types (compatibility helpers)
if (!function_exists('get_allowed_image_types')) {
    function get_allowed_image_types() {
        return array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
    }
}

if (!function_exists('get_allowed_document_types')) {
    function get_allowed_document_types() {
        return array(
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }
}

if (!function_exists('get_allowed_presentation_types')) {
    function get_allowed_presentation_types() {
        return array(
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        );
    }
}

// API Configuration
define('API_VERSION', 'v1');
define('API_BASE_PATH', '/api');

// Timezone
date_default_timezone_set('Europe/Sofia');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(UPLOAD_PICTURES_DIR)) {
    mkdir(UPLOAD_PICTURES_DIR, 0755, true);
}
if (!file_exists(UPLOAD_DOCUMENTS_DIR)) {
    mkdir(UPLOAD_DOCUMENTS_DIR, 0755, true);
}
if (!file_exists(UPLOAD_PRESENTATIONS_DIR)) {
    mkdir(UPLOAD_PRESENTATIONS_DIR, 0755, true);
}

// Helper Functions
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function errorResponse($message, $statusCode = 400) {
    jsonResponse(['error' => $message], $statusCode);
}

function successResponse($data, $message = '') {
    $response = ['success' => true];
    if ($message) {
        $response['message'] = $message;
    }
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    jsonResponse($response);
}
