<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Order Details | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$order = null; $items = [];
if (!$db_error && $id > 0) {
    $stmt = $conn->prepare("SELECT o.*, u.name AS customer_name, u.email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=? AND o.user_id=?");
    $stmt->bind_param('ii', $id, $_SESSION['user_id']); $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if ($order) {
        $stmt = $conn->prepare("SELECT oi.quantity,oi.price,p.name,p.image FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");
        $stmt->bind_param('i', $id); $stmt->execute(); $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) $items[] = $row;
        $stmt->close();
    }
}
?>
<section class="section container">
<?php if (!$order): ?><div class="empty-state"><div>🔎</div><h2>Order not found</h2><a class="btn btn-primary" href="<?= $base ?>/orders.php">Back to Orders</a></div>
<?php else: ?>
<div class="section-heading"><div><span class="eyebrow">ORDER DETAILS</span><h1>#HAM-<?= str_pad((int)$order['id'],5,'0',STR_PAD_LEFT) ?></h1></div><span class="status <?= strtolower(e($order['order_status'])) ?>"><?= e($order['order_status']) ?></span></div>
<div class="order-detail-layout"><div class="panel"><h2>Items</h2><?php foreach ($items as $item): ?><div class="detail-order-item"><img src="<?= $base ?>/assets/images/<?= e($item['image']) ?>" alt=""><div><strong><?= e($item['name']) ?></strong><span>Qty: <?= (int)$item['quantity'] ?> × Rs. <?= number_format($item['price'],2) ?></span></div><b>Rs. <?= number_format($item['quantity']*$item['price'],2) ?></b></div><?php endforeach; ?></div>
<aside class="summary-card"><h2>Summary</h2><div class="summary-line"><span>Total</span><strong>Rs. <?= number_format($order['total_amount'],2) ?></strong></div><div class="summary-line"><span>Payment</span><strong><?= e($order['payment_method']) ?></strong></div><div class="summary-line"><span>Payment Status</span><strong><?= e($order['payment_status']) ?></strong></div><div class="summary-line"><span>Transaction</span><strong class="wrap"><?= e($order['transaction_id'] ?: 'Not generated') ?></strong></div><hr><p><?= e($order['shipping_address']) ?>, <?= e($order['city']) ?> <?= e($order['postal_code']) ?></p><p>Phone: <?= e($order['phone']) ?></p></aside></div>
<?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>