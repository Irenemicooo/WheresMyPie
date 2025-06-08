<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/RateLimit.php';

header('Content-Type: application/json');

$response = ['success' => false, 'suggestions' => []];

try {
    $rateLimit = new RateLimit();
    $clientIp = $_SERVER['REMOTE_ADDR'];
    
    if (!$rateLimit->check("suggest:$clientIp", 60)) { // 60 requests per hour
        throw new Exception('Rate limit exceeded');
    }

    $query = trim($_GET['q'] ?? '');
    if (strlen($query) < 2) {
        throw new Exception('Query too short');
    }

    $stmt = $pdo->prepare("
        SELECT item_id, title, category
        FROM items 
        WHERE status = 'available' 
        AND (title LIKE ? OR description LIKE ?)
        LIMIT 5
    ");
    
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    
    $response['suggestions'] = $stmt->fetchAll();
    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to get suggestions';
}

echo json_encode($response);
