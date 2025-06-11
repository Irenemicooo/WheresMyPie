<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid item ID.";
    exit;
}

$item_id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT i.*, u.username,
            c.description as claim_description,
            c.evidence_img,
            c.status as claim_status,
            uc.username as claimer_name
        FROM items i 
        JOIN users u ON i.user_id = u.user_id 
        LEFT JOIN claims c ON i.item_id = c.item_id AND c.status = 'approved'
        LEFT JOIN users uc ON c.user_id = uc.user_id
        WHERE i.item_id = ?
    ");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if (!$item) {
        echo "Item not found.";
        exit;
    }
} catch (Exception $e) {
    echo DEBUG ? $e->getMessage() : "An error occurred.";
    exit;
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2><?= htmlspecialchars($item['title']) ?></h2>

    <?php if (!empty($item['photo_path'])): ?>
        <div class="item-image">
            <img src="/<?= htmlspecialchars($item['photo_path']) ?>" 
                 alt="Item photo" 
                 style="max-width: 100%; height: auto; max-height: 400px; object-fit: contain;">
        </div>
    <?php endif; ?>

    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($item['description'])) ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
    <p><strong>Found at:</strong> <?= htmlspecialchars($item['location']) ?></p>
    <p><strong>Date Found:</strong> <?= htmlspecialchars($item['date_found']) ?></p>
    <p><strong>Posted by:</strong> <?= htmlspecialchars($item['username']) ?></p>

    <?php if ($auth->isLoggedIn() && 
              $_SESSION['user_id'] !== $item['user_id'] && 
              !$item['is_claimed'] && 
              !$item['user_claimed']): ?>
        <a href="../claims/create.php?item_id=<?= $item['item_id'] ?>" class="btn btn-success">I want to claim this</a>
    <?php endif; ?>

    <!-- 添加認領資訊區塊 -->
    <?php if ($item['claim_status'] === 'approved' && $_SESSION['user_id'] === $item['user_id']): ?>
        <div class="claim-info">
            <h3>Claim Information</h3>
            <p><strong>Claimed by:</strong> <?= htmlspecialchars($item['claimer_name']) ?></p>
            <p><strong>Claim Description:</strong> <?= nl2br(htmlspecialchars($item['claim_description'])) ?></p>
            <?php if ($item['evidence_img']): ?>
                <div class="evidence-image">
                    <h4>Evidence Photo:</h4>
                    <img src="/<?= htmlspecialchars($item['evidence_img']) ?>" 
                         alt="Evidence" style="max-width: 300px; height: auto;">
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($auth->isLoggedIn() && $_SESSION['user_id'] === $item['user_id']): ?>
        <?php
        // Check if item has any claims
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE item_id = ?");
        $stmt->execute([$item_id]);
        $hasClaims = $stmt->fetchColumn() > 0;
        ?>
        
        <?php if (!$hasClaims): ?>
            <button onclick="confirmDelete(<?= $item_id ?>)" class="btn btn-danger">Delete Item</button>
        <?php endif; ?>
    <?php endif; ?>
    
    <p><a href="index.php" class="btn btn-secondary">Back to List</a></p>
</div>

<script>
function confirmDelete(itemId) {
    if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        window.location.href = `delete.php?id=${itemId}`;
    }
}
</script>

<?php include '../includes/footer.php'; ?>