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
        SELECT c.*, i.title as item_title, i.photo_path, i.location, i.date_found, i.description as item_description,
               u.username as finder_name, u.email as finder_email, u.phone as finder_phone
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
    
    <div class="claim-details-grid">
        <!-- Item Information Section -->
        <section class="item-info-section card">
            <h3 class="section-title">Found Item Information</h3>
            <div class="item-details">
                <h4><?= htmlspecialchars($claim['item_title']) ?></h4>
                
                <?php if (!empty($claim['photo_path'])): ?>
                    <div class="item-image">
                        <img src="/<?= htmlspecialchars($claim['photo_path']) ?>" 
                             alt="Item photo" style="max-width: 300px; height: auto;">
                    </div>
                <?php endif; ?>

                <div class="item-info">
                    <p><strong>Found by:</strong> <?= htmlspecialchars($claim['finder_name']) ?></p>
                    <p><strong>Location Found:</strong> <?= htmlspecialchars($claim['location']) ?></p>
                    <p><strong>Date Found:</strong> <?= htmlspecialchars($claim['date_found']) ?></p>
                    
                    <div class="item-description">
                        <p><strong>Description:</strong></p>
                        <div class="description-text bg-light">
                            <?= nl2br(htmlspecialchars($claim['item_description'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Claim Information Section -->
        <section class="claim-info-section card">
            <h3 class="section-title">Your Claim Information</h3>
            <div class="claim-status">
                <p><strong>Status:</strong> 
                    <span class="status-badge <?= $claim['status'] ?>">
                        <?= ucfirst(htmlspecialchars($claim['status'])) ?>
                    </span>
                </p>
                <p><strong>Your Description:</strong></p>
                <div class="claim-description bg-light">
                    <?= nl2br(htmlspecialchars($claim['description'])) ?>
                </div>
                
                <?php if (!empty($claim['evidence_img'])): ?>
                    <div class="evidence-image">
                        <h4>Your Evidence Photo:</h4>
                        <img src="/<?= htmlspecialchars($claim['evidence_img']) ?>" 
                             alt="Evidence photo" style="max-width: 300px; height: auto;">
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($claim['status'] === 'approved'): ?>
                <div class="contact-info card">
                    <h4>Finder's Contact Information</h4>
                    <p><strong>Email:</strong> <?= htmlspecialchars($claim['finder_email']) ?></p>
                    <?php if (!empty($claim['finder_phone'])): ?>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($claim['finder_phone']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="action-buttons">
                    <a href="../chat/room.php?claim_id=<?= $claim['claim_id'] ?>" 
                       class="btn btn-primary">Chat with Finder</a>
                </div>
            <?php endif; ?>
        </section>
    </div>
    
    <div class="bottom-actions">
        <a href="/user/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<style>
.claim-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    height: 100%;  /* 確保卡片高度一致 */
}

.section-title {
    color: #2c3e50;
    border-bottom: 2px solid #eee;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
    flex-shrink: 0;  /* 防止標題被壓縮 */
}

.item-details, .claim-status {
    flex-grow: 1;    /* 讓內容區域可以彈性擴展 */
    display: flex;
    flex-direction: column;
}

.item-info, .claim-description {
    flex-grow: 1;    /* 讓內容區域填滿剩餘空間 */
}

.evidence-image, .contact-info {
    margin-top: auto; /* 將圖片和聯絡資訊推到底部 */
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-weight: bold;
}

.status-badge.pending {
    background-color: #ffd700;
    color: #000;
}

.status-badge.approved {
    background-color: #28a745;
    color: #fff;
}

.status-badge.rejected {
    background-color: #dc3545;
    color: #fff;
}

.description-text, .claim-description {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    margin-top: 0.5rem;
    line-height: 1.5;
}

.contact-info {
    background: #e9ecef;
    margin-top: 1rem;
}

.action-buttons {
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .claim-details-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
