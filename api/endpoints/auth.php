<?php
// ====================================================
// Authentication Endpoints
// ====================================================

class AuthEndpoints {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // POST /api/auth/login
    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);

        $username = isset($input['username']) ? $input['username'] : '';
        $password = isset($input['password']) ? $input['password'] : '';

        if (empty($username) || empty($password)) {
            errorResponse('Username and password are required', 400);
        }

        // Get user from database
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE username = ? AND is_active = 1",
            [$username]
        );

        if (!$user) {
            errorResponse('Invalid credentials', 401);
        }

        $storedPassword = isset($user['password']) ? $user['password'] : '';
        $authenticated = false;

        if ($storedPassword !== '') {
            if (password_verify($password, $storedPassword)) {
                $authenticated = true;
            } elseif ($storedPassword === $password) {
                // Allow plain-text match (for temporary testing environments)
                $authenticated = true;
            }
        }

        if (!$authenticated) {
            errorResponse('Invalid credentials', 401);
        }

        // Generate JWT token
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        $token = JWT::encode($payload);

        // Store token in database
        $expiresAt = date('Y-m-d H:i:s', time() + JWT_EXPIRATION);
        $this->db->insert('auth_tokens', [
            'user_id' => $user['id'],
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        // Update last login
        $this->db->update('users',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = ?',
            [$user['id']]
        );

        // Remove password from response
        unset($user['password']);

        jsonResponse([
            'token' => $token,
            'user' => $user
        ]);
    }

    // POST /api/auth/logout
    public function logout() {
        $user = AuthMiddleware::authenticate();

        // Get token from header
        $headers = getallheaders();
        $authHeader = '';
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        }
        preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches);
        $token = isset($matches[1]) ? $matches[1] : '';

        if ($token) {
            // Delete token from database
            $this->db->delete('auth_tokens', 'token = ?', [$token]);
        }

        jsonResponse(['message' => 'Logged out successfully']);
    }

    // GET /api/auth/me
    public function getCurrentUser() {
        $user = AuthMiddleware::authenticate();
        jsonResponse(['user' => $user]);
    }

    // POST /api/auth/change-password
    public function changePassword() {
        $user = AuthMiddleware::authenticate();

        $input = json_decode(file_get_contents('php://input'), true);

        $currentPassword = isset($input['currentPassword']) ? $input['currentPassword'] : '';
        $newPassword = isset($input['newPassword']) ? $input['newPassword'] : '';

        if (empty($currentPassword) || empty($newPassword)) {
            errorResponse('Current password and new password are required', 400);
        }

        // Verify current password
        $userData = $this->db->fetchOne(
            "SELECT password FROM users WHERE id = ?",
            [$user['id']]
        );

        if (!password_verify($currentPassword, $userData['password'])) {
            errorResponse('Current password is incorrect', 401);
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->update('users',
            ['password' => $hashedPassword],
            'id = ?',
            [$user['id']]
        );

        jsonResponse(['message' => 'Password changed successfully']);
    }
}
