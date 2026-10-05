<?php
require_once __DIR__.'/../includes/admin_auth.php';
$page_title='Order Details | Admin';
require_once __DIR__.'/header.php';
$id=(int)($_GET['id']??0);$order=null;$items=[];
if(!$db_error&&$id){$stmt=$conn->prepare("SELECT o.*,u.name customer_name,u.email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?");$stmt->bind_param('i',$id);$stmt->execute();$order=$stmt->get_result()->fetch_assoc();$stmt->close();if($order){$stmt=$conn->prepare("SELECT oi.*,p.name,p.image FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");$stmt->bind_param('i',$id);$stmt->execute();$r=$stmt->get_result();while($r&&($row=$r->fetch_assoc()))$items[]=$row;$stmt->close();}}
?>
<?php if(!$order): ?><div class="empty-state"><h2>Order not found</h2><a class="btn btn-primary" href="<?= $base ?>/admin/orders.php">Back</a></div><?php else: ?>
<div class="admin-title"><div><span class="eyebrow">ORDER</span><h1>#HAM-<?= str_pad($id,5,'0',STR_PAD_LEFT) ?></h1><p><?= ae($order['customer_name']) ?> · <?= ae($order['email']) ?></p></div><span class="status <?= strtolower(ae($order['order_status'])) ?>"><?= ae($order['order_status']) ?></span></div>
<div class="order-detail-layout"><div class="panel"><h2>Items</h2><?php foreach($items as $i): ?><div class="detail-order-item"><img src="<?= $base ?>/assets/images/<?= ae($i['image']) ?>" alt=""><div><strong><?= ae($i['name']) ?></strong><span>Qty: <?= (int)$i['quantity'] ?></span></div><b>Rs. <?= number_format($i['price']*$i['quantity'],2) ?></b></div><?php endforeach; ?></div><aside class="summary-card"><h2>Order Info</h2><p><strong>Total:</strong> Rs. <?= number_format($order['total_amount'],2) ?></p><p><strong>Payment:</strong> <?= ae($order['payment_method']) ?></p><p><strong>Payment Status:</strong> <?= ae($order['payment_status']) ?></p><p><strong>Transaction:</strong> <span class="wrap"><?= ae($order['transaction_id']?:'—') ?></span></p><hr><p><?= ae($order['shipping_address']) ?>, <?= ae($order['city']) ?> <?= ae($order['postal_code']) ?></p><p>Phone: <?= ae($order['phone']) ?></p></aside></div>
<?php endif; ?>
<?php require_once __DIR__.'/footer.php'; ?>