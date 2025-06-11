<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if session not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get statistics using the global $pdo
$stats = [
    'total_items' => $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn(),
    'claimed_items' => $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'claimed'")->fetchColumn(),
    'active_users' => $pdo->query("SELECT COUNT(DISTINCT user_id) FROM items")->fetchColumn()
];

// Get categories
$stmt = $pdo->query("SELECT DISTINCT category FROM items ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get recent items
$stmt = $pdo->prepare("
    SELECT i.*, u.username 
    FROM items i 
    JOIN users u ON i.user_id = u.user_id 
    WHERE i.status = 'available' 
    AND NOT EXISTS (
        SELECT 1 FROM claims c 
        WHERE c.item_id = i.item_id 
        AND c.status = 'approved'
    )
    ORDER BY i.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$recentItems = $stmt->fetchAll();

$pageTitle = APP_NAME;
include 'includes/header.php';
?>

<div class="container">
    <div class="hero-section">
        <h1>Where's My Pie?</h1>
        <p class="hero-subtitle">Find Your Lost Items in Our Community</p>
        
        <div class="search-box">
            <form action="items/search.php" method="get" class="advanced-search">
                <div class="search-row">
                    <input type="text" name="keyword" placeholder="Search lost items...">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>">
                                <?= htmlspecialchars($category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="stats-section">
        <div class="stat-card">
            <h3><?= $stats['total_items'] ?></h3>
            <p>Items Posted</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['claimed_items'] ?></h3>
            <p>Items Claimed</p>
        </div>
        <div class="stat-card">
            <h3><?= $stats['active_users'] ?></h3>
            <p>Active Users</p>
        </div>
    </div>

    <section class="recent-items">
        <h2>Recently Found Items</h2>
        <div class="items-grid">
            <?php foreach ($recentItems as $item): ?>
                <div class="item-card">
                    <?php if ($item['photo_path']): ?>
                        <img src="/<?= htmlspecialchars($item['photo_path']) ?>" 
                             alt="<?= htmlspecialchars($item['title']) ?>"
                             style="max-width: 150px; max-height: 150px; object-fit: cover;">
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                    <p>Found at: <?= htmlspecialchars($item['location']) ?></p>
                    <p>Category: <?= htmlspecialchars($item['category']) ?></p>
                    <a href="items/view.php?id=<?= $item['item_id'] ?>" 
                       class="btn btn-primary">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="view-all">
            <a href="items/search.php" class="btn btn-secondary">View All Items</a>
        </div>
    </section>

    <section class="cta-section">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <h2>Join Our Community</h2>
            <p>Help others find their lost belongings</p>
            <div class="cta-buttons">
                <a href="auth/register.php" class="btn btn-primary">Sign Up Now</a>
                <a href="auth/login.php" class="btn btn-secondary">Login</a>
            </div>
        <?php else: ?>
            <h2>Found Something?</h2>
            <p>Help others by reporting what you found</p>
            <a href="items/create.php" class="btn btn-primary">Report Found Item</a>
        <?php endif; ?>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
