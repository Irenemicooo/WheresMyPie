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

// Get user's posted items
try {
    $stmt = $pdo->prepare("
        SELECT i.*, 
            COUNT(DISTINCT cp.claim_id) as pending_claims,
            ca.status as approved_status
        FROM items i 
        LEFT JOIN claims cp ON i.item_id = cp.item_id AND cp.status = 'pending'
        LEFT JOIN claims ca ON i.item_id = ca.item_id AND ca.status = 'approved'
        WHERE i.user_id = ?
        GROUP BY i.item_id, i.title, i.status, ca.status
        ORDER BY i.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $items = $stmt->fetchAll();

    // Get user's claims
    $stmt = $pdo->prepare("
        SELECT c.*, i.title as item_title 
        FROM claims c 
        JOIN items i ON c.item_id = i.item_id 
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $claims = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle potential errors
    echo "Error: " . $e->getMessage();
    exit;
}

$pageTitle = 'My Dashboard';
include '../includes/header.php';
?>

<div class="container">
    <h2>My Dashboard</h2>

    <div class="dashboard-grid">
        <!-- Posted Items Section -->
        <section class="dashboard-section">
            <h3>My Posted Items</h3>
            <?php if (empty($items)): ?>
                <p>No items posted yet.</p>
            <?php else: ?>
                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                        <div class="item-card">
                            <h4><?= htmlspecialchars($item['title']) ?></h4>
                            <p>Status: <?= $item['approved_status'] === 'approved' ? 'Claimed' : htmlspecialchars($item['status']) ?></p>
                            <?php if ($item['pending_claims'] > 0): ?>
                                <p class="alert alert-info">
                                    <?= $item['pending_claims'] ?> pending claims
                                </p>
                            <?php endif; ?>
                            <div class="item-actions">
                                <a href="../items/view.php?id=<?= $item['item_id'] ?>" 
                                   class="btn btn-sm btn-primary">View</a>
                                <?php if ($item['pending_claims'] > 0): ?>
                                    <a href="../claims/review.php?item_id=<?= $item['item_id'] ?>" 
                                       class="btn btn-sm btn-warning">Review Claims</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- My Claims Section -->
        <section class="dashboard-section">
            <h3>My Claims</h3>
            <?php if (empty($claims)): ?>
                <p>No claims submitted yet.</p>
            <?php else: ?>
                <div class="claims-list">
                    <?php foreach ($claims as $claim): ?>
                        <div class="claim-card">
                            <h4><?= htmlspecialchars($claim['item_title']) ?></h4>
                            <p>Status: <span class="status-<?= $claim['status'] ?>">
                                <?= $claim['status'] === 'approved' ? 'Claimed' : ucfirst($claim['status']) ?>
                            </span></p>
                            <p>Submitted: <?= date('Y-m-d', strtotime($claim['created_at'])) ?></p>
                            <a href="../claims/view.php?id=<?= $claim['claim_id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="dashboard-actions">
        <a href="../items/create.php" class="btn btn-primary">Report Found Item</a>
        <a href="../items/search.php" class="btn btn-secondary">Search Lost Items</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
