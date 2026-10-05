<?php
require_once __DIR__.'/../includes/admin_auth.php';
$page_title='Manage Products | Admin';
require_once __DIR__.'/header.php';
$products=[];
if(!$db_error){$r=$conn->query("SELECT p.*,c.name category_name FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC");while($r&&($row=$r->fetch_assoc()))$products[]=$row;}
?>
<div class="admin-title"><div><span class="eyebrow">CATALOG</span><h1>Product Management</h1><p>CRUD operations for the product catalog.</p></div><a class="btn btn-primary" href="<?= $base ?>/admin/add_product.php">+ Add Product</a></div>
<div class="panel table-wrap"><table><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Rating</th><th>Actions</th></tr></thead><tbody>
<?php foreach($products as $p): ?><tr><td><div class="table-product"><img src="<?= $base ?>/assets/images/<?= ae($p['image']) ?>" alt=""><strong><?= ae($p['name']) ?></strong></div></td><td><?= ae($p['category_name']) ?></td><td>Rs. <?= number_format($p['price'],2) ?></td><td><?= (int)$p['stock'] ?></td><td>★ <?= number_format($p['rating'],1) ?></td><td class="actions"><a class="btn btn-small btn-outline" href="<?= $base ?>/admin/edit_product.php?id=<?= (int)$p['id'] ?>">Edit</a><a class="btn btn-small btn-danger" href="<?= $base ?>/admin/delete_product.php?id=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require_once __DIR__.'/footer.php'; ?>