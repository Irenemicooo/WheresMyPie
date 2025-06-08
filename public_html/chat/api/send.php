<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');
session_start();

$response = ['success' => false, 'data' => null, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    $response['message'] = 'Unauthorized';
    exit(json_encode($response));
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['claim_id']) || empty($data['content'])) {
        throw new Exception('Claim ID and message content are required');
    }

    // Verify user is part of this chat
    $stmt = $pdo->prepare("
        SELECT c.*, i.user_id as finder_id 
        FROM claims c
        JOIN items i ON c.item_id = i.item_id
        WHERE c.claim_id = ?
    ");
    $stmt->execute([$data['claim_id']]);
    $claim = $stmt->fetch();

    if (!$claim || ($_SESSION['user_id'] !== $claim['user_id'] && 
                    $_SESSION['user_id'] !== $claim['finder_id'])) {
        throw new Exception('Not authorized to send messages in this chat');
    }

    // Insert message
    $stmt = $pdo->prepare("
        INSERT INTO chat_messages (claim_id, user_id, content)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $data['claim_id'],
        $_SESSION['user_id'],
        $data['content']
    ]);

    $response['success'] = true;
    $response['data'] = [
        'message_id' => $pdo->lastInsertId(),
        'timestamp' => date('Y-m-d H:i:s')
    ];

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to send message';
}

echo json_encode($response);
