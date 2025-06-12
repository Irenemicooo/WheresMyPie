<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['claim_id']) || empty($data['content'])) {
        throw new Exception('Missing required fields');
    }

    // 驗證使用者權限
    $stmt = $pdo->prepare("
        SELECT i.user_id as finder_id, c.user_id as claimer_id
        FROM claims c
        JOIN items i ON c.item_id = i.item_id
        WHERE c.claim_id = ? AND c.status = 'approved'
    ");
    $stmt->execute([$data['claim_id']]);
    $chat = $stmt->fetch();

    if (!$chat || ($_SESSION['user_id'] != $chat['finder_id'] && $_SESSION['user_id'] != $chat['claimer_id'])) {
        throw new Exception('Not authorized to send messages in this chat');
    }

    // 儲存訊息
    $stmt = $pdo->prepare("
        INSERT INTO chat_messages (claim_id, user_id, content)
        VALUES (?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $data['claim_id'],
        $_SESSION['user_id'],
        trim($data['content'])
    ]);

    if (!$result) {
        throw new Exception('Failed to save message');
    }

    $response['success'] = true;
    $response['message'] = 'Message sent successfully';

} catch (Exception $e) {
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to send message';
}

echo json_encode($response);
