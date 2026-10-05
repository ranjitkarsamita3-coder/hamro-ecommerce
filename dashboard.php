<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Dashboard | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';

$user = null; $orders = [];
if (!$db_error) {
    $stmt = $conn->prepare("SELECT id,name,email,phone,created_at FROM users WHERE id=?");
    $stmt->bind_param('i', $_SESSION['user_id']); $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc(); $stmt->close();

    $stmt = $conn->prepare("SELECT id,total_amount,payment_method,payment_status,order_status,transaction_id,created_at FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 8");
    $stmt->bind_param('i', $_SESSION['user_id']); $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $orders[] = $row;
    $stmt->close();
}
?>
<section class="page-banner"><div class="container"><span class="eyebrow">ACCOUNT</span><h1>Welcome, <?= e($_SESSION['user_name']) ?> 👋</h1><p>Your profile and recent orders.</p></div></section>
<section class="section container">
    <div class="dashboard-grid">
        <div class="profile-card"><div class="avatar"><?= e(strtoupper(substr($_SESSION['user_name'],0,1))) ?></div><h2><?= e($user['name'] ?? '') ?></h2><p><?= e($user['email'] ?? '') ?></p><p><?= e($user['phone'] ?? '') ?></p><small>Member since <?= e(date('M Y', strtotime($user['created_at'] ?? 'now'))) ?></small></div>
        <div class="panel"><div class="section-heading compact"><h2>Recent Orders</h2><a href="<?= $base ?>/orders.php" class="text-link">View all →</a></div>
            <?php if (!$orders): ?><div class="empty-mini">No orders yet. <a href="<?= $base ?>/products.php">Start shopping</a>.</div><?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Order</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead><tbody>
            <?php foreach ($orders as $order): ?><tr><td><a href="<?= $base ?>/order_details.php?id=<?= (int)$order['id'] ?>">#HAM-<?= str_pad((int)$order['id'],5,'0',STR_PAD_LEFT) ?></a></td><td>Rs. <?= number_format($order['total_amount'],2) ?></td><td><?= e($order['payment_status']) ?></td><td><span class="status <?= strtolower(e($order['order_status'])) ?>"><?= e($order['order_status']) ?></span></td><td><?= e(date('d M Y', strtotime($order['created_at']))) ?></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>