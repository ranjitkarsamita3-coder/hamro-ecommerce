<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please log in to continue.'];
    header('Location: /hamro_ecommerce/login.php');
    exit;
}
?>