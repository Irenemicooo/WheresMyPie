<?php
http_response_code(403);
$pageTitle = 'Access Denied';
include '../includes/header.php';
?>

<div class="container error-page">
    <div class="error-content">
        <h1>403</h1>
        <h2>Access Denied</h2>
        <p>Sorry, you don't have permission to access this page.</p>
        <div class="error-actions">
            <a href="<?= BASE_URL ?>" class="btn btn-primary">Go to Homepage</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary">Login</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
