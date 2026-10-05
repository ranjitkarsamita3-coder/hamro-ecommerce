<?php
require_once __DIR__.'/../includes/admin_auth.php';
$page_title='Edit Product | Admin';
require_once __DIR__.'/header.php';
$id=(int)($_GET['id']??0);$errors=[];$product=null;$categories=[];
if(!$db_error){
 $stmt=$conn->prepare("SELECT * FROM products WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();$product=$stmt->get_result()->fetch_assoc();$stmt->close();
 $r=$conn->query("SELECT * FROM categories ORDER BY name");while($r&&($row=$r->fetch_assoc()))$categories[]=$row;
}
if(!$product){echo '<div class="empty-state"><h2>Product not found</h2></div>';require_once __DIR__.'/footer.php';exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&!$db_error){
 $name=trim($_POST['name']??'');$description=trim($_POST['description']??'');$price=(float)$_POST['price'];$category=(int)$_POST['category_id'];$stock=(int)$_POST['stock'];$rating=(float)$_POST['rating'];$image=$product['image'];
 if($name===''||$description==='')$errors[]='Name and description are required.';
 if($price<=0)$errors[]='Price must be greater than zero.';if($category<=0)$errors[]='Select a category.';if($stock<0)$errors[]='Stock cannot be negative.';if($rating<0||$rating>5)$errors[]='Rating must be between 0 and 5.';
 if(!empty($_FILES['image']['name'])&&$_FILES['image']['error']===UPLOAD_ERR_OK){$ext=strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));if(in_array($ext,['jpg','jpeg','png','webp','svg'],true)&&$_FILES['image']['size']<=2*1024*1024){$safe='product_'.time().'_'.bin2hex(random_bytes(3)).'.'.$ext;move_uploaded_file($_FILES['image']['tmp_name'],__DIR__.'/../assets/images/'.$safe);$image=$safe;}else$errors[]='Invalid image file.';}
 if(!$errors){$stmt=$conn->prepare("UPDATE products SET category_id=?,name=?,description=?,price=?,image=?,stock=?,rating=? WHERE id=?");$stmt->bind_param('issdsidi',$category,$name,$description,$price,$image,$stock,$rating,$id);if($stmt->execute()){header('Location: '.$base.'/admin/products.php');exit;}$stmt->close();$errors[]='Could not update product.';}
}
?>
<div class="admin-title"><div><span class="eyebrow">CATALOG</span><h1>Edit Product</h1></div></div>
<?php foreach($errors as $e1): ?><div class="alert error"><?= ae($e1) ?></div><?php endforeach; ?>
<div class="panel"><form method="post" enctype="multipart/form-data" class="form-grid">
<label>Product Name<input name="name" required value="<?= ae($_POST['name']??$product['name']) ?>"></label>
<label>Category<select name="category_id" required><?php foreach($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= ((int)($product['category_id'])===(int)$c['id'])?'selected':'' ?>><?= ae($c['name']) ?></option><?php endforeach; ?></select></label>
<label>Price<input type="number" step="0.01" name="price" required value="<?= ae($_POST['price']??$product['price']) ?>"></label>
<label>Stock<input type="number" min="0" name="stock" required value="<?= ae($_POST['stock']??$product['stock']) ?>"></label>
<label>Rating<input type="number" min="0" max="5" step="0.1" name="rating" value="<?= ae($_POST['rating']??$product['rating']) ?>"></label>
<label>Replace Image<input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.svg"></label>
<label class="full">Description<textarea name="description" rows="5" required><?= ae($_POST['description']??$product['description']) ?></textarea></label>
<div class="full hero-actions"><button class="btn btn-primary">Update Product</button><a class="btn btn-outline" href="<?= $base ?>/admin/products.php">Cancel</a></div>
</form></div>
<?php require_once __DIR__.'/footer.php'; ?>