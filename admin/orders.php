<?php
require_once __DIR__.'/../includes/admin_auth.php';
$page_title='Manage Orders | Admin';
require_once __DIR__.'/header.php';
$orders=[];
if(!$db_error){$r=$conn->query("SELECT o.*,u.name customer_name,u.email FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC");while($r&&($row=$r->fetch_assoc()))$orders[]=$row;}
if($_SERVER['REQUEST_METHOD']==='POST'&&!$db_error){
 $id=(int)$_POST['order_id'];$status=$_POST['order_status']??'Processing';
 if(in_array($status,['Pending','Processing','Shipped','Delivered','Cancelled'],true)){$stmt=$conn->prepare("UPDATE orders SET order_status=? WHERE id=?");$stmt->bind_param('si',$status,$id);$stmt->execute();$stmt->close();}
 header('Location: '.$base.'/admin/orders.php');exit;
}
?>
<div class="admin-title"><div><span class="eyebrow">FULFILLMENT</span><h1>Order Management</h1><p>View orders, payments and transaction IDs.</p></div></div>
<div class="panel table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Transaction</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($orders as $o): ?><tr><td><a href="<?= $base ?>/admin/order_details.php?id=<?= (int)$o['id'] ?>">#HAM-<?= str_pad((int)$o['id'],5,'0',STR_PAD_LEFT) ?></a><br><small><?= ae(date('d M Y',strtotime($o['created_at']))) ?></small></td><td><?= ae($o['customer_name']) ?><br><small><?= ae($o['email']) ?></small></td><td>Rs. <?= number_format($o['total_amount'],2) ?></td><td><?= ae($o['payment_status']) ?><br><small><?= ae($o['payment_method']) ?></small></td><td class="wrap"><?= ae($o['transaction_id']?:'—') ?></td><td><span class="status <?= strtolower(ae($o['order_status'])) ?>"><?= ae($o['order_status']) ?></span></td><td><form method="post" class="inline-form"><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>"><select name="order_status"><?php foreach(['Pending','Processing','Shipped','Delivered','Cancelled'] as $s): ?><option <?= $o['order_status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select><button class="btn btn-small btn-primary">Update</button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require_once __DIR__.'/footer.php'; ?>