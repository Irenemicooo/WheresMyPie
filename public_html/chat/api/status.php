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

    // Update user's last activity time
    $stmt = $pdo->prepare("
        UPDATE users 
        SET last_active = CURRENT_TIMESTAMP 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);

    // Get other participant's status
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username, u.last_active
        FROM claims c
        JOIN items i ON c.item_id = i.item_id
        JOIN users u ON (
            CASE 
                WHEN c.user_id = ? THEN i.user_id = u.user_id
                ELSE c.user_id = u.user_id
            END
        )
        WHERE c.claim_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $_GET['claim_id']]);
    
    $otherUser = $stmt->fetch();
    if ($otherUser) {
        $otherUser['is_online'] = (time() - strtotime($otherUser['last_active'])) < 300; // 5 minutes
    }

    $response['success'] = true;
    $response['data'] = $otherUser;

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to update status';
}

echo json_encode($response);
