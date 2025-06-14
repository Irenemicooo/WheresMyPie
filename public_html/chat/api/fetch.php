<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    if (empty($_GET['claim_id'])) {
        throw new Exception('Claim ID is required');
    }

    // validate user permissions
    $stmt = $pdo->prepare("
        SELECT i.user_id as finder_id, c.user_id as claimer_id
        FROM claims c
        JOIN items i ON c.item_id = i.item_id
        WHERE c.claim_id = ? AND c.status = 'approved'
    ");
    $stmt->execute([$_GET['claim_id']]);
    $chat = $stmt->fetch();

    if (!$chat || ($_SESSION['user_id'] != $chat['finder_id'] && $_SESSION['user_id'] != $chat['claimer_id'])) {
        throw new Exception('Not authorized to view this chat');
    }

    // build fetch query
    $sql = "
        SELECT m.*, u.username 
        FROM chat_messages m
        JOIN users u ON m.user_id = u.user_id
        WHERE m.claim_id = ?
    ";

    $params = [$_GET['claim_id']];

    // Add condition for only fetching new messages
    if (!empty($_GET['last_id'])) {
        $sql .= " AND m.message_id > ?";
        $params[] = $_GET['last_id'];
    }

    $sql .= " ORDER BY m.created_at ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as &$message) {
        $message['is_mine'] = $message['user_id'] == $_SESSION['user_id'];
    }

    $response['success'] = true;
    $response['data'] = $messages;

} catch (Exception $e) {
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to fetch messages';
}

echo json_encode($response);
