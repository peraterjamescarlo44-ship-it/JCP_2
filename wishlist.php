<?php
/**
 * wishlist.php – JCP Bookworks
 * Requires login. Shows saved books, with add-to-cart and remove actions.
 */
require_once __DIR__ . '/database/functions.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ---- helpers ---- */
if (!function_exists('slugify')) {
    function slugify(string $s): string {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        return trim($s, '-');
    }
}
if (!function_exists('clean')) {
    function clean(?string $s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

/* ---- handle "Move to Cart" and "Remove" ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $slug   = trim($_POST['slug'] ?? '');

    if ($slug !== '') {
        $books = require __DIR__ . '/includes/books.php';
        $found = null;
        foreach ($books as $b) {
            if (slugify($b[0]) === $slug) { $found = $b; break; }
        }

        if ($action === 'move_to_cart' && $found) {
            $price = (float) str_replace(['P', ',', ' '], '', $found[2]);
            if (isset($_SESSION['cart'][$slug])) {
                $_SESSION['cart'][$slug]['qty']++;
            } else {
                $_SESSION['cart'][$slug] = [
                    'title'  => $found[0],
                    'author' => $found[1],
                    'price'  => $price,
                    'image'  => $found[3],
                    'qty'    => 1,
                ];
            }
            unset($_SESSION['wishlist'][$slug]);   // remove from wishlist after moving
        }

        if ($action === 'remove') {
            unset($_SESSION['wishlist'][$slug]);
        }
    }

    if ($action === 'clear') {
        $_SESSION['wishlist'] = [];
    }

    header('Location: wishlist.php');
    exit;
}

/* ---- load saved books ---- */
$catalogue = require __DIR__ . '/includes/books.php';
$bySlug    = [];
foreach ($catalogue as $b) {
    $bySlug[slugify($b[0])] = $b;
}

$items = [];
foreach ($_SESSION['wishlist'] as $slug => $savedAt) {
    if (!isset($bySlug[$slug])) continue;
    $b = $bySlug[$slug];
    $items[] = [
        'slug'   => $slug,
        'title'  => $b[0],
        'author' => $b[1],
        'price'  => $b[2],
        'image'  => $b[3],
        'cat'    => $b[4],
        'desc'   => $b[5] ?? '',
    ];
}

$empty = empty($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="page-hero">
        <h1>YOUR WISHLIST</h1>
        <p><a href="index.php">Home</a> › Wishlist</p>
    </section>

    <section class="section wishlist-page">

    <?php if ($empty): ?>

        <div class="cart-empty">
            <span class="big">♡</span>
            <h2>Your wishlist is empty</h2>
            <p>Save books you love by tapping the heart on any book.</p>
            <a class="btn btn-primary" href="shop.php">Browse Books</a>
        </div>

    <?php else: ?>

        <div class="wishlist-grid">
            <?php foreach ($items as $it): ?>
                <article class="wishlist-card">
                    <a class="wishlist-card-image" href="book.php?slug=<?= urlencode($it['slug']) ?>">
                        <img src="assets/books/<?= clean($it['image']) ?>"
                             alt="<?= clean($it['title']) ?>"
                             onerror="this.src='assets/books/placeholder.png';">
                    </a>

                    <div class="wishlist-card-body">
                        <span class="book-detail-category"><?= clean($it['cat']) ?></span>
                        <h3>
                            <a href="book.php?slug=<?= urlencode($it['slug']) ?>">
                                <?= clean($it['title']) ?>
                            </a>
                        </h3>
                        <p class="wishlist-card-author"><?= clean($it['author']) ?></p>
                        <p class="wishlist-card-price"><?= clean($it['price']) ?></p>

                        <div class="wishlist-card-actions">
                            <form method="post" action="wishlist.php" style="display:inline">
                                <input type="hidden" name="action" value="move_to_cart">
                                <input type="hidden" name="slug" value="<?= clean($it['slug']) ?>">
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </form>

                            <form method="post" action="wishlist.php" style="display:inline">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="slug" value="<?= clean($it['slug']) ?>">
                                <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Remove this book from your wishlist?');">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <form method="post" action="wishlist.php" class="wishlist-tools">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Clear your entire wishlist?');">
                Clear Wishlist
            </button>
        </form>

    <?php endif; ?>

    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>