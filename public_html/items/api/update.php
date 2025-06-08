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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['item_id'])) {
        throw new Exception('Item ID is required');
    }

    // Check item ownership
    $stmt = $pdo->prepare("SELECT user_id FROM items WHERE item_id = ?");
    $stmt->execute([$data['item_id']]);
    $item = $stmt->fetch();
    
    if (!$item || $item['user_id'] !== $_SESSION['user_id']) {
        throw new Exception('Not authorized to update this item');
    }

    // Build update query
    $updates = [];
    $params = [];
    
    if (isset($data['title'])) {
        $updates[] = "title = ?";
        $params[] = $data['title'];
    }
    if (isset($data['description'])) {
        $updates[] = "description = ?";
        $params[] = $data['description'];
    }
    if (isset($data['category'])) {
        $updates[] = "category = ?";
        $params[] = $data['category'];
    }
    if (isset($data['location'])) {
        $updates[] = "location = ?";
        $params[] = $data['location'];
    }
    if (isset($data['status'])) {
        $updates[] = "status = ?";
        $params[] = $data['status'];
    }
    
    $params[] = $data['item_id'];
    
    $sql = "UPDATE items SET " . implode(", ", $updates) . " WHERE item_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $response['success'] = true;
    $response['data'] = ['id' => $data['item_id']];
    
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to update item';
}

echo json_encode($response);
