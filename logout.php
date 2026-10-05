<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /hamro_ecommerce/login.php');
exit;
?>