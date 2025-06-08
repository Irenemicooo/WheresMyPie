<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

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

        // Update email if changed
        if (!empty($_POST['email']) && $_POST['email'] !== $user['email']) {
            if (!validateEmail($_POST['email'])) {
                throw new Exception('Invalid email format');
            }
            $updates[] = "email = ?";
            $params[] = $_POST['email'];
        }

        // Update phone if changed
        if (isset($_POST['phone']) && $_POST['phone'] !== $user['phone']) {
            $updates[] = "phone = ?";
            $params[] = $_POST['phone'] ?: null;
        }

        // Handle password change
        if (!empty($_POST['new_password'])) {
            if (!validatePassword($_POST['new_password'])) {
                throw new Exception('Password must be at least 8 characters and contain uppercase, lowercase, and numbers');
            }
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                throw new Exception('Passwords do not match');
            }
            $updates[] = "password = ?";
            $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }

        // Handle profile photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/profiles/';
            $photo_path = 'uploads/profiles/' . uploadFile($_FILES['profile_photo'], $uploadDir);
            $updates[] = "profile_photo = ?";
            $params[] = $photo_path;
        }

        if (!empty($updates)) {
            $params[] = $_SESSION['user_id'];
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success = true;
            setFlashMessage('success', 'Profile updated successfully');
            header('Location: profile.php');
            exit;
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
