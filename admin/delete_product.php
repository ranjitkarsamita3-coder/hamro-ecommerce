<?php
require_once __DIR__.'/../includes/admin_auth.php';
$id=(int)($_GET['id']??0);
if($id>0&&!$db_error){
 $stmt=$conn->prepare("DELETE FROM products WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();$stmt->close();
}
header('Location: /hamro_ecommerce/admin/products.php');exit;
?>