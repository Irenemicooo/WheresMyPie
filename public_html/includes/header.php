<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'auth.php';

// Check if there's a flash message
$flash = getFlashMessage();

// Set default page title if not set
if (!isset($page_title)) {
    $page_title = 'Lost and Found System';
}
?>
<?php if (!isset($pageTitle)) $pageTitle = APP_NAME; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= getAssetUrl('css/style.css') ?>">
    <?php if (isset($pageStyles)): ?>
        <?php foreach ($pageStyles as $style): ?>
            <link rel="stylesheet" href="<?= getAssetUrl('css/' . $style) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= isset($body_class) ? sanitize($body_class) : '' ?>">
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <header class="site-header">
        <nav class="main-nav" role="navigation" aria-label="Main navigation">
            <div class="nav-brand">
                <a href="/" class="brand-link">
                    <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Where's My Pie Logo" class="logo" onerror="this.style.display='none'">
                    <span class="brand-text">Where's My Pie?</span>
                </a>
            </div>
            
            <!-- Mobile menu toggle -->
            <button class="nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="hamburger"></span>
                <span class="hamburger"></span>
                <span class="hamburger"></span>
            </button>
            
            <div class="nav-links">
                <a href="<?= BASE_URL ?>/items/index.php">Lost Items</a>
                <a href="<?= BASE_URL ?>/items/search.php">Search</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>/items/create.php">Report Found</a>
                    <a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
                    <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
                    <a href="<?= BASE_URL ?>/auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main id="main-content" class="main-content">
        <div class="container">
            <!-- Flash messages -->
            <?php if ($flash): ?>
                <div class="alert alert-<?= sanitize($flash['type']) ?>" role="alert" aria-live="polite">
                    <span class="alert-icon">
                        <?php if ($flash['type'] === 'success'): ?>✓<?php elseif ($flash['type'] === 'error'): ?>✕<?php else: ?>ℹ<?php endif; ?>
                    </span>
                    <span class="alert-message"><?= sanitize($flash['message']) ?></span>
                    <button class="alert-close" aria-label="Close alert">&times;</button>
                </div>
            <?php endif; ?>
            
            <!-- Breadcrumb navigation -->
            <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                <nav class="breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb-list">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>">Home</a>
                        </li>
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <li class="breadcrumb-item <?= isset($crumb['active']) && $crumb['active'] ? 'active' : '' ?>">
                                <?php if (isset($crumb['url']) && !isset($crumb['active'])): ?>
                                    <a href="<?= sanitize($crumb['url']) ?>"><?= sanitize($crumb['title']) ?></a>
                                <?php else: ?>
                                    <?= sanitize($crumb['title']) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>
