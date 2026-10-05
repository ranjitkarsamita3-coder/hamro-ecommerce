<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'My Orders | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';
$orders = [];
if (!$db_error) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC");
    $stmt->bind_param('i', $_SESSION['user_id']); $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $orders[] = $row;
    $stmt->close();
}
?>
<section class="page-banner"><div class="container"><span class="eyebrow">ORDER HISTORY</span><h1>My Orders</h1><p>Track your purchases, payment and delivery status.</p></div></section>
<section class="section container">
<?php if (!$orders): ?><div class="empty-state"><div>🧾</div><h2>No orders yet</h2><p>Your completed purchases will appear here.</p><a class="btn btn-primary" href="<?= $base ?>/products.php">Browse Products</a></div>
<?php else: ?>
<div class="orders-grid"><?php foreach ($orders as $order): ?>
<article class="order-card"><div class="order-card-head"><div><span class="category-label">Order ID</span><h3>#HAM-<?= str_pad((int)$order['id'],5,'0',STR_PAD_LEFT) ?></h3></div><span class="status <?= strtolower(e($order['order_status'])) ?>"><?= e($order['order_status']) ?></span></div>
<div class="order-details-grid"><div><span>Amount</span><strong>Rs. <?= number_format($order['total_amount'],2) ?></strong></div><div><span>Payment</span><strong><?= e($order['payment_method']) ?></strong></div><div><span>Payment Status</span><strong><?= e($order['payment_status']) ?></strong></div><div><span>Date</span><strong><?= e(date('d M Y',strtotime($order['created_at']))) ?></strong></div></div>
<?php if ($order['transaction_id']): ?><p class="transaction">Transaction: <?= e($order['transaction_id']) ?></p><?php endif; ?>
<a class="btn btn-small btn-outline" href="<?= $base ?>/order_details.php?id=<?= (int)$order['id'] ?>">View Order Details</a></article>
<?php endforeach; ?></div><?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>