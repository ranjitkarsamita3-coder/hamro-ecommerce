<?php
$page_title = 'Shopping Cart | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

function get_product($conn, $id) {
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $product;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$db_error) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if (isset($_POST['remove_item'])) {
        $remove_id = (int)$_POST['remove_item'];
        unset($_SESSION['cart'][$remove_id]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Product removed from cart.'];
        redirect($base . '/cart.php');
    }

    if ($action === 'add') {
        $product = get_product($conn, $id);
        if (!$product) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Invalid product selected.'];
        } elseif ((int)$product['stock'] < 1) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'This product is out of stock.'];
        } else {
            $existing = $_SESSION['cart'][$id]['quantity'] ?? 0;
            $new_qty = $existing + $quantity;
            if ($new_qty > (int)$product['stock']) {
                $_SESSION['flash'] = ['type'=>'error','message'=>'Requested quantity exceeds available stock.'];
            } else {
                $_SESSION['cart'][$id] = ['quantity'=>$new_qty];
                $_SESSION['flash'] = ['type'=>'success','message'=>'Product added to cart.'];
            }
        }
        redirect(isset($_POST['buy_now']) ? $base . '/checkout.php' : $base . '/cart.php');
    }

    if ($action === 'update') {
        foreach (($_POST['quantities'] ?? []) as $pid => $qty) {
            $pid = (int)$pid; $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$pid]);
                continue;
            }
            $product = get_product($conn, $pid);
            if ($product) {
                $_SESSION['cart'][$pid]['quantity'] = min($qty, (int)$product['stock']);
            }
        }
        $_SESSION['flash'] = ['type'=>'success','message'=>'Cart updated.'];
        redirect($base . '/cart.php');
    }

    if ($action === 'remove') {
        unset($_SESSION['cart'][$id]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Product removed from cart.'];
        redirect($base . '/cart.php');
    }

}

$items = [];
$subtotal = 0;
if (!$db_error) {
    foreach ($_SESSION['cart'] as $id => $cart_item) {
        $product = get_product($conn, (int)$id);
        if (!$product || (int)$product['stock'] < 1) {
            unset($_SESSION['cart'][$id]);
            continue;
        }
        $qty = min((int)$cart_item['quantity'], (int)$product['stock']);
        $_SESSION['cart'][$id]['quantity'] = $qty;
        $line = $qty * (float)$product['price'];
        $product['quantity'] = $qty; $product['line_total'] = $line;
        $items[] = $product; $subtotal += $line;
    }
}
$shipping = ($subtotal > 0 && $subtotal < 3000) ? 150 : 0;
$total = $subtotal + $shipping;
?>
<section class="page-banner"><div class="container"><span class="eyebrow">YOUR BAG</span><h1>Shopping Cart</h1><p>Review your products before checkout.</p></div></section>
<section class="section container">
<?php if (!$items): ?>
    <div class="empty-state"><div>🛒</div><h2>Your cart is empty</h2><p>Add a few products and they will appear here.</p><a class="btn btn-primary" href="<?= $base ?>/products.php">Start Shopping</a></div>
<?php else: ?>
    <div class="cart-layout">
        <form method="post" class="cart-list">
            <input type="hidden" name="action" value="update">
            <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <img src="<?= $base ?>/assets/images/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>">
                    <div class="cart-item-info"><span class="category-label"><?= e($item['category_name']) ?></span><h3><?= e($item['name']) ?></h3><p>Rs. <?= number_format($item['price'], 2) ?> each</p></div>
                    <input class="quantity-input" type="number" name="quantities[<?= (int)$item['id'] ?>]" min="1" max="<?= (int)$item['stock'] ?>" value="<?= (int)$item['quantity'] ?>">
                    <strong>Rs. <?= number_format($item['line_total'], 2) ?></strong>
                    <button class="icon-btn remove-btn" type="submit" formaction="<?= $base ?>/cart.php" name="remove_item" value="<?= (int)$item['id'] ?>" onclick="return removeCartItem(this)">×</button>
                </div>
            <?php endforeach; ?>
            <button class="btn btn-outline" type="submit">Update Cart</button>
        </form>
        <aside class="summary-card">
            <h2>Order Summary</h2>
            <div class="summary-line"><span>Subtotal</span><strong>Rs. <?= number_format($subtotal, 2) ?></strong></div>
            <div class="summary-line"><span>Shipping</span><strong><?= $shipping ? 'Rs. ' . number_format($shipping, 2) : 'Free' ?></strong></div>
            <hr><div class="summary-total"><span>Grand Total</span><strong>Rs. <?= number_format($total, 2) ?></strong></div>
            <a class="btn btn-primary btn-block" href="<?= $base ?>/checkout.php">Proceed to Checkout</a>
            <a class="btn btn-outline btn-block" href="<?= $base ?>/products.php">Continue Shopping</a>
        </aside>
    </div>
<?php endif; ?>
</section>
<script>
function removeCartItem(button) {
    const form = button.closest('form');
    const hidden = document.createElement('input');
    hidden.type = 'hidden'; hidden.name = 'action'; hidden.value = 'remove';
    form.appendChild(hidden);
    const id = button.value;
    const pid = document.createElement('input');
    pid.type = 'hidden'; pid.name = 'product_id'; pid.value = id;
    form.appendChild(pid);
    return true;
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>