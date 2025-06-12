<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Register a new user
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string $phone
     * @return array
     */
    public function register($username, $email, $password, $phone) {
        // Validate input
        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        if (!validatePassword($password)) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long and contain uppercase, lowercase, and numbers'];
        }

        try {
            // Check if username or email already exists
            $stmt = $this->db->query(
                "SELECT * FROM users WHERE username = ? OR email = ?",
                [$username, $email]
            );

            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->db->query(
                "INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)",
                [$username, $email, $hashedPassword, $phone]
            );

            return ['success' => true, 'message' => 'Registration successful'];
        } catch (Exception $e) {
            if (DEBUG) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    /**
     * Login user
     * @param string $username
     * @param string $password
     * @return array
     */
    public function login($username, $password) {
        $key = 'login_attempts_' . $username;
        $blockKey = 'login_block_time_' . $username;

        // Check if temporarily blocked
        if (isset($_SESSION[$blockKey]) && time() < $_SESSION[$blockKey]) {
            $wait = ceil(($_SESSION[$blockKey] - time()) / 60);
            return ['success' => false, 'message' => "Your account is temporarily locked. Please try again in $wait minutes."];
        }

        try {
            $stmt = $this->db->query(
                "SELECT * FROM users WHERE username = ? OR email = ?",
                [$username, $username]
            );

            $user = $stmt->fetch();

            if (!$user) {
                $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
                return ['success' => false, 'message' => 'User not found. Please check your username or email.'];
            }

            if (!password_verify($password, $user['password'])) {
                $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
                $remainingAttempts = 5 - $_SESSION[$key];

                if ($_SESSION[$key] >= 5) {
                    $_SESSION[$blockKey] = time() + (10 * 60);
                    return ['success' => false, 'message' => 'Too many failed login attempts. Your account has been locked for 10 minutes.'];
                }

                return ['success' => false, 'message' => "Incorrect password. You have {$remainingAttempts} attempt(s) remaining."];
            }

            // Login successful, reset attempts
            unset($_SESSION[$key]);
            unset($_SESSION[$blockKey]);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_login'] = time();

            return ['success' => true, 'message' => 'Login successful'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => DEBUG ? $e->getMessage() : 'A system error occurred. Please try again later.'];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logout successful'];
    }

    /**
     * Check if user is logged in
     * @return boolean
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user data
     * @return array|null
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $stmt = $this->db->query(
                "SELECT user_id, username, email, phone, created_at FROM users WHERE user_id = ?",
                [$_SESSION['user_id']]
            );
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Update user password
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return array
     */
    public function updatePassword($userId, $currentPassword, $newPassword) {
        try {
            $stmt = $this->db->query(
                "SELECT password FROM users WHERE user_id = ?",
                [$userId]
            );
            $user = $stmt->fetch();

            // Check current password
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            if (!validatePassword($newPassword)) {
                return ['success' => false, 'message' => 'Invalid new password format'];
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->query(
                "UPDATE users SET password = ? WHERE user_id = ?",
                [$hashedPassword, $userId]
            );

            return ['success' => true, 'message' => 'Password updated successfully'];
        } catch (Exception $e) {
            if (DEBUG) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
            return ['success' => false, 'message' => 'Password update failed'];
        }
    }

    /**
     * Generate CSRF token
     * @return string
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     * @param string $token
     * @return boolean
     */
    public function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check login attempts
     * @param string $username
     * @return boolean
     */
    private function checkLoginAttempts($username) {
        $key = 'login_attempts_' . $username;
        $attempts = $_SESSION[$key] ?? 0;

        if ($attempts >= 5) {
            return false;
        }
        return true;
    }

    /**
     * Require login to access page
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        }
    }

    /**
     * Get current user ID
     * @return int|null
     */
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}

// Create global auth instance
$auth = new Auth();