<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

$auth = new Auth($pdo);
$auth->requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid item ID');
    redirect('/items/index.php');
}

$item_id = (int)$_GET['id'];

try {
    // Get item details and verify ownership
    $stmt = $pdo->prepare("
        SELECT i.*, c.claim_id, c.user_id as claimer_id, c.status as claim_status
        FROM items i 
        LEFT JOIN claims c ON i.item_id = c.item_id AND c.status = 'approved'
        WHERE i.item_id = ? AND i.user_id = ?
    ");
    $stmt->execute([$item_id, $_SESSION['user_id']]);
    $item = $stmt->fetch();

    if (!$item) {
        throw new Exception('Item not found or access denied');
    }

    if ($item['status'] !== 'claimed') {
        throw new Exception('Only claimed items can be marked as returned');
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Update item status
    $stmt = $pdo->prepare("UPDATE items SET status = 'returned' WHERE item_id = ?");
    $stmt->execute([$item_id]);

    // Add return confirmation message in chat
    $stmt = $pdo->prepare("
        INSERT INTO chat_messages (claim_id, user_id, content)
        VALUES (?, ?, 'Item has been marked as returned.')
    ");
    $stmt->execute([$item['claim_id'], $_SESSION['user_id']]);

    $pdo->commit();
    setFlashMessage('success', 'Item has been marked as returned successfully');
    redirect('/items/view.php?id=' . $item_id);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlashMessage('error', DEBUG ? $e->getMessage() : 'Failed to process return');
    redirect('/items/view.php?id=' . $item_id);
}
