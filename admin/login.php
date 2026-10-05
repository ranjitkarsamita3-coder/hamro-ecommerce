<?php
session_start();
require_once __DIR__ . '/../config/database.php';
$base='/hamro_ecommerce';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST' && !$db_error) {
    $email=strtolower(trim($_POST['email']??'')); $password=$_POST['password']??'';
    $stmt=$conn->prepare("SELECT id,name,email,password,role FROM users WHERE email=? AND role='admin'");
    $stmt->bind_param('s',$email); $stmt->execute(); $admin=$stmt->get_result()->fetch_assoc(); $stmt->close();
    if($admin && password_verify($password,$admin['password'])){
        session_regenerate_id(true); $_SESSION['admin_id']=(int)$admin['id']; $_SESSION['admin_name']=$admin['name']; $_SESSION['admin_role']=$admin['role'];
        header('Location: '.$base.'/admin/dashboard.php'); exit;
    }
    $error='Invalid admin credentials.';
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login | Hamro Ecommerce</title><link rel="stylesheet" href="<?= $base ?>/assets/css/style.css"></head><body class="admin-body">
<div class="auth-card admin-login"><div class="auth-heading"><span class="eyebrow">ADMIN PANEL</span><h1>Admin Login</h1><p>Authorized users only.</p></div>
<?php if($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post"><label>Email<input type="email" name="email" required placeholder="admin@hamro.com"></label><label>Password<input type="password" name="password" required></label><button class="btn btn-primary btn-block">Login</button></form>
<p class="auth-footer"><a href="<?= $base ?>/index.php">← Back to store</a></p></div></body></html>