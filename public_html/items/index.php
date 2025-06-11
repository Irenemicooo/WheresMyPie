<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM items 
        WHERE status = 'available' 
        AND NOT EXISTS (
            SELECT 1 FROM claims 
            WHERE claims.item_id = items.item_id 
            AND claims.status = 'approved'
        )
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $items = $stmt->fetchAll();
} catch (Exception $e) {
    $error = DEBUG ? $e->getMessage() : 'Failed to load items.';
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Available Lost Items</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <p>No items found.</p>
    <?php else: ?>
        <div class="item-list">
            <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                    <?php if (!empty($item['photo_path'])): ?>
                        <img src="/<?= htmlspecialchars($item['photo_path']) ?>" 
                             alt="<?= htmlspecialchars($item['title']) ?>" width="150">
                    <?php endif; ?>
                    <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($item['location']) ?></p>
                    <p><strong>Date Found:</strong> <?= htmlspecialchars($item['date_found']) ?></p>
                    <a href="view.php?id=<?= $item['item_id'] ?>" class="btn">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($auth->isLoggedIn()): ?>
        <p><a href="create.php" class="btn btn-primary">I Found Something</a></p>
    <?php else: ?>
        <p><a href="../auth/login.php" class="btn">Login to post an item</a></p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>