<?php
// ====================================================
// Configuration File
// ====================================================

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'nukgszco_3ou_Cms');
define('DB_USER', 'nukgszco_nukgszc');
define('DB_PASS', 'hk~Gn-EG7f8J');
define('DB_CHARSET', 'utf8mb4');

if (!class_exists('Database')) {
    class Database {
        private static $instance = null;
        private $connection;

        private function __construct() {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                );

                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }

        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function getConnection() {
            return $this->connection;
        }

        public function query($sql, $params = array()) {
            try {
                $stmt = $this->connection->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            } catch (PDOException $e) {
                $debugMessage = sprintf(
                    "Query failed: %s | SQL: %s | Params: %s",
                    $e->getMessage(),
                    $sql,
                    json_encode($params)
                );
                error_log($debugMessage);
                throw new Exception("Database query failed: " . $e->getMessage());
            }
        }

        public function fetchAll($sql, $params = array()) {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        }

        public function fetchOne($sql, $params = array()) {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
        }

        public function fetchColumn($sql, $params = array()) {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchColumn();
        }

        public function insert($table, $data) {
            $columns = array_keys($data);
            $placeholders = array();
            foreach ($columns as $col) {
                $placeholders[] = ':' . $col;
            }

            $sql = "INSERT INTO $table (" . implode(', ', $columns) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";

            $this->query($sql, $data);
            return $this->connection->lastInsertId();
        }

        public function update($table, $data, $where, $whereParams = array()) {
            $data = array_filter($data, function($value) {
                return $value !== null;
            });

            if (empty($data)) {
                return 0;
            }

            $setParts = array();
            foreach ($data as $col => $value) {
                $setParts[] = "$col = :$col";
            }

            $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE $where";

            $params = array_merge($data, $whereParams);
            return $this->query($sql, $params)->rowCount();
        }

        public function delete($table, $where, $whereParams = array()) {
            $sql = "DELETE FROM $table WHERE $where";
            return $this->query($sql, $whereParams)->rowCount();
        }

        public function beginTransaction() {
            return $this->connection->beginTransaction();
        }

        public function commit() {
            return $this->connection->commit();
        }

        public function rollBack() {
            return $this->connection->rollBack();
        }

        public function lastInsertId() {
            return $this->connection->lastInsertId();
        }
    }
}

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

$publicUploadsBase = '/public/uploads';
$documentsPublicPath = '/public/documents';
$uploadPicturesPublicPath = $publicUploadsBase . '/pictures';
$uploadDocumentsPublicPath = $documentsPublicPath;
$uploadPresentationsPublicPath = $publicUploadsBase . '/presentations';

define('UPLOAD_PUBLIC_BASE', $publicUploadsBase);
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
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : dirname(__DIR__);
$customUploadBaseDir = $documentRoot . '/public/uploads/';
$documentsBaseDir = $documentRoot . '/public/documents/';

define('UPLOAD_DIR', $customUploadBaseDir);
define('UPLOAD_PICTURES_DIR', $customUploadBaseDir . 'pictures/');
define('UPLOAD_DOCUMENTS_DIR', $documentsBaseDir);
define('UPLOAD_PRESENTATIONS_DIR', $customUploadBaseDir . 'presentations/');
define('UPLOAD_MAX_SIZE', 500 * 1024 * 1024); // 500MB

// Increase PHP runtime upload limits where possible
if (function_exists('ini_set')) {
    @ini_set('max_file_uploads', '69');
}

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
