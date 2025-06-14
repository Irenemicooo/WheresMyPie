<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid item ID.";
    exit;
}

$item_id = (int) $_GET['id'];

try {
    // 檢查是否有任何認領請求
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as claim_count 
        FROM claims 
        WHERE item_id = ?
    ");
    $stmt->execute([$item_id]);
    $hasClaims = (bool)$stmt->fetch()['claim_count'];

    $stmt = $pdo->prepare("
        SELECT i.*, 
            u.username,
            COALESCE((
                SELECT COUNT(*) 
                FROM claims c 
                WHERE c.item_id = i.item_id 
                AND c.status = 'approved'
            ), 0) as is_claimed,
            COALESCE((
                SELECT COUNT(*) 
                FROM claims c 
                WHERE c.item_id = i.item_id 
                AND c.user_id = ? 
                AND c.status IN ('pending', 'approved')
            ), 0) as user_claimed,
            c.claim_id,
            c.status as claim_status,
            c.description as claim_description,
            c.evidence_img,
            uc.username as claimer_name,
            uc.email as claimer_email,
            uc.phone as claimer_phone
        FROM items i 
        JOIN users u ON i.user_id = u.user_id 
        LEFT JOIN claims c ON i.item_id = c.item_id AND c.status = 'approved'
        LEFT JOIN users uc ON c.user_id = uc.user_id
        WHERE i.item_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'] ?? 0, $item_id]);
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
    <h2>Item Details</h2>
    
    <div class="item-details-grid">
        <!-- Item Information Section -->
        <section class="item-info-section card">
            <h3 class="section-title">Found Item Information</h3>
            <div class="item-details">
                <h4><?= htmlspecialchars($item['title']) ?></h4>
                
                <?php if (!empty($item['photo_path'])): ?>
                    <div class="item-image">
                        <img src="/<?= htmlspecialchars($item['photo_path']) ?>" 
                             alt="Item photo" style="max-width: 300px; height: auto;">
                    </div>
                <?php endif; ?>

                <div class="item-info">
                    <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                    <p><strong>Found at:</strong> <?= htmlspecialchars($item['location']) ?></p>
                    <p><strong>Date Found:</strong> <?= htmlspecialchars($item['date_found']) ?></p>
                    <p><strong>Posted by:</strong> <?= htmlspecialchars($item['username']) ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?= $item['is_claimed'] ? 'claimed' : 'available' ?>">
                            <?= $item['is_claimed'] ? 'Claimed' : 'Available' ?>
                        </span>
                    </p>
                    <div class="item-description">
                        <p><strong>Description:</strong></p>
                        <div class="description-text bg-light">
                            <?= nl2br(htmlspecialchars($item['description'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Claim Information Section -->
        <section class="claim-info-section card">
            <?php if ($auth->isLoggedIn()): ?>
                <?php if ($_SESSION['user_id'] === $item['user_id']): ?>
                    <?php if (($item['claim_status'] ?? '') === 'approved'): ?>
                        <h3 class="section-title">Claim Information</h3>
                        <div class="claim-details">
                            <p><strong>Claimed by:</strong> <?= htmlspecialchars($item['claimer_name'] ?? 'Unknown') ?></p>
                            <div class="claim-description">
                                <p><strong>Claim Description:</strong></p>
                                <div class="description-text bg-light">
                                    <?= nl2br(htmlspecialchars($item['claim_description'] ?? 'No description provided')) ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($item['evidence_img'])): ?>
                                <div class="evidence-image">
                                    <h4>Evidence Photo:</h4>
                                    <img src="/<?= htmlspecialchars($item['evidence_img']) ?>" 
                                         alt="Evidence" style="max-width: 300px; height: auto;">
                                </div>
                            <?php endif; ?>

                            <div class="contact-info card">
                                <h4>Claimer's Contact Information</h4>
                                <p><strong>Email:</strong> <?= htmlspecialchars($item['claimer_email']) ?></p>
                                <?php if (!empty($item['claimer_phone'])): ?>
                                    <p><strong>Phone:</strong> <?= htmlspecialchars($item['claimer_phone']) ?></p>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($item['claim_id'])): ?>
                                <div class="action-buttons">
                                    <a href="../chat/room.php?claim_id=<?= htmlspecialchars($item['claim_id']) ?>" 
                                       class="btn btn-primary">Chat with Claimer</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="claim-actions">
                            <?php if (!$hasClaims): ?>
                                <button onclick="confirmDelete(<?= $item_id ?>)" class="btn btn-danger">Delete Item</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <h3 class="section-title">Claim Actions</h3>
                    <?php if (!$item['is_claimed'] && !$item['user_claimed']): ?>
                        <a href="../claims/create.php?item_id=<?= $item['item_id'] ?>" 
                           class="btn btn-success">I want to claim this</a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
    
    <div class="bottom-actions">
        <a href="/user/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<script>
function confirmDelete(itemId) {
    if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        window.location.href = `delete.php?id=${itemId}`;
    }
}
</script>

<style>
.item-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #333;
}

.item-details {
    display: flex;
    flex-direction: column;
}

.item-image {
    margin-bottom: 1rem;
}

.item-info {
    flex-grow: 1;
}

.item-description {
    margin-top: 1rem;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: bold;
}

.status-badge.claimed {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.available {
    background-color: #cce5ff;
    color: #004085;
}

.claim-info-section {
    margin-top: 2rem;
}

.claim-details {
    margin-bottom: 1rem;
}

.evidence-image {
    margin-top: 1rem;
}

.contact-info {
    margin-top: 1rem;
}

.action-buttons {
    margin-top: 1rem;
}

.bottom-actions {
    margin-top: 2rem;
    text-align: center;
}

.description-text {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    margin-top: 0.5rem;
    line-height: 1.5;
}
</style>

<?php include '../includes/footer.php'; ?>