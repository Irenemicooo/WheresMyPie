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
    if (!isset($_POST['item_id']) || !isset($_FILES['photo'])) {
        throw new Exception('Item ID and photo are required');
    }

    // Check item ownership
    $stmt = $pdo->prepare("SELECT user_id, photo_path FROM items WHERE item_id = ?");
    $stmt->execute([$_POST['item_id']]);
    $item = $stmt->fetch();
    
    if (!$item || $item['user_id'] !== $_SESSION['user_id']) {
        throw new Exception('Not authorized to update this item');
    }

    // Delete old photo if exists
    if ($item['photo_path']) {
        $oldPhotoPath = '../../' . $item['photo_path'];
        if (file_exists($oldPhotoPath)) {
            unlink($oldPhotoPath);
        }
    }

    // Upload new photo
    $uploadDir = '../../uploads/items/';
    $photo_path = 'uploads/items/' . uploadFile($_FILES['photo'], $uploadDir);

    // Update database
    $stmt = $pdo->prepare("UPDATE items SET photo_path = ? WHERE item_id = ?");
    $stmt->execute([$photo_path, $_POST['item_id']]);
    
    $response['success'] = true;
    $response['data'] = ['photo_path' => $photo_path];
    $response['message'] = 'Photo updated successfully';
    
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to update photo';
}

echo json_encode($response);
