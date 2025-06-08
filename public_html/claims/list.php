<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

$auth = new Auth($pdo);
$auth->requireLogin();

// Get user's claims
$stmt = $pdo->prepare("
    SELECT c.*, i.title as item_title, i.photo_path as item_photo 
    FROM claims c 
    JOIN items i ON c.item_id = i.item_id 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$claims = $stmt->fetchAll();

$pageTitle = 'My Claims';
include '../includes/header.php';
?>

<div class="container">
    <h2>My Claims</h2>

    <?php if (empty($claims)): ?>
        <p>You haven't submitted any claims yet.</p>
    <?php else: ?>
        <div class="claims-list">
            <?php foreach ($claims as $claim): ?>
                <div class="claim-card">
                    <h3><?= htmlspecialchars($claim['item_title']) ?></h3>
                    <p>Status: <span class="status-<?= $claim['status'] ?>">
                        <?= ucfirst($claim['status']) ?>
                    </span></p>
                    <p>Submitted: <?= date('Y-m-d', strtotime($claim['created_at'])) ?></p>
                    <a href="view.php?id=<?= $claim['claim_id'] ?>" class="btn btn-primary">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
