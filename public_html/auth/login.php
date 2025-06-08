<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if session not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$username_or_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    $auth = new Auth($pdo);
    if ($auth->login($username_or_email, $password)) {
        $redirect = $_SESSION['redirect_after_login'] ?? '../user/dashboard.php';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    } else {
        $errors[] = 'Invalid username/email or password.';
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Login</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <label for="username_or_email">Username or Email</label>
        <input type="text" name="username_or_email" id="username_or_email" required value="<?= htmlspecialchars($username_or_email) ?>">

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account yet? <a href="register.php">Register here</a>.</p>
</div>

<?php include '../includes/footer.php'; ?>