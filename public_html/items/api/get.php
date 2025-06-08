<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

try {
    $response = ['success' => false, 'data' => null, 'message' => ''];
    
    if (isset($_GET['id'])) {
        // Get single item
        $stmt = $pdo->prepare("SELECT i.*, u.username FROM items i 
                              JOIN users u ON i.user_id = u.user_id 
                              WHERE i.item_id = ?");
        $stmt->execute([$_GET['id']]);
        $response['data'] = $stmt->fetch();
    } else {
        // Get all items with optional filters
        $sql = "SELECT i.*, u.username FROM items i 
                JOIN users u ON i.user_id = u.user_id WHERE 1=1";
        $params = [];
        
        if (isset($_GET['status'])) {
            $sql .= " AND i.status = ?";
            $params[] = $_GET['status'];
        }
        
        if (isset($_GET['category'])) {
            $sql .= " AND i.category = ?";
            $params[] = $_GET['category'];
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $response['data'] = $stmt->fetchAll();
    }
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = DEBUG ? $e->getMessage() : 'An error occurred';
}

echo json_encode($response);
