<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

$auth = new Auth($pdo);
$auth->requireLogin();

$claim_id = isset($_GET['claim_id']) ? (int)$_GET['claim_id'] : 0;

// Get claim and chat details
$stmt = $pdo->prepare("
    SELECT c.*, i.title as item_title, i.user_id as finder_id,
           u1.username as claimer_name, u2.username as finder_name
    FROM claims c
    JOIN items i ON c.item_id = i.item_id
    JOIN users u1 ON c.user_id = u1.user_id
    JOIN users u2 ON i.user_id = u2.user_id
    WHERE c.claim_id = ? AND (c.user_id = ? OR i.user_id = ?)
");
$stmt->execute([$claim_id, $_SESSION['user_id'], $_SESSION['user_id']]);
$claim = $stmt->fetch();

if (!$claim) {
    setFlashMessage('error', 'Chat room not found or access denied');
    redirect('/user/dashboard.php');
}

$pageTitle = "Chat - {$claim['item_title']}";
$pageScripts = ['chat.js'];
include '../includes/header.php';
?>

<div class="container chat-container">
    <div class="chat-header">
        <h2>Chat: <?= htmlspecialchars($claim['item_title']) ?></h2>
        <div class="chat-participants">
            Finder: <?= htmlspecialchars($claim['finder_name']) ?> |
            Claimer: <?= htmlspecialchars($claim['claimer_name']) ?>
        </div>
    </div>

    <div class="chat-messages" id="chatMessages">
        <!-- Messages will be loaded here -->
    </div>

    <form id="chatForm" class="chat-form">
        <input type="hidden" id="claimId" value="<?= $claim_id ?>">
        <div class="input-group">
            <input type="text" id="messageInput" class="form-control" 
                   placeholder="Type your message..." required>
            <button type="submit" class="btn btn-primary">Send</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
