<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

$base = '/hamro_ecommerce';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Hamro Ecommerce') ?></title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<main class="site-main">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="container">
            <div class="alert <?= e($_SESSION['flash']['type']) ?>">
                <?= e($_SESSION['flash']['message']) ?>
            </div>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
