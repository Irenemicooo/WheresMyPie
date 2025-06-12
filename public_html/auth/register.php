<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$username = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // check for empty fields and validate inputs
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // check for existing username or email
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Username or email already registered.';
        }
    }

    // if no errors, proceed with registration
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, phone) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$username, $hashed_password, $email, $phone]);

        if ($result) {
            $_SESSION['success'] = 'Registration successful! You can now log in.';
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Failed to register. Please try again.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Register</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register.php" method="post">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required value="<?= htmlspecialchars($username) ?>">

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required value="<?= htmlspecialchars($email) ?>">

        <label for="phone">Phone (optional)</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($phone) ?>">

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Log in here</a>.</p>
</div>

<?php include '../includes/footer.php'; ?>