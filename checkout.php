<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Checkout | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';

$items = []; $subtotal = 0;
if (!$db_error) {
    foreach (($_SESSION['cart'] ?? []) as $id => $cart_item) {
        $stmt = $conn->prepare("SELECT id,name,price,image,stock FROM products WHERE id=?");
        $pid = (int)$id; $stmt->bind_param('i',$pid); $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($product && (int)$product['stock'] >= 1) {
            $qty = min((int)$cart_item['quantity'], (int)$product['stock']);
            $product['quantity']=$qty; $product['line_total']=$qty*(float)$product['price'];
            $items[]=$product; $subtotal += $product['line_total'];
        }
    }
}
$shipping = ($subtotal > 0 && $subtotal < 3000) ? 150 : 0;
$total = $subtotal + $shipping;
if (!$items) {
    $_SESSION['flash'] = ['type'=>'error','message'=>'Your cart is empty.'];
    redirect($base . '/cart.php');
}

$user = null;
$stmt = $conn->prepare("SELECT name,email,phone FROM users WHERE id=?"); $stmt->bind_param('i',$_SESSION['user_id']); $stmt->execute(); $user=$stmt->get_result()->fetch_assoc(); $stmt->close();

$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $full_name=trim($_POST['full_name']??''); $email=strtolower(trim($_POST['email']??'')); $phone=trim($_POST['phone']??'');
    $address=trim($_POST['address']??''); $city=trim($_POST['city']??''); $postal=trim($_POST['postal_code']??'');
    $method=$_POST['payment_method']??'';
    if (strlen($full_name)<2) $errors[]='Please enter your full name.';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Please enter a valid email.';
    if (!preg_match('/^[0-9+\-\s]{7,20}$/',$phone)) $errors[]='Please enter a valid phone number.';
    if ($address==='' || $city==='' || $postal==='') $errors[]='Please complete the shipping address.';
    if (!in_array($method,['Cash on Delivery','eSewa'],true)) $errors[]='Please select a payment method.';
    if (!$errors) {
        $_SESSION['checkout'] = ['full_name'=>$full_name,'email'=>$email,'phone'=>$phone,'address'=>$address,'city'=>$city,'postal_code'=>$postal,'payment_method'=>$method,'total'=>$total];
        if ($method==='Cash on Delivery') redirect($base.'/payment.php?mode=cod');
        redirect($base.'/payment.php');
    }
}
?>
<section class="page-banner"><div class="container"><span class="eyebrow">FINAL STEP</span><h1>Checkout</h1><p>Enter delivery details and choose your payment method.</p></div></section>
<section class="section container">
<?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" class="checkout-layout" data-validate="checkout">
<div class="panel"><h2>Customer Information</h2><div class="form-grid">
<label>Full Name<input name="full_name" required value="<?= e($_POST['full_name']??$user['name']) ?>"></label>
<label>Email<input type="email" name="email" required value="<?= e($_POST['email']??$user['email']) ?>"></label>
<label>Phone Number<input name="phone" required value="<?= e($_POST['phone']??$user['phone']) ?>"></label>
<label>City<input name="city" required value="<?= e($_POST['city']??'Kathmandu') ?>"></label>
<label class="full">Address<textarea name="address" required rows="3" placeholder="Street, area and landmark"><?= e($_POST['address']??'') ?></textarea></label>
<label>Postal Code<input name="postal_code" required value="<?= e($_POST['postal_code']??'44600') ?>"></label>
</div><h2 class="subheading">Payment Method</h2>
<div class="payment-options">
<label class="payment-option"><input type="radio" name="payment_method" value="Cash on Delivery" <?= (($_POST['payment_method']??'')==='Cash on Delivery')?'checked':'' ?>><span>💵 <strong>Cash on Delivery</strong><small>Pay when your order arrives.</small></span></label>
<label class="payment-option"><input type="radio" name="payment_method" value="eSewa" <?= (($_POST['payment_method']??'')==='eSewa')?'checked':'' ?>><span>📱 <strong>eSewa</strong><small>Pay using eSewa mobile payment.</small></span></label>
</div>
<button class="btn btn-primary btn-large" type="submit">Continue to Payment</button>
</div>
<aside class="summary-card"><h2>Order Summary</h2><?php foreach($items as $item): ?><div class="checkout-item"><span><?= e($item['name']) ?> × <?= (int)$item['quantity'] ?></span><strong>Rs. <?= number_format($item['line_total'],2) ?></strong></div><?php endforeach; ?><hr><div class="summary-line"><span>Subtotal</span><strong>Rs. <?= number_format($subtotal,2) ?></strong></div><div class="summary-line"><span>Shipping</span><strong><?= $shipping ? 'Rs. '.number_format($shipping,2) : 'Free' ?></strong></div><div class="summary-total"><span>Total</span><strong>Rs. <?= number_format($total,2) ?></strong></div></aside>
</form></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>