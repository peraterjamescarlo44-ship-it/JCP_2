<?php
/**
 * cart.php – JCP Bookworks
 */
require_once __DIR__ . '/database/functions.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---- Safe-escape helper (works on null / int / array) ---- */
if (!function_exists('h')) {
    function h($v): string {
        if (is_array($v) || is_object($v)) return '';
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* =========================================================
   NORMALISE CART
   ========================================================= */
$norm = [];
foreach ($_SESSION['cart'] as $key => $entry) {
    if (!is_array($entry)) continue;

    $slug = (string)($entry['slug'] ?? $key);
    $qty  = (int)($entry['qty'] ?? 1);
    if ($qty < 1) continue;

    $norm[$slug] = [
        'slug'   => $slug,
        'title'  => (string)($entry['title']  ?? ('Book #' . $slug)),
        'author' => (string)($entry['author'] ?? ''),
        'price'  => (float)($entry['price']   ?? 0),
        'image'  => (string)($entry['image']  ?? 'placeholder.png'),
        'qty'    => min($qty, 99),
    ];
}
$_SESSION['cart'] = $norm;

/* =========================================================
   POST ACTIONS
   ========================================================= */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'clear') {
        $_SESSION['cart'] = [];

    } elseif ($action === 'remove' && isset($_POST['slug'])) {
        unset($_SESSION['cart'][$_POST['slug']]);

    } elseif ($action === 'update') {
        if (isset($_POST['remove_id'])) {
            unset($_SESSION['cart'][$_POST['remove_id']]);
        } else {
            $qtys = (isset($_POST['qty']) && is_array($_POST['qty'])) ? $_POST['qty'] : [];

            if (isset($_POST['increase'], $qtys[$_POST['increase']])) {
                $qtys[$_POST['increase']] = (int)$qtys[$_POST['increase']] + 1;
            }
            if (isset($_POST['decrease'], $qtys[$_POST['decrease']])) {
                $qtys[$_POST['decrease']] = (int)$qtys[$_POST['decrease']] - 1;
            }

            foreach ($qtys as $slug => $q) {
                if (!isset($_SESSION['cart'][$slug])) continue;
                $q = (int)$q;
                if ($q <= 0) unset($_SESSION['cart'][$slug]);
                else         $_SESSION['cart'][$slug]['qty'] = min($q, 99);
            }
        }
    }

    header('Location: cart.php');
    exit;
}

/* =========================================================
   BUILD ROWS
   ========================================================= */
$items    = [];
$subtotal = 0.0;

foreach ($_SESSION['cart'] as $slug => $item) {
    $qty = (int)($item['qty'] ?? 1);
    if ($qty < 1) continue;

    $price = (float)($item['price'] ?? 0);
    $line  = $price * $qty;
    $subtotal += $line;

    $items[] = [
        'slug'   => (string)$slug,
        'title'  => (string)($item['title']  ?? $slug),
        'author' => (string)($item['author'] ?? ''),
        'price'  => $price,
        'image'  => (string)($item['image']  ?? 'placeholder.png'),
        'qty'    => $qty,
        'line'   => $line,
    ];
}

$shipping = 0.00;
$total    = $subtotal + $shipping;
$empty    = ($items === []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="page-hero">
        <h1>YOUR CART</h1>
        <p>Home › Cart</p>
    </section>

    <section class="section cart-page">

    <?php if ($empty): ?>

        <div class="cart-empty">
            <span class="big">🛒</span>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any books yet.</p>
            <a class="btn btn-primary" href="shop.php">Browse Books</a>
        </div>

    <?php else: ?>

        <div class="cart-layout">

            <section class="cart-items">
                <form method="post" action="cart.php" id="cartForm">
                    <input type="hidden" name="action" value="update">

                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th colspan="2">Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td class="col-img">
                                    <img class="cart-row-img"
                                         src="assets/books/<?= h($it['image']) ?>"
                                         alt="<?= h($it['title']) ?>"
                                         onerror="this.src='assets/books/placeholder.png';">
                                </td>

                                <td class="col-info">
                                    <span class="cart-item-title"><?= h($it['title']) ?></span>
                                    <?php if ($it['author'] !== ''): ?>
                                        <div class="cart-item-author"><?= h($it['author']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="col-price">₱<?= number_format((float)$it['price'], 2) ?></td>

                                <td class="col-qty">
                                    <div class="qty-box">
                                        <button type="submit" name="decrease"
                                                value="<?= h($it['slug']) ?>"
                                                aria-label="Decrease quantity">−</button>

                                        <input type="number"
                                               name="qty[<?= h($it['slug']) ?>]"
                                               value="<?= (int)$it['qty'] ?>"
                                               min="0" max="99" step="1">

                                        <button type="submit" name="increase"
                                                value="<?= h($it['slug']) ?>"
                                                aria-label="Increase quantity">+</button>
                                    </div>
                                </td>

                                <td class="col-total">₱<?= number_format((float)$it['line'], 2) ?></td>

                                <td class="col-remove">
                                    <button type="submit" name="remove_id"
                                            value="<?= h($it['slug']) ?>"
                                            class="link-remove"
                                            onclick="return confirm('Remove this item from your cart?');">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="cart-tools">
                        <a class="btn" href="shop.php">← Continue Shopping</a>
                        <button type="submit" class="btn">Update Cart</button>
                    </div>
                </form>

                <form method="post" action="cart.php" class="cart-clear-form">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Remove ALL items from your cart?');">
                        Clear Cart
                    </button>
                </form>
            </section>

            <aside class="cart-summary">
                <h2>Order Summary</h2>

                <div class="sum-row">
                    <span>Subtotal</span>
                    <span>₱<?= number_format((float)$subtotal, 2) ?></span>
                </div>

                <div class="sum-row muted">
                    <span>Shipping</span>
                    <span>FREE</span>
                </div>

                <div class="sum-total">
                    <span>Total</span>
                    <span>₱<?= number_format((float)$total, 2) ?></span>
                </div>

                <a class="btn btn-primary btn-block" href="checkout.php">Proceed to Checkout</a>

                <p class="cart-note">
                    ▣ Free delivery &nbsp;·&nbsp; ◷ Easy returns<br>
                    ▢ Secure payment &nbsp;·&nbsp; ♧ 24/7 support
                </p>
            </aside>

        </div>

        <script>
        (function () {
            var form = document.getElementById('cartForm');
            if (!form) return;
            form.querySelectorAll('.qty-box input[type="number"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    if (input.value === '') input.value = '1';
                    form.submit();
                });
            });
        })();
        </script>

    <?php endif; ?>

    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>