<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => null, 'message' => ''];

// Verify user is logged in
session_start();
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
    $data = $_POST;  // Changed to handle multipart form data
    $photo_path = null;
    
    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploadDir = '../../uploads/items/';
            $photo_path = 'uploads/items/' . uploadFile($_FILES['photo'], $uploadDir);
        } catch (RuntimeException $e) {
            throw new Exception('File upload failed: ' . $e->getMessage());
        }
    }
    
    // Validate required fields
    $required = ['title', 'category', 'location', 'date_found'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO items (title, description, category, location, 
                          date_found, photo_path, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['title'],
        $data['description'] ?? null,
        $data['category'],
        $data['location'],
        $data['date_found'],
        $photo_path,
        $_SESSION['user_id']
    ]);
    
    $response['success'] = true;
    $response['data'] = ['id' => $pdo->lastInsertId()];
    
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to create item';
}

echo json_encode($response);
