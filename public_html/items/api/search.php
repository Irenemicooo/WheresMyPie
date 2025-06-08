<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');

try {
    $response = ['success' => false, 'data' => null, 'message' => ''];
    
    $sql = "SELECT i.*, u.username FROM items i 
            JOIN users u ON i.user_id = u.user_id WHERE 1=1";
    $params = [];
    
    // Search by keyword
    if (!empty($_GET['keyword'])) {
        $keyword = '%' . $_GET['keyword'] . '%';
        $sql .= " AND (i.title LIKE ? OR i.description LIKE ?)";
        $params[] = $keyword;
        $params[] = $keyword;
    }
    
    // Filter by category
    if (!empty($_GET['category'])) {
        $sql .= " AND i.category = ?";
        $params[] = $_GET['category'];
    }
    
    // Filter by date range
    if (!empty($_GET['date_from'])) {
        $sql .= " AND i.date_found >= ?";
        $params[] = $_GET['date_from'];
    }
    if (!empty($_GET['date_to'])) {
        $sql .= " AND i.date_found <= ?";
        $params[] = $_GET['date_to'];
    }
    
    $sql .= " ORDER BY i.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $response['data'] = $stmt->fetchAll();
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = DEBUG ? $e->getMessage() : 'Search failed';
}

echo json_encode($response);
