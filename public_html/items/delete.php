<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

// need login to delete items
if (!$auth->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// check if item ID is provided and is numeric
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid item ID.";
    exit;
}

$item_id = (int)$_GET['id'];

// get item details
$stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    echo "Item not found.";
    exit;
}

// check if the logged-in user is the owner of the item
if ($_SESSION['user_id'] !== $item['user_id']) {
    echo "You are not authorized to delete this item.";
    exit;
}

// delete the photo file if it exists
if (!empty($item['photo_path'])) {
    $photoFilePath = '../' . ltrim($item['photo_path'], '/'); // make sure the path is correct
    if (file_exists($photoFilePath)) {
        unlink($photoFilePath);
    }
}

// delete the item from the database
try {
    $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);

    $_SESSION['success'] = "Item and photo deleted successfully.";
    header("Location: index.php");
    exit;
} catch (Exception $e) {
    echo DEBUG ? $e->getMessage() : "Failed to delete item.";
}
?>