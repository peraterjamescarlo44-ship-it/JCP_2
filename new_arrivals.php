<?php
session_start();
require_once __DIR__ . '/database/functions.php';
requireLogin();

if (!function_exists('slugify')) {
    function slugify(string $s): string {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        return trim($s, '-');
    }
}

// ----- Clean any corrupted cart data (remove "undefined" entries) -----
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
        $price_float = (float) str_replace(['P', ',', ' '], '', $price);

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

    // PRG: redirect so refresh doesn't re-add
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ----- REMOVE ENTIRE ITEM (GET) -----
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        if (empty($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
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

// ----- Book data -----
// ----- Book data (loaded from shared catalogue) -----
$books = require __DIR__ . '/includes/books.php';

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
        .cart-item:last-of-type {
            border-bottom: none;
        }
        .remove-btn {
            color: #d9534f;
            font-weight: bold;
            text-decoration: none;
            padding: 0 6px;
            font-size: 1.3rem;
            transition: transform 0.2s;
            line-height: 1;
        }
        .remove-btn:hover {
            transform: scale(1.3);
            color: #c9302c;
        }
        .clear-cart-btn {
            color: #d9534f;
            font-size: 0.9rem;
            text-decoration: underline;
            font-weight: 500;
            transition: color 0.2s;
        }
        .clear-cart-btn:hover {
            color: #c9302c;
        }
        #cartTotal {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--green);
            background: #f0f5f0;
            padding: 6px 16px;
            border-radius: 6px;
            white-space: nowrap;
        }
        .cart-empty {
            color: #888;
            font-size: 0.95rem;
            padding: 10px 0;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main>
    <section class="page-hero">
        <h1>NEW ARRIVALS</h1>
        <p>Home › New Arrivals</p>
    </section>
    <section class="section shop-layout">
        <aside class="filters">
            <h3>FILTERS</h3>
            <h4>Category</h4>
            <?php foreach(['Fiction','Non-Fiction','Self-Help','Academic','Children','Comics','Poetry'] as $c): ?>
            <label><input type="checkbox" class="filter-category" value="<?= $c ?>"> <?= $c ?></label>
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
            <?php foreach($books as $i => $book): ?>
                <article class="product-card" data-category="<?= $book[4] ?>" data-price="<?= floatval(str_replace(['P',','],'',$book[2])) ?>" data-name="<?= strtolower($book[0]) ?>">
    <div class="product-image">
        <a href="book.php?slug=<?= urlencode(slugify($book[0])) ?>">
            <img src="assets/books/<?= $book[3] ?>"
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
            </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>