<?php
require_once __DIR__.'/../includes/admin_auth.php';
$page_title='Users | Admin';
require_once __DIR__.'/header.php';
$users=[];
if(!$db_error){$r=$conn->query("SELECT id,name,email,phone,role,created_at FROM users ORDER BY id DESC");while($r&&($row=$r->fetch_assoc()))$users[]=$row;}
?>
<div class="admin-title"><div><span class="eyebrow">CUSTOMERS</span><h1>Users</h1><p>Registered user accounts.</p></div></div>
<div class="panel table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><?= ae($u['name']) ?></td><td><?= ae($u['email']) ?></td><td><?= ae($u['phone']) ?></td><td><span class="status"><?= ae($u['role']) ?></span></td><td><?= ae(date('d M Y',strtotime($u['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php require_once __DIR__.'/footer.php'; ?>