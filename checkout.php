<?php
/**
 * checkout.php – JCP Bookworks
 * Requires login. Shows cart summary, collects shipping info, places order.
 */
require_once __DIR__ . '/database/functions.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---- redirect if cart empty ---- */
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['flash'] = 'Your cart is empty — add a book before checking out.';
    header('Location: shop.php');
    exit;
}

/* ---- helpers ---- */
if (!function_exists('clean')) {
    function clean(?string $s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

/* ---- build cart rows ---- */
$items    = [];
$subtotal = 0.0;

foreach ($_SESSION['cart'] as $slug => $item) {
    if (!is_array($item)) continue;
    $qty = (int)($item['qty'] ?? 1);
    if ($qty < 1) continue;

    $price = (float)($item['price'] ?? 0);
    $line  = $price * $qty;
    $subtotal += $line;

    $items[] = [
        'slug'   => $slug,
        'title'  => $item['title']  ?? $slug,
        'author' => $item['author'] ?? '',
        'price'  => $price,
        'image'  => $item['image']  ?? 'placeholder.png',
        'qty'    => $qty,
        'line'   => $line,
    ];
}

$shipping = 0.00;                // FREE DELIVERY
$total    = $subtotal + $shipping;

/* ---- handle order submission ---- */
$errors  = [];
$success = false;
$orderNo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city']    ?? '');
    $zip     = trim($_POST['zip']     ?? '');
    $payment = $_POST['payment'] ?? 'cod';

    if ($name === '')    $errors[] = 'Full name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
                         $errors[] = 'A valid email is required.';
    if ($phone === '')   $errors[] = 'Phone number is required.';
    if ($address === '') $errors[] = 'Street address is required.';
    if ($city === '')    $errors[] = 'City is required.';
    if ($zip === '')     $errors[] = 'ZIP / postal code is required.';

    if (empty($errors)) {
        $orderNo = 'JCP-' . strtoupper(bin2hex(random_bytes(4)));

        /* Save the order in the session (replace with DB insert if you have one) */
        $_SESSION['last_order'] = [
            'order_no' => $orderNo,
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'address'  => $address,
            'city'     => $city,
            'zip'      => $zip,
            'payment'  => $payment,
            'items'    => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total'    => $total,
            'placed_at'=> date('Y-m-d H:i:s'),
            'user_id'  => $_SESSION['user']['id'] ?? null,
        ];

        /* Empty the cart */
        $_SESSION['cart'] = [];

        header('Location: checkout.php?success=1');
        exit;
    }
}

/* ---- success view (after redirect) ---- */
if (isset($_GET['success']) && !empty($_SESSION['last_order'])) {
    $success = true;
    $orderNo = $_SESSION['last_order']['order_no'];
    $saved   = $_SESSION['last_order'];
    /* keep it for the receipt, but flag it as shown */
    unset($_SESSION['last_order']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="page-hero">
        <h1>CHECKOUT</h1>
        <p><a href="index.php">Home</a> › <a href="cart.php">Cart</a> › Checkout</p>
    </section>

    <section class="section checkout-page">

    <?php if ($success): ?>

        <!-- ============ SUCCESS ============ -->
        <div class="checkout-success">
            <div class="checkout-success-icon">✓</div>
            <h2>Order placed!</h2>
            <p class="checkout-success-order">
                Your order number is <strong><?= clean($orderNo) ?></strong>
            </p>
            <p>
                A confirmation has been sent to <strong><?= clean($saved['email']) ?></strong>.
                We'll ship your books to:
            </p>
            <p class="checkout-success-address">
                <?= clean($saved['name']) ?><br>
                <?= clean($saved['address']) ?><br>
                <?= clean($saved['city']) ?> <?= clean($saved['zip']) ?><br>
                <?= clean($saved['phone']) ?>
            </p>
            <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
        </div>

    <?php else: ?>

        <?php if (!empty($errors)): ?>
            <div class="flash flash-error">
                <strong>Please fix the following:</strong>
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= clean($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="checkout.php" class="checkout-layout" novalidate>

            <!-- ============ LEFT: SHIPPING FORM ============ -->
            <section class="checkout-form">
                <h2>Shipping Details</h2>

                <div class="field-row">
                    <div class="field">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required
                               value="<?= clean($_POST['name'] ?? ($_SESSION['user']['name'] ?? '')) ?>">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?= clean($_POST['email'] ?? ($_SESSION['user']['email'] ?? '')) ?>">
                    </div>
                </div>

                <div class="field">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required
                           value="<?= clean($_POST['phone'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="address">Street Address</label>
                    <input type="text" id="address" name="address" required
                           value="<?= clean($_POST['address'] ?? '') ?>">
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required
                               value="<?= clean($_POST['city'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="zip">ZIP / Postal Code</label>
                        <input type="text" id="zip" name="zip" required
                               value="<?= clean($_POST['zip'] ?? '') ?>">
                    </div>
                </div>

                <h2 class="checkout-section-title">Payment Method</h2>

                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment" value="cod" checked>
                        <span>
                            <strong>Cash on Delivery</strong>
                            <em>Pay when your books arrive</em>
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment" value="gcash">
                        <span>
                            <strong>GCash</strong>
                            <em>Send payment via GCash</em>
                        </span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment" value="bank">
                        <span>
                            <strong>Bank Transfer</strong>
                            <em>BPI / BDO transfer</em>
                        </span>
                    </label>
                </div>
            </section>

            <!-- ============ RIGHT: ORDER SUMMARY ============ -->
            <aside class="checkout-summary">
                <h2>Order Summary</h2>

                <ul class="checkout-items">
                    <?php foreach ($items as $it): ?>
                        <li class="checkout-item">
                            <img src="assets/books/<?= clean($it['image']) ?>"
                                 alt="<?= clean($it['title']) ?>"
                                 onerror="this.src='assets/books/placeholder.png';">
                            <div class="checkout-item-info">
                                <span class="checkout-item-title"><?= clean($it['title']) ?></span>
                                <span class="checkout-item-author"><?= clean($it['author']) ?></span>
                                <span class="checkout-item-qty">Qty <?= (int)$it['qty'] ?></span>
                            </div>
                            <span class="checkout-item-price">₱<?= number_format($it['line'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="sum-row">
                    <span>Subtotal</span>
                    <span>₱<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="sum-row muted">
                    <span>Shipping</span>
                    <span>FREE</span>
                </div>
                <div class="sum-total">
                    <span>Total</span>
                    <span>₱<?= number_format($total, 2) ?></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Place Order
                </button>

                <a href="cart.php" class="checkout-back">← Back to cart</a>

                <p class="cart-note">
                    ▣ Free delivery &nbsp;·&nbsp; ◷ Easy returns<br>
                    ▢ Secure payment &nbsp;·&nbsp; ♧ 24/7 support
                </p>
            </aside>

        </form>

    <?php endif; ?>

    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>