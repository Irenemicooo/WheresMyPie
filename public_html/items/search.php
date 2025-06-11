<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

$keyword = $_GET['keyword'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT i.* FROM items i WHERE 1=1 AND i.status = 'available' 
        AND NOT EXISTS (
            SELECT 1 FROM claims c 
            WHERE c.item_id = i.item_id 
            AND c.status = 'approved'
        )";
$params = [];

if (!empty($keyword)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $keywordParam = '%' . $keyword . '%';
    $params[] = $keywordParam;
    $params[] = $keywordParam;
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if (!empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Search Lost Items</h2>

    <form action="search.php" method="get" class="search-form">
        <input type="text" name="keyword" placeholder="Keyword..." value="<?= htmlspecialchars($keyword) ?>">
        
        <select name="category">
            <option value="">-- Category --</option>
            <option value="Electronics" <?= $category == 'Electronics' ? 'selected' : '' ?>>Electronics</option>
            <option value="Clothing" <?= $category == 'Clothing' ? 'selected' : '' ?>>Clothing</option>
            <option value="Accessories" <?= $category == 'Accessories' ? 'selected' : '' ?>>Accessories</option>
            <option value="Documents" <?= $category == 'Documents' ? 'selected' : '' ?>>Documents</option>
            <option value="Others" <?= $category == 'Others' ? 'selected' : '' ?>>Others</option>
        </select>

        <select name="status">
            <option value="">-- Status --</option>
            <option value="available" <?= $status == 'available' ? 'selected' : '' ?>>Available</option>
            <option value="claimed" <?= $status == 'claimed' ? 'selected' : '' ?>>Claimed</option>
            <option value="returned" <?= $status == 'returned' ? 'selected' : '' ?>>Returned</option>
        </select>

        <button type="submit">Search</button>
    </form>

    <?php if (count($items) > 0): ?>
        <div class="item-list">
            <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                    <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($item['status'])) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($item['location']) ?></p>
                    <p><strong>Date Found:</strong> <?= htmlspecialchars($item['date_found']) ?></p>
                    <?php if (!empty($item['photo_path'])): ?>
                        <img src="/<?= htmlspecialchars($item['photo_path']) ?>" 
                             alt="<?= htmlspecialchars($item['title']) ?>" 
                             class="item-photo"
                             style="max-width: 150px; max-height: 150px; object-fit: cover;">
                    <?php endif; ?>
                    <p><a href="view.php?id=<?= $item['item_id'] ?>" class="btn btn-sm btn-secondary">View Details</a></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No items found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>