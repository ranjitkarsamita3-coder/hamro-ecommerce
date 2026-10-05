<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/esewa.php';

$page_title = 'Payment Successful | Hamro Ecommerce';

$data = null;
$raw = $_GET['data'] ?? '';
if ($raw !== '') {
    $decoded = base64_decode($raw, true);
    if ($decoded !== false) {
        $json = json_decode($decoded, true);
        if (is_array($json)) $data = $json;
    }
}

$pending = $_SESSION['esewa_pending'] ?? null;
$verified = false;
$order = null;
$error_message = 'The eSewa payment could not be verified.';

if (!$db_error && $data && $pending && esewa_verify_response($data)) {
    $uuid = (string)($data['transaction_uuid'] ?? '');
    $amount = (float)($data['total_amount'] ?? 0);
    $product_code = (string)($data['product_code'] ?? '');

    if ($uuid === (string)$pending['transaction_uuid'] && $product_code === ESEWA_PRODUCT_CODE && abs($amount - (float)$pending['amount']) < 0.01 && ($data['status'] ?? '') === 'COMPLETE') {
        $status = esewa_check_status($amount, $uuid);
        if ($status && ($status['status'] ?? '') === 'COMPLETE') {
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id=? AND user_id=? AND transaction_id=? LIMIT 1");
            $stmt->bind_param('iis', $pending['order_id'], $_SESSION['user_id'], $uuid);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($order) {
                $conn->begin_transaction();
                try {
                    if ($order['payment_status'] !== 'Successful') {
                        $items_stmt=$conn->prepare("SELECT product_id,quantity FROM order_items WHERE order_id=?");
                        $items_stmt->bind_param('i',$order['id']); $items_stmt->execute();
                        $items_result=$items_stmt->get_result();
                        $items=[];
                        while($item=$items_result->fetch_assoc()) $items[]=$item;
                        $items_stmt->close();

                        foreach($items as $item){
                            $stock_stmt=$conn->prepare("UPDATE products SET stock=stock-? WHERE id=? AND stock>=?");
                            $stock_stmt->bind_param('iii',$item['quantity'],$item['product_id'],$item['quantity']);
                            $stock_stmt->execute();
                            if ($stock_stmt->affected_rows !== 1) throw new Exception('Product stock is no longer available.');
                            $stock_stmt->close();
                        }

                        $transaction_code = (string)($data['transaction_code'] ?? $uuid);
                        $update=$conn->prepare("UPDATE orders SET payment_status='Successful', order_status='Processing', transaction_id=? WHERE id=?");
                        $update->bind_param('si',$transaction_code,$order['id']);
                        $update->execute(); $update->close();
                    }
                    $conn->commit();
                    $verified = true;
                    $transaction_code = (string)($data['transaction_code'] ?? $uuid);
                    $_SESSION['last_order']=[
                        'order_id'=>$order['id'],
                        'transaction_id'=>$transaction_code,
                        'amount'=>$order['total_amount'],
                        'payment_method'=>'eSewa',
                        'customer_name'=>$_SESSION['checkout']['full_name'] ?? 'Customer',
                        'payment_status'=>'Successful'
                    ];
                    unset($_SESSION['cart'], $_SESSION['checkout'], $_SESSION['esewa_pending']);
                } catch (Throwable $e) {
                    $conn->rollback();
                    $error_message = 'Payment was received, but the order could not be finalized. Please contact the administrator.';
                }
            }
        } else {
            $error_message = 'eSewa returned a payment that could not be confirmed by the transaction status check.';
        }
    } else {
        $error_message = 'The eSewa response did not match the order amount or transaction reference.';
    }
}

if (!$verified) {
    $_SESSION['payment_error'] = $error_message;
    redirect('/hamro_ecommerce/payment_failed.php?reason=verification');
}
require_once __DIR__ . '/includes/header.php';
$data = $_SESSION['last_order'];
?>
<section class="section container result-section">
    <div class="success-card">
        <div class="result-icon">✓</div><span class="eyebrow">TRANSACTION COMPLETE</span><h1>🎉 Payment Successful!</h1><p>Your eSewa payment and order were verified successfully.</p>
        <div class="receipt-grid">
            <div><span>Transaction ID</span><strong><?= e($data['transaction_id']) ?></strong></div>
            <div><span>Order ID</span><strong>#HAM-<?= str_pad((int)$data['order_id'],5,'0',STR_PAD_LEFT) ?></strong></div>
            <div><span>Customer</span><strong><?= e($data['customer_name']) ?></strong></div>
            <div><span>Amount Paid</span><strong>Rs. <?= number_format($data['amount'],2) ?></strong></div>
            <div><span>Payment Method</span><strong>eSewa</strong></div>
            <div><span>Date</span><strong><?= e(date('d M Y, h:i A')) ?></strong></div>
            <div><span>Payment Status</span><strong class="success-text">Successful</strong></div>
            <div><span>Order Status</span><strong>Processing</strong></div>
        </div>
        <div class="hero-actions"><a class="btn btn-primary" href="<?= $base ?>/order_details.php?id=<?= (int)$data['order_id'] ?>">View Order</a><a class="btn btn-outline" href="<?= $base ?>/products.php">Continue Shopping</a></div>
    </div>
</section>
<?php unset($_SESSION['last_order']); require_once __DIR__ . '/includes/footer.php'; ?>
