<?php
session_start();
require_once __DIR__ . '/database/functions.php';
require_once __DIR__ . '/database/admin_functions.php';

// Allow either a logged-in admin OR a logged-in normal user
if (!isAdminLoggedIn() && !isLoggedIn()) {
    header('Location: login.php');
    exit;
}

/* Category coming from categories.php */
$active_category = $_GET['category'] ?? '';
$valid_categories = ['Fiction','Non-Fiction','Self-Help','Academic','Children','Comics','Poetry'];
if ($active_category !== '' && !in_array($active_category, $valid_categories, true)) {
    $active_category = '';
}

if (!function_exists('slugify')) {
    function slugify(string $s): string {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        return trim($s, '-');
    }
}

// ----- Clean any corrupted cart data -----
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $item) {
        if (!isset($item['title']) || $item['title'] === '' || $item['title'] === 'undefined') {
            unset($_SESSION['cart'][$key]);
        }
    }
    if (empty($_SESSION['cart'])) {
        unset($_SESSION['cart']);
    }
}

// ----- ADD TO CART (POST) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $slug   = trim($_POST['slug']   ?? '');
    $title  = trim($_POST['title']  ?? '');
    $author = trim($_POST['author'] ?? '');
    $price  = $_POST['price'] ?? '';
    $image  = trim($_POST['image']  ?? '');

    if ($slug !== '' && $title !== '' && $price !== '') {
        $price_float = (float) str_replace(['₱', 'P', ',', ' '], '', $price);

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$slug])) {
            $_SESSION['cart'][$slug]['qty']++;
        } else {
            $_SESSION['cart'][$slug] = [
                'title'  => $title,
                'author' => $author,
                'price'  => $price_float,
                'image'  => $image,
                'qty'    => 1,
            ];
        }
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ----- REMOVE ENTIRE ITEM (GET) -----
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        if (empty($_SESSION['cart'])) unset($_SESSION['cart']);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ----- CLEAR CART (GET) -----
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ============================================================
// LOAD PRODUCTS FROM JSON (same source as admin.php)
// ============================================================
require_once __DIR__ . '/database/products.php';
$products_json = getProducts();

// Convert to the array shape your template uses:
// [0] title, [1] author, [2] price, [3] image, [4] category
$books = [];
foreach ($products_json as $p) {
    $books[] = [
        $p['title']    ?? '',
        $p['author']   ?? '',
        '₱' . number_format((float)($p['price'] ?? 0), 2),
        $p['image']    ?? '',
        $p['category'] ?? '',
    ];
}

