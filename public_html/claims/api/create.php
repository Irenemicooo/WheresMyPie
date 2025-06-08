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
    if (empty($_POST['item_id']) || empty($_POST['description'])) {
        throw new Exception('Item ID and description are required');
    }

    // Check if item exists and is available
    $stmt = $pdo->prepare("SELECT user_id, status FROM items WHERE item_id = ?");
    $stmt->execute([$_POST['item_id']]);
    $item = $stmt->fetch();

    if (!$item || $item['status'] !== 'available') {
        throw new Exception('Item not available for claiming');
    }

    if ($item['user_id'] === $_SESSION['user_id']) {
        throw new Exception('You cannot claim your own item');
    }

    // Handle evidence photo upload
    $evidence_path = null;
    if (isset($_FILES['evidence_img']) && $_FILES['evidence_img']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/evidence/';
        $evidence_path = 'uploads/evidence/' . uploadFile($_FILES['evidence_img'], $uploadDir);
    }

    // Create claim
    $stmt = $pdo->prepare("
        INSERT INTO claims (item_id, user_id, description, evidence_img)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_POST['item_id'],
        $_SESSION['user_id'],
        $_POST['description'],
        $evidence_path
    ]);

    $response['success'] = true;
    $response['data'] = ['claim_id' => $pdo->lastInsertId()];
    $response['message'] = 'Claim submitted successfully';

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to submit claim';
}

echo json_encode($response);
