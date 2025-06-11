<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

$auth = new Auth($pdo);
$auth->requireLogin();

// Get claims for items posted by the current user
$stmt = $pdo->prepare("
    SELECT c.*, i.title as item_title, u.username as claimer_name
    FROM claims c 
    JOIN items i ON c.item_id = i.item_id 
    JOIN users u ON c.user_id = u.user_id
    WHERE i.user_id = ? AND c.status = 'pending'
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$claims = $stmt->fetchAll();

$pageTitle = 'Review Claims';
include '../includes/header.php';
?>

<div class="container">
    <h2>Review Claims</h2>

    <?php if (empty($claims)): ?>
        <p>No pending claims to review.</p>
    <?php else: ?>
        <div class="claims-list">
            <?php foreach ($claims as $claim): ?>
                <div class="claim-card">
                    <h3>Item: <?= htmlspecialchars($claim['item_title']) ?></h3>
                    <p>Claimed by: <?= htmlspecialchars($claim['claimer_name']) ?></p>
                    <p>Description: <?= nl2br(htmlspecialchars($claim['description'])) ?></p>
                    
                    <?php if ($claim['evidence_img']): ?>
                        <div class="evidence-image">
                            <img src="/<?= htmlspecialchars($claim['evidence_img']) ?>" 
                                 alt="Evidence" 
                                 style="max-width: 200px; max-height: 200px; object-fit: contain;">
                        </div>
                    <?php endif; ?>

                    <div class="claim-actions">
                        <button onclick="approveClaim(<?= $claim['claim_id'] ?>)" 
                                class="btn btn-success">Approve</button>
                        <button onclick="rejectClaim(<?= $claim['claim_id'] ?>)" 
                                class="btn btn-danger">Reject</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function approveClaim(claimId) {
    updateClaimStatus(claimId, 'approved');
}

function rejectClaim(claimId) {
    updateClaimStatus(claimId, 'rejected');
}

function updateClaimStatus(claimId, status) {
    if (!confirm('Are you sure you want to ' + status + ' this claim?')) {
        return;
    }

    fetch('/claims/api/update.php', {  // 修改為完整路徑
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            claim_id: claimId,
            status: status
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update claim status. Please try again.');
    });
}
</script>

<?php include '../includes/footer.php'; ?>
