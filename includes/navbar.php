<?php
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += (int)$item['quantity'];
    }
}
?>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= $base ?>/index.php">
            <span class="brand-mark">H</span>
            <span>Hamro <strong>Ecommerce</strong></span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Toggle navigation">☰</button>
        <nav class="main-nav">
            <a href="<?= $base ?>/index.php">Home</a>
            <a href="<?= $base ?>/products.php">Products</a>
            <a href="<?= $base ?>/products.php#categories">Categories</a>
            <a href="<?= $base ?>/cart.php">Cart <span class="cart-badge"><?= $cart_count ?></span></a>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="<?= $base ?>/dashboard.php">Hi, <?= e($_SESSION['user_name']) ?></a>
                <a href="<?= $base ?>/logout.php">Logout</a>
            <?php else: ?>
                <a href="<?= $base ?>/login.php">Login</a>
                <a class="nav-cta" href="<?= $base ?>/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
