<?php
/**
 * book.php – JCP Bookworks
 * Book detail page. Shows one book by ?slug=... and allows add-to-cart.
 */
require_once __DIR__ . '/database/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ---- slugify safety (in case functions.php doesn't have it yet) ---- */
if (!function_exists('slugify')) {
    function slugify(string $s): string {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        return trim($s, '-');
    }
}

/* ---- clean() safety ---- */
if (!function_exists('clean')) {
    function clean(?string $s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

/* ---- Load catalogue ---- */
$books = require __DIR__ . '/includes/books.php';

/* ---- Find the requested book by slug ---- */
$slug = isset($_GET['slug']) ? (string)$_GET['slug'] : '';
$book  = null;
$index = null;

foreach ($books as $i => $b) {
    if (slugify($b[0]) === $slug) {
        $book  = $b;
        $index = $i;
        break;
    }
}

/* Book not found → back to shop */
if (!$book) {
    header('Location: shop.php');
    exit;
}

/* ---- Handle ADD TO CART ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $slug_post  = trim($_POST['slug']   ?? '');
    $title      = trim($_POST['title']  ?? '');
    $author     = trim($_POST['author'] ?? '');
    $price      = $_POST['price']  ?? '';
    $image      = trim($_POST['image']  ?? '');

    if ($slug_post !== '' && $title !== '' && $price !== '') {
        $price_float = (float) str_replace(['P', ',', ' '], '', $price);

        if (isset($_SESSION['cart'][$slug_post])) {
            $_SESSION['cart'][$slug_post]['qty']++;
        } else {
            $_SESSION['cart'][$slug_post] = [
                'title'  => $title,
                'author' => $author,
                'price'  => $price_float,
                'image'  => $image,
                'qty'    => 1,
            ];
        }
    }

    header('Location: book.php?slug=' . urlencode($slug_post) . '&added=1');
    exit;
}

/* ---- Related books: same category, excluding current ---- */
$related = [];
foreach ($books as $i => $b) {
    if ($i === $index) continue;
    if ($b[4] === $book[4]) $related[] = ['index' => $i, 'book' => $b];
    if (count($related) >= 4) break;
}

$title_clean  = clean($book[0]);
$author_clean = clean($book[1]);
$price_clean  = clean($book[2]);
$image_clean  = clean($book[3]);
$cat_clean    = clean($book[4]);
$desc_clean   = clean($book[5] ?? '');
$slug_clean   = clean(slugify($book[0]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title_clean ?> | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="page-hero">
        <h1><?= strtoupper($title_clean) ?></h1>
        <p><a href="index.php">Home</a> › <a href="shop.php">Shop</a> › <?= $title_clean ?></p>
    </section>

    <section class="section book-detail">

        <?php if (isset($_GET['added'])): ?>
            <div class="flash flash-success">
                ✓ Added to cart. <a href="cart.php">View cart</a>
            </div>
        <?php endif; ?>

        <div class="book-detail-layout">

            <div class="book-detail-image">
                <img src="assets/books/<?= $image_clean ?>"
                     alt="<?= $title_clean ?>"
                     onerror="this.src='assets/books/placeholder.png';">
            </div>

            <div class="book-detail-info">
                <span class="book-detail-category"><?= $cat_clean ?></span>
                <h2><?= $title_clean ?></h2>
                <p class="book-detail-author">by <?= $author_clean ?></p>

                <p class="book-detail-price"><?= $price_clean ?></p>

                <h3 class="book-detail-about">About this book</h3>
                <p class="book-detail-desc"><?= $desc_clean ?></p>

                <form method="post" action="book.php?slug=<?= urlencode(slugify($book[0])) ?>" class="book-detail-form">
    <input type="hidden" name="add_to_cart" value="1">
    <input type="hidden" name="slug"   value="<?= clean(slugify($book[0])) ?>">
    <input type="hidden" name="title"  value="<?= clean($book[0]) ?>">
    <input type="hidden" name="author" value="<?= clean($book[1]) ?>">
    <input type="hidden" name="price"  value="<?= clean($book[2]) ?>">
    <input type="hidden" name="image"  value="<?= clean($book[3]) ?>">
    <button type="submit" class="btn btn-primary add-product cart-btn">ADD TO CART</button>
</form>

<!-- ✅ SEPARATE FORM — not nested -->
<?php
$slug_key = slugify($book[0]);
$in_wish  = isset($_SESSION['wishlist'][$slug_key]);
?>
<form method="post" action="wishlist_action.php" class="book-detail-wish">
    <input type="hidden" name="slug" value="<?= clean($slug_key) ?>">
    <input type="hidden" name="back" value="<?= clean($_SERVER['REQUEST_URI']) ?>">
    <button type="submit" class="btn wishlist-btn-detail <?= $in_wish ? 'active' : '' ?>">
        <span class="heart"><?= $in_wish ? '♥' : '♡' ?></span>
        <?= $in_wish ? 'Saved to Wishlist' : 'Add to Wishlist' ?>
    </button>
</form>

<a href="shop.php" class="book-detail-back">← Back to shop</a>
                <a href="shop.php" class="book-detail-back">← Back to shop</a>
            </div>

        </div>

        <?php if (!empty($related)): ?>
            <div class="book-related">
                <h3>You might also like</h3>
                <div class="product-grid">
                    <?php foreach ($related as $r):
                        $rb = $r['book'];
                        $rslug = slugify($rb[0]);
                    ?>
                        <article class="product-card" data-category="<?= clean($rb[4]) ?>">
                            <div class="product-image">
                                <a href="book.php?slug=<?= urlencode($rslug) ?>">
                                    <img src="assets/books/<?= clean($rb[3]) ?>"
                                         alt="<?= clean($rb[0]) ?>"
                                         onerror="this.src='assets/books/placeholder.png';">
                                </a>
                            </div>
                            <h3>
                                <a href="book.php?slug=<?= urlencode($rslug) ?>">
                                    <?= clean($rb[0]) ?>
                                </a>
                            </h3>
                            <p><?= clean($rb[1]) ?></p>
                            <strong><?= clean($rb[2]) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>