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
    if (empty($_GET['claim_id'])) {
        throw new Exception('Claim ID is required');
    }

    $lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

    // Verify user is part of this chat
    $stmt = $pdo->prepare("
        SELECT c.*, i.user_id as finder_id 
        FROM claims c
        JOIN items i ON c.item_id = i.item_id
        WHERE c.claim_id = ?
    ");
    $stmt->execute([$_GET['claim_id']]);
    $claim = $stmt->fetch();

    if (!$claim || ($_SESSION['user_id'] !== $claim['user_id'] && 
                    $_SESSION['user_id'] !== $claim['finder_id'])) {
        throw new Exception('Not authorized to view this chat');
    }

    // Get messages
    $stmt = $pdo->prepare("
        SELECT m.*, u.username
        FROM chat_messages m
        JOIN users u ON m.user_id = u.user_id
        WHERE m.claim_id = ? AND m.message_id > ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$_GET['claim_id'], $lastId]);
    
    $messages = $stmt->fetchAll();
    foreach ($messages as &$message) {
        $message['is_mine'] = $message['user_id'] === $_SESSION['user_id'];
    }

    $response['success'] = true;
    $response['data'] = $messages;

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to fetch messages';
}

echo json_encode($response);