// ----- Calculate cart totals -----
$cart_total = 0;
$total_items = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_total += $item['price'] * $item['qty'];
        $total_items += $item['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Additional cart panel styles – match test.html style */
        .cart-panel {
            margin-top: 30px;
            padding: 20px 24px;
            border: 2px solid var(--green);
            border-radius: 8px;
            background: #fafafa;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }
        .cart-panel h2 {
            font-family: Georgia, serif;
            font-size: 22px;
            font-weight: 500;
            margin-bottom: 12px;
            border-bottom: 2px solid var(--green);
            padding-bottom: 6px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            gap: 10px;
        }
        .cart-item:last-of-type { border-bottom: none; }
        .remove-btn {
            color: #d9534f;
            font-weight: bold;
            text-decoration: none;
            padding: 0 6px;
            font-size: 1.3rem;
            transition: transform 0.2s;
            line-height: 1;
        }
        .remove-btn:hover { transform: scale(1.3); color: #c9302c; }
        .clear-cart-btn {
            color: #d9534f;
            font-size: 0.9rem;
            text-decoration: underline;
            font-weight: 500;
            transition: color 0.2s;
        }
        .clear-cart-btn:hover { color: #c9302c; }
        #cartTotal {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--green);
            background: #f0f5f0;
            padding: 6px 16px;
            border-radius: 6px;
            white-space: nowrap;
        }
        .cart-empty { color: #888; font-size: 0.95rem; padding: 10px 0; }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main>
    <section class="page-hero">
        <h1>BEST SELLERS</h1>
        <p>Home › Best Sellers</p>
    </section>
    <section class="section shop-layout">
        <aside class="filters">
            <h3>FILTERS</h3>
            <h4>Category</h4>
            <?php foreach ($valid_categories as $c): ?>
                <label>
                    <input type="checkbox"
                           class="filter-category"
                           value="<?= htmlspecialchars($c) ?>"
                           <?= $active_category === $c ? 'checked' : '' ?>>
                    <?= htmlspecialchars($c) ?>
                </label>
            <?php endforeach; ?>
            <h4>Price</h4>
            <label><input type="radio" name="price" value="all" checked> All</label>
            <label><input type="radio" name="price" value="low"> Under P500</label>
            <label><input type="radio" name="price" value="mid"> P500 - P1000</label>
            <label><input type="radio" name="price" value="high"> Above P1000</label>
        </aside>
        <div class="shop-results">
            <div class="shop-toolbar">
                <span><?= count($books) ?> books</span>
                <select id="sortBooks">
                    <option value="default">Sort by</option>
                    <option value="name">Name A-Z</option>
                    <option value="price">Price low-high</option>
                </select>
            </div>
            <div class="product-grid" id="productGrid">
            <?php if (empty($books)): ?>
                <p style="padding:20px; color:#888;">
                    No products available.
                    <?php if (isAdminLoggedIn()): ?>
                        <a href="admin.php">Add one in the dashboard →</a>
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <?php foreach ($books as $i => $book): ?>
                    <article class="product-card"
                             data-category="<?= htmlspecialchars($book[4]) ?>"
                             data-price="<?= floatval(str_replace(['₱','P',','], '', $book[2])) ?>"
                             data-name="<?= strtolower(htmlspecialchars($book[0])) ?>">
                        <div class="product-image">
                            <a href="book.php?slug=<?= urlencode(slugify($book[0])) ?>">
                                <img src="assets/books/<?= htmlspecialchars($book[3]) ?>"
                                     alt="<?= clean($book[0]) ?>"
                                     onerror="this.src='assets/books/placeholder.png';">
                            </a>
                        </div>
                        <h3>
                            <a href="book.php?slug=<?= urlencode(slugify($book[0])) ?>">
                                <?= clean($book[0]) ?>
                            </a>
                        </h3>
                        <p><?= clean($book[1]) ?></p>
                        <strong><?= clean($book[2]) ?></strong>

                        <form method="post" style="display:inline;">
                            <input type="hidden" name="add_to_cart" value="1">
                            <input type="hidden" name="slug"   value="<?= clean(slugify($book[0])) ?>">
                            <input type="hidden" name="title"  value="<?= clean($book[0]) ?>">
                            <input type="hidden" name="author" value="<?= clean($book[1]) ?>">
                            <input type="hidden" name="price"  value="<?= clean($book[2]) ?>">
                            <input type="hidden" name="image"  value="<?= clean($book[3]) ?>">
                            <button type="submit" class="add-product cart-btn">ADD TO CART</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>

            <!-- ===== CART PANEL ===== -->
            <div id="cart" class="cart-panel">
                <div style="flex:1;">
                    <h2>🛒 Your Cart</h2>

                    <?php if (empty($_SESSION['cart'])): ?>
                        <p class="cart-empty">Your cart is empty.</p>
                    <?php else: ?>
                        <div id="cartItems">
                            <?php
                            $counter = 1;
                            foreach ($_SESSION['cart'] as $index => $item):
                            ?>
                                <div class="cart-item">
                                    <div style="flex:1;">
                                        <strong><?= $counter ?>.</strong>
                                        <?= clean($item['title']) ?>
                                        <span style="color:#888; font-size:0.8rem; margin-left:6px;">
                                            ×<?= (int)$item['qty'] ?>
                                        </span>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <span style="font-weight:600; color:var(--green); min-width:80px; text-align:right;">
                                            ₱<?= number_format($item['price'] * $item['qty'], 2) ?>
                                        </span>
                                        <a href="?remove=<?= urlencode($index) ?>"
                                           class="remove-btn"
                                           title="Remove item"
                                           onclick="return confirm('Remove this item from cart?')">✕</a>
                                    </div>
                                </div>
                            <?php
                                $counter++;
                            endforeach;
                            ?>

                            <div style="margin-top:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                                <a href="?clear=1"
                                   class="clear-cart-btn"
                                   onclick="return confirm('Clear all items from cart?')">
                                    🗑️ Clear All
                                </a>
                                <span style="font-size:0.85rem; color:#888;">
                                    Total items: <?= (int)$total_items ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px; min-width:120px;">
                    <strong id="cartTotal">₱<?= number_format($cart_total, 2) ?></strong>
                    <span style="font-size:0.7rem; color:#888;">Total</span>
                </div>
            </div>
            <!-- END CART PANEL -->
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>