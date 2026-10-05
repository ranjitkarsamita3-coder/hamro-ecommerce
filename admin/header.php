<?php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/../config/database.php';
$base='/hamro_ecommerce';
function ae($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= ae($page_title??'Admin') ?></title><link rel="stylesheet" href="<?= $base ?>/assets/css/style.css"></head><body class="admin-body">
<header class="admin-header"><div class="container nav-wrap"><a class="brand" href="<?= $base ?>/admin/dashboard.php"><span class="brand-mark">H</span><span>Hamro <strong>Admin</strong></span></a><nav class="main-nav"><a href="<?= $base ?>/admin/dashboard.php">Dashboard</a><a href="<?= $base ?>/admin/products.php">Products</a><a href="<?= $base ?>/admin/orders.php">Orders</a><a href="<?= $base ?>/admin/users.php">Users</a><a href="<?= $base ?>/admin/logout.php">Logout</a></nav></div></header>
<main class="site-main"><div class="container">
