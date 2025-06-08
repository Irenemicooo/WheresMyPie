<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
session_start();

// login needed to edit items
if (!$auth->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// check if item ID is provided and is numeric
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid item ID.";
    exit;
}

$item_id = (int) $_GET['id'];

// get item details
$stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    echo "Item not found.";
    exit;
}

// only the owner can edit the item
if ($_SESSION['user_id'] !== $item['user_id']) {
    echo "You are not authorized to edit this item.";
    exit;
}

// initialize variables
$title = $item['title'];
$description = $item['description'];
$category = $item['category'];
$location = $item['location'];
$date_found = $item['date_found'];
$errors = [];

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date_found = $_POST['date_found'] ?? '';

    // validate inputs
    if (empty($title)) $errors[] = "Title is required.";
    if (empty($category)) $errors[] = "Category is required.";
    if (empty($location)) $errors[] = "Location is required.";
    if (empty($date_found)) $errors[] = "Date found is required.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE items
                SET title = ?, description = ?, category = ?, location = ?, date_found = ?
                WHERE item_id = ?
            ");
            $stmt->execute([$title, $description, $category, $location, $date_found, $item_id]);

            $_SESSION['success'] = "Item updated successfully.";
            header("Location: view.php?id=" . $item_id);
            exit;
        } catch (Exception $e) {
            $errors[] = DEBUG ? $e->getMessage() : "Failed to update item.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Edit Item</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="edit.php?id=<?= $item_id ?>" method="post">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description"><?= htmlspecialchars($description) ?></textarea>

        <label for="category">Category:</label>
        <input type="text" name="category" id="category" value="<?= htmlspecialchars($category) ?>" required>

        <label for="location">Location Found:</label>
        <input type="text" name="location" id="location" value="<?= htmlspecialchars($location) ?>" required>

        <label for="date_found">Date Found:</label>
        <input type="date" name="date_found" id="date_found" value="<?= htmlspecialchars($date_found) ?>" required>

        <button type="submit">Update Item</button>
        <a href="view.php?id=<?= $item_id ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>