<?php
$page_title = 'Products | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$category = (int)($_GET['category'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';

$allowed_sorts = [
    'newest' => 'p.id DESC',
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'rating' => 'p.rating DESC'
];
$order_by = $allowed_sorts[$sort] ?? $allowed_sorts['newest'];

$products = [];
if (!$db_error) {
    $sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE 1=1";
    $types = '';
    $params = [];
    if ($q !== '') {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $like = "%{$q}%";
        $types .= 'ss';
        $params[] = $like; $params[] = $like;
    }
    if ($category > 0) {
        $sql .= " AND p.category_id = ?";
        $types .= 'i';
        $params[] = $category;
    }
    $sql .= " ORDER BY $order_by";
    $stmt = $conn->prepare($sql);
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $products[] = $row;
    $stmt->close();
}
$categories = [];
if (!$db_error) {
    $result = $conn->query("SELECT * FROM categories ORDER BY name");
    while ($result && ($row = $result->fetch_assoc())) $categories[] = $row;
}
?>
<section class="page-banner">
    <div class="container">
        <span class="eyebrow">OUR CATALOG</span>
        <h1>Products</h1>
        <p>Search, filter and sort products loaded directly from MySQL.</p>
    </div>
</section>
<section class="section container">
    <form class="filter-bar" method="get">
        <div class="search-field"><span>⌕</span><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search products..."></div>
        <select name="category">
            <option value="0">All categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $category === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="sort">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
        </select>
        <button class="btn btn-primary" type="submit">Apply</button>
    </form>

    <div class="results-row"><strong><?= count($products) ?> product(s)</strong><?php if ($q): ?><span>for “<?= e($q) ?>”</span><?php endif; ?></div>

    <?php if (!$products): ?>
        <div class="empty-state"><div>🔎</div><h2>No products found</h2><p>Try a different search or category.</p><a class="btn btn-primary" href="<?= $base ?>/products.php">Clear Filters</a></div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <div class="product-image-wrap">
                        <img src="<?= $base ?>/assets/images/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                        <span class="stock-pill <?= ((int)$product['stock'] < 1) ? 'out' : '' ?>"><?= ((int)$product['stock'] < 1) ? 'Out of stock' : e($product['stock']) . ' left' ?></span>
                    </div>
                    <div class="product-card-body">
                        <span class="category-label"><?= e($product['category_name']) ?></span>
                        <h3><?= e($product['name']) ?></h3>
                        <p><?= e(mb_strimwidth($product['description'], 0, 92, '...')) ?></p>
                        <div class="product-bottom"><strong>Rs. <?= number_format($product['price'], 2) ?></strong><span class="rating">★ <?= number_format($product['rating'], 1) ?></span></div>
                        <div class="card-actions">
                            <a class="btn btn-small btn-outline" href="<?= $base ?>/product_details.php?id=<?= (int)$product['id'] ?>">View Details</a>
                            <form method="post" action="<?= $base ?>/cart.php">
                                <input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input type="hidden" name="quantity" value="1">
                                <button class="btn btn-small btn-primary" type="submit" <?= ((int)$product['stock'] < 1) ? 'disabled' : '' ?>>Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>