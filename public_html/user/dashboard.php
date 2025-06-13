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
            ca.status as approved_status,
            ca.claim_id as approved_claim_id
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
        <section class="dashboard-section card">
            <div class="section-header">
                <h3>My Posted Items</h3>
                <a href="../items/create.php" class="btn btn-sm btn-primary">Report New Item</a>
            </div>

            <?php if (empty($items)): ?>
                <p class="empty-state">No items posted yet.</p>
            <?php else: ?>
                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                        <div class="item-card">
                            <div class="card-header">
                                <h4><?= htmlspecialchars($item['title']) ?></h4>
                                <span class="status-badge <?= $item['approved_status'] === 'approved' ? 'claimed' : $item['status'] ?>">
                                    <?= $item['approved_status'] === 'approved' ? 'Claimed' : htmlspecialchars($item['status']) ?>
                                </span>
                            </div>
                            
                            <?php if ($item['pending_claims'] > 0): ?>
                                <div class="alert alert-info">
                                    <i class="alert-icon">📋</i>
                                    <span><?= $item['pending_claims'] ?> pending claims</span>
                                </div>
                            <?php endif; ?>

                            <div class="card-actions">
                                <a href="../items/view.php?id=<?= $item['item_id'] ?>" 
                                   class="btn btn-sm btn-outline">View Details</a>
                                <?php if ($item['pending_claims'] > 0): ?>
                                    <a href="../claims/review.php?item_id=<?= $item['item_id'] ?>" 
                                       class="btn btn-sm btn-warning">Review Claims</a>
                                <?php endif; ?>
                                <?php if ($item['approved_claim_id']): ?>
                                    <a href="../chat/room.php?claim_id=<?= $item['approved_claim_id'] ?>" 
                                       class="btn btn-sm btn-primary">Chat</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Claims Section -->
        <section class="dashboard-section card">
            <div class="section-header">
                <h3>My Claims</h3>
                <a href="../items/search.php" class="btn btn-sm btn-primary">Search Items</a>
            </div>

            <?php if (empty($claims)): ?>
                <p class="empty-state">No claims submitted yet.</p>
            <?php else: ?>
                <div class="claims-list">
                    <?php foreach ($claims as $claim): ?>
                        <div class="claim-card">
                            <div class="card-header">
                                <h4><?= htmlspecialchars($claim['item_title']) ?></h4>
                                <span class="status-badge <?= $claim['status'] ?>">
                                    <?= $claim['status'] === 'approved' ? 'Claimed' : ucfirst($claim['status']) ?>
                                </span>
                            </div>
                            <p class="submission-date">
                                <i class="date-icon">📅</i>
                                Submitted: <?= date('Y-m-d', strtotime($claim['created_at'])) ?>
                            </p>
                            <div class="card-actions">
                                <a href="../claims/view.php?id=<?= $claim['claim_id'] ?>" 
                                   class="btn btn-sm btn-outline">View Details</a>
                                <?php if ($claim['status'] === 'approved'): ?>
                                    <a href="../chat/room.php?claim_id=<?= $claim['claim_id'] ?>" 
                                       class="btn btn-sm btn-primary">Chat</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1.5rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #eee;
}

.section-header h3 {
    margin: 0;
    color: #2c3e50;
}

.item-card, .claim-card {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.card-header h4 {
    margin: 0;
    color: #2c3e50;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.875rem;
    font-weight: bold;
}

.status-badge.available { background: #17a2b8; color: white; }
.status-badge.claimed { background: #28a745; color: white; }
.status-badge.pending { background: #ffd700; color: black; }
.status-badge.rejected { background: #dc3545; color: white; }

.alert {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    margin: 0.5rem 0;
    background: #fff3cd;
    border-radius: 4px;
}

.card-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.empty-state {
    text-align: center;
    color: #6c757d;
    padding: 2rem;
}

.submission-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    margin: 0.5rem 0;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
