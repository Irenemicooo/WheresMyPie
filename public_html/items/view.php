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
        SELECT i.*, u.username 
        FROM items i 
        JOIN users u ON i.user_id = u.user_id 
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
                 alt="Item photo" style="max-width: 400px;">
        </div>
    <?php endif; ?>

    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($item['description'])) ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
    <p><strong>Found at:</strong> <?= htmlspecialchars($item['location']) ?></p>
    <p><strong>Date Found:</strong> <?= htmlspecialchars($item['date_found']) ?></p>
    <p><strong>Posted by:</strong> <?= htmlspecialchars($item['username']) ?></p>

    <?php if ($auth->isLoggedIn() && $_SESSION['user_id'] !== $item['user_id']): ?>
        <a href="../claims/create.php?item_id=<?= $item['item_id'] ?>" class="btn btn-success">I want to claim this</a>
    <?php endif; ?>

    <p><a href="index.php" class="btn btn-secondary">Back to List</a></p>
</div>

<?php include '../includes/footer.php'; ?>