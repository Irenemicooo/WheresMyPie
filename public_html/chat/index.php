<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

$auth = new Auth($pdo);
$auth->requireLogin();

// Get all chats where user is either the finder or claimer
$stmt = $pdo->prepare("
    SELECT 
        c.claim_id,
        c.status as claim_status,
        i.title as item_title,
        i.user_id as finder_id,
        cl.user_id as claimer_id,
        uf.username as finder_name,
        uc.username as claimer_name,
        (
            SELECT MAX(created_at) 
            FROM chat_messages 
            WHERE claim_id = c.claim_id
        ) as last_message_time,
        (
            SELECT COUNT(*) 
            FROM chat_messages 
            WHERE claim_id = c.claim_id
        ) as message_count
    FROM claims c
    JOIN items i ON c.item_id = i.item_id
    JOIN users uf ON i.user_id = uf.user_id
    JOIN users uc ON c.user_id = uc.user_id
    WHERE i.user_id = ? OR c.user_id = ?
    ORDER BY last_message_time DESC NULLS LAST
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$chats = $stmt->fetchAll();

$pageTitle = 'My Chats';
include '../includes/header.php';
?>

<div class="container">
    <h2>My Chats</h2>

    <?php if (empty($chats)): ?>
        <p>No active chats found.</p>
    <?php else: ?>
        <div class="chat-list">
            <?php foreach ($chats as $chat): ?>
                <?php
                $isParticipant = $_SESSION['user_id'] === $chat['finder_id'] || 
                                $_SESSION['user_id'] === $chat['claimer_id'];
                $otherUser = $_SESSION['user_id'] === $chat['finder_id'] ? 
                            $chat['claimer_name'] : $chat['finder_name'];
                ?>
                <div class="chat-item">
                    <div class="chat-info">
                        <h3><?= htmlspecialchars($chat['item_title']) ?></h3>
                        <p>Chatting with: <?= htmlspecialchars($otherUser) ?></p>
                        <p>Claim Status: <span class="status-<?= $chat['claim_status'] ?>">
                            <?= ucfirst($chat['claim_status']) ?>
                        </span></p>
                        <?php if ($chat['message_count'] > 0): ?>
                            <p>Messages: <?= $chat['message_count'] ?></p>
                            <p>Last activity: <?= date('Y-m-d H:i', strtotime($chat['last_message_time'])) ?></p>
                        <?php else: ?>
                            <p>No messages yet</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($isParticipant): ?>
                        <div class="chat-actions">
                            <a href="room.php?claim_id=<?= $chat['claim_id'] ?>" 
                               class="btn btn-primary">Open Chat</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
