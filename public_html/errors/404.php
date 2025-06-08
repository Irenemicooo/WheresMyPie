<?php
http_response_code(404);
$pageTitle = 'Page Not Found';
include '../includes/header.php';
?>

<div class="container error-page">
    <div class="error-content">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>Sorry, the page you are looking for might have been removed or is temporarily unavailable.</p>
        <div class="error-actions">
            <a href="<?= BASE_URL ?>" class="btn btn-primary">Go to Homepage</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
