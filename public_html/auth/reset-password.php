<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

$errors = [];
$message = '';
$identifier = '';

// Generate CSRF Token
$csrf_token = $auth->generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $submitted_token = $_POST['csrf_token'] ?? '';

    // check CSRF Token
    if (!$auth->verifyCSRFToken($submitted_token)) {
        $errors[] = 'Invalid request. Please try again.';
    }

    // input validation
    if (empty($identifier)) {
        $errors[] = 'Please enter username or email.';
    }
    if (empty($new_password)) {
        $errors[] = 'Please enter new password.';
    }
    if (empty($confirm_password)) {
        $errors[] = 'Please confirm your password.';
    }
    if ($new_password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    if (!validatePassword($new_password)) {
        $errors[] = 'Password must be at least 8 characters and contain uppercase, lowercase, and numbers.';
    }

    // if no errors, proceed with password reset
    if (empty($errors)) {
        try {
            // find user by username or email
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            if (!$user) {
                $errors[] = 'User not found.';
            } else {
                // update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $result = $stmt->execute([$hashed_password, $user['user_id']]);

                if ($result) {
                    $_SESSION['success'] = 'Password has been reset successfully. You can now login.';
                    header('Location: login.php');
                    exit;
                } else {
                    $errors[] = 'Failed to reset password. Please try again.';
                }
            }
        } catch (Exception $e) {
            $errors[] = DEBUG ? $e->getMessage() : 'An error occurred. Please try again.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Reset Password</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="reset-password.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <label for="identifier">Username or Email</label>
        <input type="text" name="identifier" id="identifier" required value="<?= htmlspecialchars($identifier) ?>">

        <label for="new_password">New Password</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit">Reset Password</button>
    </form>

    <div class="auth-links">
        <p><a href="login.php">Back to Login</a></p>
        <p><a href="register.php">Register here</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>