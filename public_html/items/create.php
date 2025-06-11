<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/image.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth($pdo);
$auth->requireLogin();

$errors = [];
$title = '';
$description = '';
$category = '';
$location = '';
$date_found = '';
$photo_path = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $date_found = $_POST['date_found'] ?? '';

        // validate inputs
        if (empty($title)) $errors[] = 'Title is required.';
        if (empty($category)) $errors[] = 'Category is required.';
        if (empty($location)) $errors[] = 'Location is required.';
        if (empty($date_found)) $errors[] = 'Date found is required.';

        // process photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $imageHandler = new ImageHandler();
            $filename = $imageHandler->processUpload($_FILES['photo'], '../uploads/items/');
            $photo_path = 'uploads/items/' . $filename;
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO items (title, description, category, location, date_found, photo_path, user_id) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $title,
                $description,
                $category,
                $location,
                $date_found,
                $photo_path,
                $_SESSION['user_id']
            ]);

            header("Location: index.php");
            exit;
        }
    } catch (Exception $e) {
        if (DEBUG) {
            $errors[] = $e->getMessage();
        } else {
            $errors[] = 'Error saving item. Please try again.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Report a Found Item</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="create.php" method="post" enctype="multipart/form-data">
        <label for="title">Title *</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required>

        <label for="description">Description</label>
        <textarea name="description" id="description"><?= htmlspecialchars($description) ?></textarea>

        <label for="category">Category *</label>
        <input type="text" name="category" id="category" value="<?= htmlspecialchars($category) ?>" required>

        <label for="location">Location Found *</label>
        <input type="text" name="location" id="location" value="<?= htmlspecialchars($location) ?>" required>

        <label for="date_found">Date Found *</label>
        <input type="date" name="date_found" id="date_found" value="<?= htmlspecialchars($date_found) ?>" required>

        <label for="photo">Upload Photo</label>
        <input type="file" name="photo" id="photo" accept="image/*">

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>