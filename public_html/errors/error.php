<?php
$error_code = $_GET['code'] ?? 500;
$error_message = $_GET['message'] ?? 'An unexpected error occurred.';
http_response_code($error_code);

$pageTitle = 'Error';
include '../includes/header.php';
?>

<div class="container error-page">
    <div class="error-content">
        <h1>Error <?= htmlspecialchars($error_code) ?></h1>
        <p><?= htmlspecialchars($error_message) ?></p>
        <div class="error-actions">
            <a href="<?= BASE_URL ?>" class="btn btn-primary">Go to Homepage</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
