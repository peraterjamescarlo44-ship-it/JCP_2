<?php
/**
 * Header – JCP Bookworks (Sticky & Responsive)
 */
require_once __DIR__ . '/../database/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += (int)($item['qty'] ?? 1);
    }
}

$wishlist_count = 0;
if (!empty($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
    $wishlist_count = count($_SESSION['wishlist']);
}

// Remove ensureDataFiles() – it's not needed here

/* ---- ensure session is available ---- */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = function_exists('currentUser') ? currentUser() : ($_SESSION['user'] ?? null);
?>
<!-- STICKY WRAPPER: This keeps everything glued to the top -->
<div class="sticky-header-wrapper">

    <div class="top-strip">
        <span>▣ FREE DELIVERY</span>
        <span>◷ EASY RETURNS</span>
        <span>▢ SECURE PAYMENT</span>
        <span>♧ 24/7 SUPPORT</span>
    </div>

    <header class="site-header">
        <a class="brand" href="index.php">
            <span class="brand-main">JCP</span>
            <span class="brand-sub">BOOKWORKS</span>
        </a>

        <div class="search-wrap">
            <input id="siteSearch" type="search" placeholder="Search for books..." autocomplete="off">
            <button id="siteSearchBtn" aria-label="Search">⌕</button>
            <div id="searchSuggestions" class="search-suggestions"></div>
        </div>

        <div class="header-actions">
            <a href="<?= $user ? 'account.php' : 'login.php' ?>" title="Account" aria-label="Account">👤︎</a>
            <a href="wishlist.php" id="wishlistBtn" title="Wishlist" aria-label="Wishlist">♡ <span id="wishlistCount"><?= $wishlist_count ?></span>
            </a>
            <a href="cart.php" title="Cart" aria-label="Shopping cart"> 🛒︎ <span id="cartCount"><?= $cart_count ?></span>
            </a>
        </div>

        <button class="mobile-menu" id="mobileMenu" aria-label="Toggle navigation menu">☰</button>
    </header>

    <nav class="main-nav" id="mainNav">
        <?php
        // Auto-highlight active page
        $current = basename($_SERVER['PHP_SELF']);
        $nav_links = [
            'HOME'          => 'index.php',
            'CATEGORIES'    => 'categories.php',
            'BESTSELLERS'   => 'shop.php',
            'NEW ARRIVALS'  => 'new_arrivals.php?new=1',
            'ABOUT US'      => 'index.php#about',
            'CONTACT'       => 'contact.php',
        ];
        foreach ($nav_links as $label => $url):
            $active = '';
            if (strpos($url, '?') !== false) {
                $parts = explode('?', $url);
                if ($current === $parts[0] && isset($_GET['new'])) $active = 'active';
            } elseif ($current === basename($url)) {
                $active = 'active';
            }
        ?>
            <a href="<?= $url ?>" class="<?= $active ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </nav>

</div>
<!-- END STICKY WRAPPER -->