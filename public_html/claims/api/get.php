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
    if (isset($_GET['id'])) {
        // Get specific claim
        $stmt = $pdo->prepare("
            SELECT c.*, i.title as item_title, u.username as claimer_name
            FROM claims c 
            JOIN items i ON c.item_id = i.item_id 
            JOIN users u ON c.user_id = u.user_id
            WHERE c.claim_id = ? AND (c.user_id = ? OR i.user_id = ?)
        ");
        $stmt->execute([$_GET['id'], $_SESSION['user_id'], $_SESSION['user_id']]);
        $response['data'] = $stmt->fetch();
    } else {
        // Get all claims for user (both made and received)
        $stmt = $pdo->prepare("
            SELECT c.*, i.title as item_title, u.username as claimer_name
            FROM claims c 
            JOIN items i ON c.item_id = i.item_id 
            JOIN users u ON c.user_id = u.user_id
            WHERE c.user_id = ? OR i.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
        $response['data'] = $stmt->fetchAll();
    }

    $response['success'] = true;

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = DEBUG ? $e->getMessage() : 'Failed to fetch claims';
}

echo json_encode($response);
