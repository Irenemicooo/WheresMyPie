<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

$auth = new Auth($pdo);
$auth->requireLogin();

// Get user settings
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $settings = [
            'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
            'public_profile' => isset($_POST['public_profile']) ? 1 : 0,
            'contact_visibility' => $_POST['contact_visibility']
        ];

        $stmt = $pdo->prepare("
            UPDATE users 
            SET email_notifications = ?, 
                public_profile = ?,
                contact_visibility = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $settings['email_notifications'],
            $settings['public_profile'],
            $settings['contact_visibility'],
            $_SESSION['user_id']
        ]);

        setFlashMessage('success', 'Settings updated successfully');
        redirect('/user/settings.php');
    } catch (Exception $e) {
        setFlashMessage('error', 'Failed to update settings');
    }
}

$pageTitle = 'Account Settings';
include '../includes/header.php';
?>

<div class="container">
    <h2>Account Settings</h2>

    <form method="post" class="settings-form">
        <fieldset>
            <legend>Notifications</legend>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="email_notifications" 
                           <?= $user['email_notifications'] ? 'checked' : '' ?>>
                    Receive email notifications
                </label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Privacy Settings</legend>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="public_profile" 
                           <?= $user['public_profile'] ? 'checked' : '' ?>>
                    Make profile public
                </label>
            </div>
            <div class="form-group">
                <label for="contact_visibility">Contact Information Visibility</label>
                <select name="contact_visibility" id="contact_visibility">
                    <option value="none" <?= $user['contact_visibility'] === 'none' ? 'selected' : '' ?>>
                        None
                    </option>
                    <option value="email" <?= $user['contact_visibility'] === 'email' ? 'selected' : '' ?>>
                        Email Only
                    </option>
                    <option value="all" <?= $user['contact_visibility'] === 'all' ? 'selected' : '' ?>>
                        All Contact Info
                    </option>
                </select>
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
