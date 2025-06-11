<?php
// Ensure auth object exists
if (!isset($auth)) {
    require_once __DIR__ . '/auth.php';
    $auth = new Auth($pdo);
}

// Get current year for copyright
$currentYear = date('Y');

// Check if user is logged in safely
$isLoggedIn = isset($auth) && $auth->isLoggedIn();
?>
        </div>  <!-- Close main content container -->
    </main>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <p>&copy; <?= $currentYear ?> <?= APP_NAME ?>. Built By Team TreeNoPie</p>
                    <?php if ($isLoggedIn): ?>
                        <div class="footer-nav">
                            <a href="<?= BASE_URL ?>/user/dashboard.php">My Dashboard</a> |
                            <a href="<?= BASE_URL ?>/items/create.php">Report Found</a> |
                            <a href="<?= BASE_URL ?>/items/search.php">Search Items</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- Base JavaScript -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
    <?php if (isset($pageScripts) && is_array($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?= BASE_URL ?>/assets/js/<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>