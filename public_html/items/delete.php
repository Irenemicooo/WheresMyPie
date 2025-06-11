<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth($pdo);
$auth->requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('error', 'Invalid item ID');
    redirect('/items/index.php');
}

$item_id = (int)$_GET['id'];

try {
    // 開始交易
    $pdo->beginTransaction();

    // 檢查物品是否屬於當前使用者且沒有認領申請
    $stmt = $pdo->prepare("
        SELECT i.*, 
            (SELECT COUNT(*) FROM claims c WHERE c.item_id = i.item_id) as claim_count
        FROM items i 
        WHERE i.item_id = ? AND i.user_id = ?
    ");
    $stmt->execute([$item_id, $_SESSION['user_id']]);
    $item = $stmt->fetch();

    if (!$item) {
        throw new Exception('Item not found or access denied');
    }

    if ($item['claim_count'] > 0) {
        throw new Exception('Cannot delete item with existing claims');
    }

    // 刪除相關的照片檔案
    if (!empty($item['photo_path'])) {
        $photoPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $item['photo_path'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }

    // 刪除物品記錄
    $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);

    $pdo->commit();
    setFlashMessage('success', 'Item deleted successfully');
    redirect('/items/index.php');

} catch (Exception $e) {
    $pdo->rollBack();
    setFlashMessage('error', DEBUG ? $e->getMessage() : 'Failed to delete item');
    redirect('/items/view.php?id=' . $item_id);
}
?>