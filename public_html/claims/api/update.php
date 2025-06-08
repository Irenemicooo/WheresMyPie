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
    echo json_encode($response);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['claim_id']) || !isset($data['status'])) {
        throw new Exception('Claim ID and status are required');
    }

    if (!in_array($data['status'], ['approved', 'rejected'])) {
        throw new Exception('Invalid status value');
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
        throw new Exception('Not authorized to update this claim');
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Update claim status
    $stmt = $pdo->prepare("UPDATE claims SET status = ? WHERE claim_id = ?");
    $stmt->execute([$data['status'], $data['claim_id']]);

    // If approved, update item status and reject other claims
    if ($data['status'] === 'approved') {
        // Get item_id for this claim
        $stmt = $pdo->prepare("SELECT item_id FROM claims WHERE claim_id = ?");
        $stmt->execute([$data['claim_id']]);
        $claim = $stmt->fetch();

        // Update item status
        $stmt = $pdo->prepare("UPDATE items SET status = 'claimed' WHERE item_id = ?");
        $stmt->execute([$claim['item_id']]);

        // Reject other pending claims for this item
        $stmt = $pdo->prepare("
            UPDATE claims 
            SET status = 'rejected' 
            WHERE item_id = ? AND claim_id != ? AND status = 'pending'
        ");
        $stmt->execute([$claim['item_id'], $data['claim_id']]);
    }

    $pdo->commit();
    
    $response['success'] = true;
    $response['message'] = 'Claim status updated successfully';
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to update claim status';
}

echo json_encode($response);
