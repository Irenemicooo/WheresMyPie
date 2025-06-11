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

$errors = [];
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

// Get item details
$stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND status = 'available'");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    setFlashMessage('error', 'Item not found or not available');
    redirect('/items/index.php');
}

// Check if user already has a pending claim for this item
$stmt = $pdo->prepare("SELECT * FROM claims WHERE item_id = ? AND user_id = ? AND status = 'pending'");
$stmt->execute([$item_id, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    setFlashMessage('error', 'You already have a pending claim for this item');
    redirect('/items/view.php?id=' . $item_id);
}

$pageTitle = 'Submit Claim';
include '../includes/header.php';
?>

<div class="container">
    <h2>Submit Claim for: <?= htmlspecialchars($item['title']) ?></h2>

    <form method="post" action="api/create.php" enctype="multipart/form-data" class="claim-form">
        <input type="hidden" name="item_id" value="<?= $item_id ?>">
        
        <div class="form-group">
            <label for="description">Description of Your Item</label>
            <textarea name="description" id="description" required 
                      placeholder="Please describe your item in detail..."></textarea>
        </div>

        <div class="form-group">
            <label for="evidence_img">Evidence Photo (Optional)</label>
            <input type="file" name="evidence_img" id="evidence_img" accept="image/*">
            <small>Upload a photo that proves your ownership</small>
        </div>

        <button type="submit" class="btn btn-primary">Submit Claim</button>
        <a href="/items/view.php?id=<?= $item_id ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
