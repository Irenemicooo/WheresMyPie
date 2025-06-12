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

    <script>
    document.getElementById('chatForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const messageInput = document.getElementById('messageInput');
        const claimId = document.getElementById('claimId').value;
        const content = messageInput.value.trim();

        if (!content) return;

        try {
            const response = await fetch('/chat/api/send.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    claim_id: claimId,
                    content: content
                })
            });
            const data = await response.json();
            
            if (data.success) {
                messageInput.value = '';
                await loadMessages(); // 立即重新加載消息
            } else {
                alert(data.message || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to send message');
        }
    });

    async function loadMessages() {
        const claimId = document.getElementById('claimId').value;
        try {
            const response = await fetch(`/chat/api/fetch.php?claim_id=${claimId}`);
            const data = await response.json();
            
            if (data.success) {
                const chatMessages = document.getElementById('chatMessages');
                chatMessages.innerHTML = data.data.map(message => `
                    <div class="message ${message.user_id == <?= $_SESSION['user_id'] ?> ? 'mine' : 'other'}">
                        <strong>${message.username}:</strong>
                        <p>${message.content}</p>
                        <small>${new Date(message.created_at).toLocaleString()}</small>
                    </div>
                `).join('');
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    // 初始加載消息
    loadMessages();

    // 每5秒更新一次
    setInterval(loadMessages, 5000);
    </script>
</div>

<?php include '../includes/footer.php'; ?>
