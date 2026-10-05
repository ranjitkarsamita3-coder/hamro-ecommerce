<?php
$page_title = 'Product Details | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$product = null;
if (!$db_error && $id > 0) {
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<section class="section container">
<?php if (!$product): ?>
    <div class="empty-state"><div>📦</div><h2>Product not found</h2><p>The requested product does not exist.</p><a class="btn btn-primary" href="<?= $base ?>/products.php">Back to Products</a></div>
<?php else: ?>
    <div class="detail-layout">
        <div class="detail-image"><img src="<?= $base ?>/assets/images/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>"></div>
        <div class="detail-content">
            <span class="category-label"><?= e($product['category_name']) ?></span>
            <h1><?= e($product['name']) ?></h1>
            <div class="detail-rating">★ <?= number_format($product['rating'], 1) ?> <span>Customer rating</span></div>
            <div class="detail-price">Rs. <?= number_format($product['price'], 2) ?></div>
            <p class="lead"><?= e($product['description']) ?></p>
            <div class="detail-meta"><span>📦 Stock: <strong><?= (int)$product['stock'] ?></strong></span><span>🏷️ <?= e($product['category_name']) ?></span></div>
            <?php if ((int)$product['stock'] > 0): ?>
                <form method="post" action="<?= $base ?>/cart.php" class="buy-form">
                    <input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <label>Quantity <input class="quantity-input" type="number" name="quantity" min="1" max="<?= (int)$product['stock'] ?>" value="1"></label>
                    <button class="btn btn-primary btn-large" type="submit">Add to Cart</button>
                    <button class="btn btn-outline btn-large" type="submit" name="buy_now" value="1">Buy Now</button>
                </form>
            <?php else: ?>
                <div class="alert error">This product is currently out of stock.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>