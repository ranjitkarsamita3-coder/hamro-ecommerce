<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/esewa.php';

$page_title = 'Payment | Hamro Ecommerce';

$checkout = $_SESSION['checkout'] ?? null;

if (!$checkout) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Checkout session expired.'
    ];
    redirect('/hamro_ecommerce/cart.php');
}

$mode = $_GET['mode'] ?? '';
$errors = [];


/*
|--------------------------------------------------------------------------
| CASH ON DELIVERY ORDER
|--------------------------------------------------------------------------
*/

function create_cod_order($conn, $checkout, $transaction_id)
{
    $cart = $_SESSION['cart'] ?? [];

    if (!$cart) {
        return false;
    }

    $conn->begin_transaction();

    try {

        $items = [];
        $subtotal = 0;

        foreach ($cart as $id => $ci) {

            $pid = (int)$id;
            $qty = (int)$ci['quantity'];

            if ($qty < 1) {
                throw new Exception('Invalid quantity.');
            }

            $stmt = $conn->prepare(
                "SELECT id, name, price, stock
                 FROM products
                 WHERE id = ?
                 FOR UPDATE"
            );

            $stmt->bind_param('i', $pid);
            $stmt->execute();

            $product = $stmt->get_result()->fetch_assoc();

            $stmt->close();

            if (!$product) {
                throw new Exception('Product not found.');
            }

            if ((int)$product['stock'] < $qty) {
                throw new Exception('Product stock changed.');
            }

            $price = (float)$product['price'];

            $items[] = [
                'id' => $pid,
                'quantity' => $qty,
                'price' => $price
            ];

            $subtotal += $qty * $price;
        }

        $shipping = ($subtotal > 0 && $subtotal < 3000) ? 150 : 0;
        $total = $subtotal + $shipping;

        $payment_status = 'Pending';
        $order_status = 'Processing';
        $method = 'Cash on Delivery';

        $stmt = $conn->prepare(
            "INSERT INTO orders
            (
                user_id,
                total_amount,
                shipping_address,
                city,
                postal_code,
                phone,
                payment_method,
                payment_status,
                order_status,
                transaction_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            'idssssssss',
            $_SESSION['user_id'],
            $total,
            $checkout['address'],
            $checkout['city'],
            $checkout['postal_code'],
            $checkout['phone'],
            $method,
            $payment_status,
            $order_status,
            $transaction_id
        );

        if (!$stmt->execute()) {
            throw new Exception('Order creation failed.');
        }

        $order_id = $conn->insert_id;

        $stmt->close();

        $item_stmt = $conn->prepare(
            "INSERT INTO order_items
            (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)"
        );

        $stock_stmt = $conn->prepare(
            "UPDATE products
             SET stock = stock - ?
             WHERE id = ?"
        );

        foreach ($items as $item) {

            $item_stmt->bind_param(
                'iiid',
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price']
            );

            if (!$item_stmt->execute()) {
                throw new Exception('Order item creation failed.');
            }

            $stock_stmt->bind_param(
                'ii',
                $item['quantity'],
                $item['id']
            );

            if (!$stock_stmt->execute()) {
                throw new Exception('Stock update failed.');
            }
        }

        $item_stmt->close();
        $stock_stmt->close();

        $conn->commit();

        $_SESSION['last_order'] = [
            'order_id' => $order_id,
            'transaction_id' => $transaction_id,
            'amount' => $total,
            'payment_method' => $method,
            'customer_name' => $checkout['full_name'],
            'payment_status' => $payment_status
        ];

        unset(
            $_SESSION['cart'],
            $_SESSION['checkout'],
            $_SESSION['esewa_pending']
        );

        return true;

    } catch (Throwable $e) {

        $conn->rollback();

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| CREATE eSEWA PENDING ORDER
|--------------------------------------------------------------------------
*/

function create_esewa_pending_order($conn, $checkout, $transaction_uuid)
{
    $cart = $_SESSION['cart'] ?? [];

    if (!$cart) {
        return false;
    }

    $conn->begin_transaction();

    try {

        $items = [];
        $subtotal = 0;

        foreach ($cart as $id => $ci) {

            $pid = (int)$id;
            $qty = (int)$ci['quantity'];

            if ($qty < 1) {
                throw new Exception('Invalid quantity.');
            }

            $stmt = $conn->prepare(
                "SELECT id, name, price, stock
                 FROM products
                 WHERE id = ?
                 FOR UPDATE"
            );

            $stmt->bind_param('i', $pid);
            $stmt->execute();

            $product = $stmt->get_result()->fetch_assoc();

            $stmt->close();

            if (!$product) {
                throw new Exception('Product not found.');
            }

            if ((int)$product['stock'] < $qty) {
                throw new Exception('Product stock changed.');
            }

            $price = (float)$product['price'];

            $items[] = [
                'id' => $pid,
                'quantity' => $qty,
                'price' => $price
            ];

            $subtotal += $qty * $price;
        }

        $shipping = ($subtotal > 0 && $subtotal < 3000) ? 150 : 0;
        $total = $subtotal + $shipping;

        $method = 'eSewa';
        $payment_status = 'Pending';
        $order_status = 'Pending';

        $stmt = $conn->prepare(
            "INSERT INTO orders
            (
                user_id,
                total_amount,
                shipping_address,
                city,
                postal_code,
                phone,
                payment_method,
                payment_status,
                order_status,
                transaction_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            'idssssssss',
            $_SESSION['user_id'],
            $total,
            $checkout['address'],
            $checkout['city'],
            $checkout['postal_code'],
            $checkout['phone'],
            $method,
            $payment_status,
            $order_status,
            $transaction_uuid
        );

        if (!$stmt->execute()) {
            throw new Exception('Order creation failed.');
        }

        $order_id = $conn->insert_id;

        $stmt->close();

        $item_stmt = $conn->prepare(
            "INSERT INTO order_items
            (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)"
        );

        foreach ($items as $item) {

            $item_stmt->bind_param(
                'iiid',
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price']
            );

            if (!$item_stmt->execute()) {
                throw new Exception('Order item creation failed.');
            }
        }

        $item_stmt->close();

        $conn->commit();

        return [
            'order_id' => $order_id,
            'amount' => $total,
            'transaction_uuid' => $transaction_uuid
        ];

    } catch (Throwable $e) {

        $conn->rollback();

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| CASH ON DELIVERY
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && !$db_error
    && $mode === 'cod'
) {

    $txn = 'COD-HAMRO-' . date('Ymd') . '-' . random_int(10000, 99999);

    if (create_cod_order($conn, $checkout, $txn)) {

        redirect('/hamro_ecommerce/payment_success.php');

    } else {

        $errors[] = 'We could not create the order. Please try again.';
    }
}


/*
|--------------------------------------------------------------------------
| eSEWA PAYMENT
|--------------------------------------------------------------------------
|
| IMPORTANT:
| A completely NEW UUID is generated every time this page is opened.
| This prevents the "Duplicate transaction UUID" error from eSewa.
|
*/

$esewa_order = null;

if ($mode !== 'cod' && !$db_error) {

    /*
     * Remove any old payment attempt from the session.
     */
    unset($_SESSION['esewa_pending']);

    /*
     * Generate a NEW unique transaction UUID.
     *
     * Example:
     * HAMRO-20260901210532-A83F91C24B10
     */
    $uuid =
        'HAMRO-' .
        date('YmdHis') .
        '-' .
        strtoupper(bin2hex(random_bytes(6)));

    /*
     * Create a new pending order.
     */
    $created = create_esewa_pending_order(
        $conn,
        $checkout,
        $uuid
    );

    if ($created) {

        $esewa_order = $created;

        /*
         * Store the current payment attempt.
         */
        $_SESSION['esewa_pending'] = $created;

    } else {

        $errors[] =
            'We could not prepare the eSewa payment. Please return to your cart and try again.';
    }
}


/*
|--------------------------------------------------------------------------
| eSEWA SIGNATURE
|--------------------------------------------------------------------------
*/

$esewa_signature = '';

if ($esewa_order) {

    $esewa_signature = esewa_request_signature(
        (float)$esewa_order['amount'],
        $esewa_order['transaction_uuid']
    );
}


/*
|--------------------------------------------------------------------------
| SUCCESS / FAILURE URL
|--------------------------------------------------------------------------
*/

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$success_url =
    'http://' .
    $host .
    '/hamro_ecommerce/payment_success.php';

$failure_url =
    'http://' .
    $host .
    '/hamro_ecommerce/payment_failed.php?reason=esewa';


require_once __DIR__ . '/includes/header.php';

?>

<section class="section container payment-page">

    <div class="payment-head">

        <span class="eyebrow">
            MOBILE PAYMENT
        </span>

        <h1>
            eSewa Payment
        </h1>

        <p>
            You will be securely redirected to eSewa to complete your payment.
        </p>

    </div>


    <?php foreach ($errors as $error): ?>

        <div class="alert error">
            <?= e($error) ?>
        </div>

    <?php endforeach; ?>


    <div class="payment-layout">


        <div class="payment-card">


            <?php if ($mode === 'cod'): ?>


                <div class="demo-icon">
                    💵
                </div>

                <h2>
                    Cash on Delivery
                </h2>

                <p>
                    Your order will be recorded with a pending payment status.
                </p>


                <form method="post">

                    <button
                        class="btn btn-primary btn-large btn-block"
                        type="submit"
                    >
                        Confirm Cash on Delivery
                    </button>

                </form>


            <?php elseif ($esewa_order): ?>


                <div class="demo-icon">
                    📱
                </div>

                <h2>
                    eSewa Payment
                </h2>

                <p>
                    Please wait while we redirect you to the eSewa payment page.
                </p>


                <form
                    method="post"
                    action="<?= e(ESEWA_PAYMENT_URL) ?>"
                    id="esewaForm"
                >


                    <input
                        type="hidden"
                        name="amount"
                        value="<?= e(number_format($esewa_order['amount'], 2, '.', '')) ?>"
                    >


                    <input
                        type="hidden"
                        name="tax_amount"
                        value="0"
                    >


                    <input
                        type="hidden"
                        name="total_amount"
                        value="<?= e(number_format($esewa_order['amount'], 2, '.', '')) ?>"
                    >


                    <input
                        type="hidden"
                        name="transaction_uuid"
                        value="<?= e($esewa_order['transaction_uuid']) ?>"
                    >


                    <input
                        type="hidden"
                        name="product_code"
                        value="<?= e(ESEWA_PRODUCT_CODE) ?>"
                    >


                    <input
                        type="hidden"
                        name="product_service_charge"
                        value="0"
                    >


                    <input
                        type="hidden"
                        name="product_delivery_charge"
                        value="0"
                    >


                    <input
                        type="hidden"
                        name="success_url"
                        value="<?= e($success_url) ?>"
                    >


                    <input
                        type="hidden"
                        name="failure_url"
                        value="<?= e($failure_url) ?>"
                    >


                    <input
                        type="hidden"
                        name="signed_field_names"
                        value="total_amount,transaction_uuid,product_code"
                    >


                    <input
                        type="hidden"
                        name="signature"
                        value="<?= e($esewa_signature) ?>"
                    >


                    <button
                        class="btn btn-primary btn-large btn-block"
                        type="submit"
                        id="payButton"
                    >
                        Continue to eSewa
                    </button>

                </form>


                <p class="transaction">
                    Transaction Reference:
                    <?= e($esewa_order['transaction_uuid']) ?>
                </p>


            <?php endif; ?>


        </div>


        <aside class="summary-card">

            <h2>
                Amount
            </h2>


            <div class="summary-total">

                <span>
                    Total
                </span>

                <strong>
                    Rs. <?= number_format($checkout['total'], 2) ?>
                </strong>

            </div>


            <p>
                Method:
                <?= e($checkout['payment_method']) ?>
            </p>


            <p>
                Customer:
                <?= e($checkout['full_name']) ?>
            </p>

        </aside>


    </div>

</section>


<div
    class="loading-overlay"
    id="loadingOverlay"
>

    <div class="loader"></div>

    <h3>
        Redirecting to eSewa...
    </h3>

    <p>
        Please wait while the payment page opens.
    </p>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('esewaForm');

    const overlay = document.getElementById('loadingOverlay');

    const button = document.getElementById('payButton');


    if (form && overlay) {

        form.addEventListener('submit', function () {

            overlay.classList.add('show');

            if (button) {

                button.disabled = true;

                button.textContent = 'Redirecting...';

            }

        });


        setTimeout(function () {

            form.submit();

        }, 700);

    }

});

</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>