<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/esewa.php';
$page_title = 'Payment Failed | Hamro Ecommerce';
$reason=$_GET['reason']??'unknown';

if (!$db_error && !empty($_SESSION['esewa_pending']['order_id'])) {
    $order_id=(int)$_SESSION['esewa_pending']['order_id'];
    $stmt=$conn->prepare("UPDATE orders SET payment_status='Failed', order_status='Cancelled' WHERE id=? AND user_id=? AND payment_method='eSewa' AND payment_status='Pending'");
    $stmt->bind_param('ii',$order_id,$_SESSION['user_id']);
    $stmt->execute(); $stmt->close();
    unset($_SESSION['esewa_pending']);
}
$error_message=$_SESSION['payment_error']??'';
unset($_SESSION['payment_error']);
require_once __DIR__ . '/includes/header.php';
?>
<section class="section container result-section"><div class="failure-card">
<div class="result-icon">!</div><span class="eyebrow">eSEWA PAYMENT</span><h1>Payment Failed</h1>
<p><?= e($error_message ?: 'The eSewa payment was cancelled or could not be completed.') ?></p>
<div class="alert error">Please try the eSewa payment again or return to your cart.</div>
<div class="hero-actions"><a class="btn btn-primary" href="<?= $base ?>/payment.php">Try Again</a><a class="btn btn-outline" href="<?= $base ?>/cart.php">Return to Cart</a></div>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
