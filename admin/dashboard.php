<?php
require_once __DIR__.'/../includes/admin_auth.php';
$page_title='Admin Dashboard | Hamro Ecommerce';
require_once __DIR__.'/header.php';
$stats=['users'=>0,'products'=>0,'orders'=>0,'sales'=>0];
if(!$db_error){
 $r=$conn->query("SELECT COUNT(*) c FROM users WHERE role='user'"); $stats['users']=$r?$r->fetch_assoc()['c']:0;
 $r=$conn->query("SELECT COUNT(*) c FROM products"); $stats['products']=$r?$r->fetch_assoc()['c']:0;
 $r=$conn->query("SELECT COUNT(*) c FROM orders"); $stats['orders']=$r?$r->fetch_assoc()['c']:0;
 $r=$conn->query("SELECT COALESCE(SUM(total_amount),0) c FROM orders WHERE payment_status='Successful'"); $stats['sales']=$r?(float)$r->fetch_assoc()['c']:0;
}
?>
<div class="admin-title"><div><span class="eyebrow">CONTROL CENTER</span><h1>Admin Dashboard</h1><p>Welcome, <?= ae($_SESSION['admin_name']) ?>.</p></div><a class="btn btn-primary" href="<?= $base ?>/admin/add_product.php">+ Add Product</a></div>
<div class="stats-grid"><div class="stat-card"><span>👥</span><strong><?= (int)$stats['users'] ?></strong><small>Total Users</small></div><div class="stat-card"><span>📦</span><strong><?= (int)$stats['products'] ?></strong><small>Total Products</small></div><div class="stat-card"><span>🧾</span><strong><?= (int)$stats['orders'] ?></strong><small>Total Orders</small></div><div class="stat-card"><span>💰</span><strong>Rs. <?= number_format($stats['sales'],2) ?></strong><small>Total Sales</small></div></div>
<div class="admin-quick"><a class="category-card" href="<?= $base ?>/admin/products.php">📦<h3>Manage Products</h3><p>Add, edit, delete and update stock.</p></a><a class="category-card" href="<?= $base ?>/admin/orders.php">🧾<h3>Manage Orders</h3><p>Review payments and change status.</p></a><a class="category-card" href="<?= $base ?>/admin/users.php">👥<h3>View Users</h3><p>See registered customer accounts.</p></a></div>
<?php require_once __DIR__.'/footer.php'; ?>