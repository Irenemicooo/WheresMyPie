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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit;
}

try {
    $item_id = $_POST['item_id'] ?? $_GET['id'] ?? null;
    if (!$item_id) {
        throw new Exception('Item ID is required');
    }

    // Check item ownership and get photo path
    $stmt = $pdo->prepare("SELECT user_id, photo_path FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    
    if (!$item || $item['user_id'] !== $_SESSION['user_id']) {
        throw new Exception('Not authorized to delete this item');
    }

    // Delete associated claims first
    $stmt = $pdo->prepare("DELETE FROM claims WHERE item_id = ?");
    $stmt->execute([$item_id]);

    // Delete the item
    $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);

    // Delete photo if exists
    if ($item['photo_path']) {
        $photoPath = '../../' . $item['photo_path'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Item deleted successfully';
    
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to delete item';
}

echo json_encode($response);
