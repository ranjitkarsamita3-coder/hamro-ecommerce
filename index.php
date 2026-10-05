<?php
$page_title = 'Home | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';

$featured = [];
if (!$db_error) {
    $result = $conn->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.rating DESC, p.id DESC LIMIT 8");
    if ($result) {
        while ($row = $result->fetch_assoc()) $featured[] = $row;
    }
}
$categories = [];
if (!$db_error) {
    $result = $conn->query("SELECT * FROM categories ORDER BY name");
    if ($result) {
        while ($row = $result->fetch_assoc()) $categories[] = $row;
    }
}
?>
<section class="hero">
    <div class="container hero-content">
        <div>
            <span class="eyebrow">NEPAL'S SIMPLE ONLINE STORE</span>
            <h1>Shop smart. Shop local. Shop <span>Hamro.</span></h1>
            <p>Explore everyday electronics, fashion and accessories with a clean, student-friendly e-commerce experience.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= $base ?>/products.php">Shop Products</a>
                <a class="btn btn-outline" href="<?= $base ?>/register.php">Create Account</a>
            </div>
        </div>
    </div>
</section>

<section class="section container">
    <div class="section-heading">
        <div>
            <span class="eyebrow">CURATED FOR YOU</span>
            <h2>Featured Products</h2>
        </div>
        <a href="<?= $base ?>/products.php" class="text-link">View all →</a>
    </div>
    <div class="product-grid">
        <?php foreach ($featured as $product): ?>
            <article class="product-card">
                <div class="product-image-wrap">
                    <img src="<?= $base ?>/assets/images/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                    <?php if ((int)$product['stock'] > 0): ?><span class="stock-pill">In stock</span><?php endif; ?>
                </div>
                <div class="product-card-body">
                    <span class="category-label"><?= e($product['category_name']) ?></span>
                    <h3><?= e($product['name']) ?></h3>
                    <p><?= e(mb_strimwidth($product['description'], 0, 78, '...')) ?></p>
                    <div class="product-bottom">
                        <strong>Rs. <?= number_format($product['price'], 2) ?></strong>
                        <span class="rating">★ <?= number_format($product['rating'], 1) ?></span>
                    </div>
                    <div class="card-actions">
                        <a class="btn btn-small btn-outline" href="<?= $base ?>/product_details.php?id=<?= (int)$product['id'] ?>">Details</a>
                        <form method="post" action="<?= $base ?>/cart.php">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button class="btn btn-small btn-primary" type="submit" <?= ((int)$product['stock'] < 1) ? 'disabled' : '' ?>>Add to Cart</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section soft-section" id="categories">
    <div class="container">
        <div class="section-heading">
            <div>
                <span class="eyebrow">BROWSE EASILY</span>
                <h2>Popular Categories</h2>
            </div>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <a class="category-card" href="<?= $base ?>/products.php?category=<?= (int)$category['id'] ?>">
                    <span class="category-icon"><?= $category['name'] === 'Electronics' ? '💻' : ($category['name'] === 'Fashion' ? '👟' : '🎒') ?></span>
                    <h3><?= e($category['name']) ?></h3>
                    <p><?= e($category['description']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section container feature-strip">
    <div class="feature-item"><span>🔒</span><div><strong>Secure Basics</strong><p>Hashed passwords and prepared SQL.</p></div></div>
    <div class="feature-item"><span>🚚</span><div><strong>Simple Checkout</strong><p>Address, shipping and order summary.</p></div></div>
    <div class="feature-item"><span>📱</span><div><strong>eSewa Payment</strong><p>Mobile payment through eSewa.</p></div></div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>