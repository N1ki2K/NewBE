<?php
// ====================================================
// Authentication Middleware
// ====================================================

require_once __DIR__ . '/jwt.php';

class AuthMiddleware {

    private static function getAuthorizationHeader() {
        $authHeader = '';
        // Try native getallheaders first
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            } elseif (isset($headers['authorization'])) {
                $authHeader = $headers['authorization'];
            }
            if (!empty($authHeader)) {
                return $authHeader;
            }
        }

        // Fallbacks for environments where Authorization is not included in getallheaders
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return $authHeader;
    }

    public static function authenticate() {
        $authHeader = self::getAuthorizationHeader();

        if (empty($authHeader)) {
            errorResponse('Authorization header missing', 401);
        }

        // Extract token from "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            errorResponse('Invalid authorization format', 401);
        }

        try {
            $payload = JWT::decode($token);

            // Verify token exists in database and is not expired
            $db = Database::getInstance();
            $tokenData = $db->fetchOne(
                "SELECT * FROM auth_tokens WHERE token = ? AND expires_at > NOW()",
                [$token]
            );

            if (!$tokenData) {
                errorResponse('Invalid or expired token', 401);
            }

            // Get user data
            $user = $db->fetchOne(
                "SELECT id, username, email, role, is_active FROM users WHERE id = ?",
                [$payload['user_id']]
            );

            if (!$user || !$user['is_active']) {
                errorResponse('User not found or inactive', 401);
            }

            return $user;
        } catch (Exception $e) {
            error_log("Auth error: " . $e->getMessage());
            errorResponse('Authentication failed: ' . $e->getMessage(), 401);
        }
    }

    public static function requireAdmin() {
        $user = self::authenticate();

        if ($user['role'] !== 'admin') {
            errorResponse('Admin access required', 403);
        }

        return $user;
    }

    public static function requireEditorOrAdmin() {
        $user = self::authenticate();

        if (!in_array($user['role'], ['admin', 'editor'])) {
            errorResponse('Editor or admin access required', 403);
        }

        return $user;
    }

    public static function check() {
        return self::requireEditorOrAdmin();
    }
}
