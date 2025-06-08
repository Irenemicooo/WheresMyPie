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
    
    if (!isset($data['item_id']) || !isset($data['status'])) {
        throw new Exception('Item ID and status are required');
    }

    if (!in_array($data['status'], ['available', 'claimed', 'returned'])) {
        throw new Exception('Invalid status value');
    }

    // Check item ownership
    $stmt = $pdo->prepare("SELECT user_id FROM items WHERE item_id = ?");
    $stmt->execute([$data['item_id']]);
    $item = $stmt->fetch();
    
    if (!$item || $item['user_id'] !== $_SESSION['user_id']) {
        throw new Exception('Not authorized to update this item');
    }

    $stmt = $pdo->prepare("UPDATE items SET status = ? WHERE item_id = ?");
    $stmt->execute([$data['status'], $data['item_id']]);
    
    $response['success'] = true;
    $response['message'] = 'Status updated successfully';
    
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to update status';
}

echo json_encode($response);
