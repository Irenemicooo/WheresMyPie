<?php
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Where's My Pie?</h3>
                    <p>A community-based lost and found platform</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?= BASE_URL ?>">Home</a></li>
                        <li><a href="<?= BASE_URL ?>/items/">Browse Items</a></li>
                        <?php if ($auth->isLoggedIn()): ?>
                            <li><a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="<?= BASE_URL ?>/auth/login.php">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>If you need assistance, please contact the administrator.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Where's My Pie? | Created by TreeNoPie Team</p>
            </div>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>