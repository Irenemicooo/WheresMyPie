<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'auth.php';

// Check if there's a flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Where's My Pie? | Lost and Found System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <nav class="main-nav">
            <div class="nav-brand">
                <a href="<?= BASE_URL ?>">
                    Where's My Pie?
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="<?= BASE_URL ?>/items/">Browse Items</a></li>
                
                <?php if ($auth->isLoggedIn()): ?>
                    <li><a href="<?= BASE_URL ?>/items/create.php">Report Found</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            <?= sanitize($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a></li>
                            <li><a href="<?= BASE_URL ?>/user/profile.php">Profile</a></li>
                            <li><a href="<?= BASE_URL ?>/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/auth/login.php">Login</a></li>
                    <li><a href="<?= BASE_URL ?>/auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>