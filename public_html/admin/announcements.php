<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

$auth = new Auth($pdo);
$auth->requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO announcements (title, content, start_date, end_date, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['title'],
            $_POST['content'],
            $_POST['start_date'],
            $_POST['end_date'] ?: null,
            $_SESSION['user_id']
        ]);

        setFlashMessage('success', 'Announcement created successfully');
        redirect('/admin/announcements.php');
    } catch (Exception $e) {
        setFlashMessage('error', 'Failed to create announcement');
    }
}

// Get active announcements
$stmt = $pdo->prepare("
    SELECT * FROM announcements 
    WHERE end_date IS NULL OR end_date > NOW()
    ORDER BY created_at DESC
");
$stmt->execute();
$announcements = $stmt->fetchAll();

$pageTitle = 'Manage Announcements';
include '../includes/header.php';
?>

<div class="container">
    <h2>System Announcements</h2>
    
    <form method="post" class="announcement-form">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" required>
        </div>
        
        <div class="form-group">
            <label for="content">Content</label>
            <textarea name="content" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="datetime-local" name="start_date" required>
        </div>
        
        <div class="form-group">
            <label for="end_date">End Date (Optional)</label>
            <input type="datetime-local" name="end_date">
        </div>
        
        <button type="submit" class="btn btn-primary">Create Announcement</button>
    </form>

    <div class="announcements-list">
        <?php foreach ($announcements as $announcement): ?>
            <div class="announcement-item">
                <h3><?= htmlspecialchars($announcement['title']) ?></h3>
                <p><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
                <div class="announcement-meta">
                    Start: <?= $announcement['start_date'] ?>
                    <?php if ($announcement['end_date']): ?>
                        <br>End: <?= $announcement['end_date'] ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
