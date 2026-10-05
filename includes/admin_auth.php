<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id']) || ($_SESSION['admin_role'] ?? '') !== 'admin') {
    header('Location: /hamro_ecommerce/admin/login.php');
    exit;
}
?>