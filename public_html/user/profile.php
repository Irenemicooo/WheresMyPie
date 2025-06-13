<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth($pdo);
$auth->requireLogin();

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$errors = [];
$success = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updates = [];
        $params = [];

        // Handle password change
        if (!empty($_POST['new_password'])) {
            if (strlen($_POST['new_password']) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                throw new Exception('Passwords do not match');
            }
            $updates[] = "password = ?";
            $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }

        // Update email if changed and valid
        if (!empty($_POST['email']) && $_POST['email'] !== $user['email']) {
            if (!validateEmail($_POST['email'])) {
                throw new Exception('Invalid email format');
            }
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$_POST['email'], $_SESSION['user_id']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Email already in use');
            }
            $updates[] = "email = ?";
            $params[] = $_POST['email'];
        }

        // Update phone if changed
        if (isset($_POST['phone']) && $_POST['phone'] !== $user['phone']) {
            // Add basic phone validation if needed
            $updates[] = "phone = ?";
            $params[] = $_POST['phone'] ?: null;
        }

        // Update contact visibility
        if (isset($_POST['contact_visibility'])) {
            $updates[] = "contact_visibility = ?";
            $params[] = $_POST['contact_visibility'] ?: 'none'; // Set default value if empty
        }

        if (!empty($updates)) {
            $params[] = $_SESSION['user_id'];
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            setFlashMessage('success', 'Profile updated successfully');
            redirect('/user/profile.php');
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$pageTitle = 'My Profile';
$pageScripts = ['profile.js'];
include '../includes/header.php';
?>

<div class="container">
    <h2>My Profile</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="profile-form">
        <div class="profile-photo">
            <?php if (!empty($user['profile_photo'])): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['profile_photo']) ?>" 
                     alt="Profile Photo" class="current-photo">
            <?php endif; ?>
            <div class="form-group">
                <label for="profile_photo">Profile Photo</label>
                <input type="file" name="profile_photo" id="profile_photo" accept="image/*">
            </div>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" value="<?= htmlspecialchars($user['username']) ?>" 
                   readonly class="form-control-plaintext">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" 
                   value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number (Optional)</label>
            <input type="tel" name="phone" id="phone" 
                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="contact_visibility">Contact Visibility</label>
            <select name="contact_visibility" id="contact_visibility">
                <option value="public" <?= $user['contact_visibility'] === 'public' ? 'selected' : '' ?>>Public</option>
                <option value="private" <?= $user['contact_visibility'] === 'private' ? 'selected' : '' ?>>Private</option>
            </select>
        </div>

        <fieldset class="password-change">
            <legend>Change Password</legend>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password">
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
