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
    
    if (!isset($data['claim_id']) || !isset($data['status'])) {
        throw new Exception('Missing required fields');
    }

    if (!in_array($data['status'], ['approved', 'rejected'])) {
        throw new Exception('Invalid status');
    }

    // Verify ownership of the claimed item
    $stmt = $pdo->prepare("
        SELECT i.user_id 
        FROM claims c
        JOIN items i ON c.item_id = i.item_id
        WHERE c.claim_id = ?
    ");
    $stmt->execute([$data['claim_id']]);
    $item = $stmt->fetch();

    if (!$item || $item['user_id'] !== $_SESSION['user_id']) {
        throw new Exception('Not authorized');
    }

    // Update claim status
    $stmt = $pdo->prepare("UPDATE claims SET status = ? WHERE claim_id = ?");
    $stmt->execute([$data['status'], $data['claim_id']]);

    $response['success'] = true;
    $response['message'] = 'Status updated successfully';

} catch (Exception $e) {
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to update status';
}

echo json_encode($response);
