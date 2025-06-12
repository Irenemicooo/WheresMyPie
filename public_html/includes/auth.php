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

        // 檢查是否被暫時封鎖
        if (isset($_SESSION[$blockKey]) && time() < $_SESSION[$blockKey]) {
            $wait = ceil(($_SESSION[$blockKey] - time()) / 60);
            return ['success' => false, 'message' => "帳號已被暫時鎖定，請等待 $wait 分鐘後再試。"];
        }

        try {
            $stmt = $this->db->query(
                "SELECT * FROM users WHERE username = ? OR email = ?",
                [$username, $username]
            );

            $user = $stmt->fetch();

            if (!$user) {
                $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
                return ['success' => false, 'message' => '找不到此使用者，請確認帳號或email是否正確。'];
            }

            if (!password_verify($password, $user['password'])) {
                $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
                $remainingAttempts = 5 - $_SESSION[$key];

                if ($_SESSION[$key] >= 5) {
                    $_SESSION[$blockKey] = time() + (10 * 60);
                    return ['success' => false, 'message' => '登入嘗試次數過多，帳號已被鎖定10分鐘。'];
                }

                return ['success' => false, 'message' => "密碼錯誤，還剩 {$remainingAttempts} 次嘗試機會。"];
            }

            // 登入成功，重置嘗試次數
            unset($_SESSION[$key]);
            unset($_SESSION[$blockKey]);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_login'] = time();

            return ['success' => true, 'message' => '登入成功'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => DEBUG ? $e->getMessage() : '系統錯誤，請稍後再試。'];
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

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            if (!validatePassword($newPassword)) {
                return ['success' => false, 'message' => 'Invalid new password format'];
            }

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

    // 新增CSRF令牌生成
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // 新增CSRF令牌驗證
    public function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // 新增登入嘗試限制
    private function checkLoginAttempts($username) {
        $key = 'login_attempts_' . $username;
        $attempts = $_SESSION[$key] ?? 0;
    
        if ($attempts >= 5) {
            return false; // 已達最大嘗試次數
        }
        return true;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        }
    }

    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}

// Create global auth instance
$auth = new Auth();
?>