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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid claim ID');
    redirect('/user/dashboard.php');
}

try {
    $stmt = $pdo->prepare("
        SELECT c.*, i.title as item_title, i.photo_path, i.location, i.date_found,
               u.username as finder_name
        FROM claims c
        JOIN items i ON c.item_id = i.item_id
        JOIN users u ON i.user_id = u.user_id
        WHERE c.claim_id = ? AND c.user_id = ?
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $claim = $stmt->fetch();

    if (!$claim) {
        setFlashMessage('error', 'Claim not found or access denied');
        redirect('/user/dashboard.php');
    }
} catch (Exception $e) {
    setFlashMessage('error', 'Error loading claim details');
    redirect('/user/dashboard.php');
}

$pageTitle = 'Claim Details';
include '../includes/header.php';
?>

<div class="container">
    <h2>Claim Details</h2>
    
    <div class="claim-details">
        <h3>Item: <?= htmlspecialchars($claim['item_title']) ?></h3>
        
        <?php if (!empty($claim['photo_path'])): ?>
            <div class="item-image">
                <img src="/<?= htmlspecialchars($claim['photo_path']) ?>" 
                     alt="Item photo" style="max-width: 300px; height: auto;">
            </div>
        <?php endif; ?>

        <div class="claim-info">
            <p><strong>Status:</strong> <?= ucfirst(htmlspecialchars($claim['status'])) ?></p>
            <p><strong>Found by:</strong> <?= htmlspecialchars($claim['finder_name']) ?></p>
            <p><strong>Location Found:</strong> <?= htmlspecialchars($claim['location']) ?></p>
            <p><strong>Date Found:</strong> <?= htmlspecialchars($claim['date_found']) ?></p>
            <p><strong>Your Description:</strong></p>
            <p><?= nl2br(htmlspecialchars($claim['description'])) ?></p>
            
            <?php if (!empty($claim['evidence_img'])): ?>
                <div class="evidence-image">
                    <h4>Your Evidence Photo:</h4>
                    <img src="/<?= htmlspecialchars($claim['evidence_img']) ?>" 
                         alt="Evidence photo" style="max-width: 300px; height: auto;">
                </div>
            <?php endif; ?>
        </div>

        <?php if ($claim['status'] === 'approved'): ?>
            <a href="../chat/room.php?claim_id=<?= $claim['claim_id'] ?>" 
               class="btn btn-primary">Chat with Finder</a>
        <?php endif; ?>
        
        <a href="/user/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
