<?php
/**
 * Header – JCP Bookworks (Sticky & Responsive)
 */
require_once __DIR__ . '/../database/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---- Detect admin session (no need to load admin_functions.php) ---- */
$is_admin = !empty($_SESSION['admin']['id']);

/* ---- Cart count (only relevant for normal users) ---- */
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += (int)($item['qty'] ?? 1);
    }
}

/* ---- Wishlist count ---- */
$wishlist_count = 0;
if (!empty($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
    $wishlist_count = count($_SESSION['wishlist']);
}

/* ---- Current user ---- */
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

        <!-- Hide search on admin pages -->
        <?php if (!$is_admin): ?>
            <div class="search-wrap">
                <input id="siteSearch" type="search" placeholder="Search for books..." autocomplete="off">
                <button id="siteSearchBtn" aria-label="Search">⌕</button>
                <div id="searchSuggestions" class="search-suggestions"></div>
            </div>
        <?php endif; ?>

        <div class="header-actions">
            <?php if ($is_admin): ?>
                <!-- ADMIN: only show dashboard + logout -->
                <a href="admin.php" title="Admin Dashboard" aria-label="Admin Dashboard" 
                   style="font-size:14px; font-weight:600; letter-spacing:1px;">⚙ ADMIN</a>
                <a href="admin_logout.php" title="Logout" aria-label="Logout"
                   style="font-size:12px; font-weight:600; color:#d9534f;">LOGOUT</a>
            <?php else: ?>
                <!-- NORMAL USER: full icon set -->
                <a href="<?= $user ? 'account.php' : 'login.php' ?>" title="Account" aria-label="Account">👤︎</a>
                <a href="wishlist.php" id="wishlistBtn" title="Wishlist" aria-label="Wishlist">
                    ♡ <span id="wishlistCount"><?= $wishlist_count ?></span>
                </a>
                <a href="cart.php" title="Cart" aria-label="Shopping cart">
                    🛒︎ <span id="cartCount"><?= $cart_count ?></span>
                </a>
            <?php endif; ?>
        </div>

        <button class="mobile-menu" id="mobileMenu" aria-label="Toggle navigation menu">☰</button>
    </header>

    <nav class="main-nav" id="mainNav">
        <?php
        $current = basename($_SERVER['PHP_SELF']);

        // Different nav for admin vs user
        if ($is_admin) {
            $nav_links = [
                'DASHBOARD'   => 'admin.php',
                'PRODUCTS'    => 'admin.php#products',
                'USERS'       => 'admin.php#users',
                'VIEW SHOP'   => 'shop.php',
                'HOME'        => 'index.php',
            ];
        } else {
            $nav_links = [
                'HOME'          => 'index.php',
                'CATEGORIES'    => 'categories.php',
                'BESTSELLERS'   => 'shop.php',
                'NEW ARRIVALS'  => 'new_arrivals.php?new=1',
                'ABOUT US'      => 'index.php#about',
                'CONTACT'       => 'contact.php',
            ];
        }

        foreach ($nav_links as $label => $url):
            $active = '';
            if (strpos($url, '?') !== false) {
                $parts = explode('?', $url);
                if ($current === $parts[0] && isset($_GET['new'])) $active = 'active';
            } elseif (strpos($url, '#') !== false) {
                // anchor — don't mark active
            } elseif ($current === basename($url)) {
                $active = 'active';
            }
        ?>
            <a href="<?= $url ?>" class="<?= $active ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </nav>

</div>
<!-- END STICKY WRAPPER -->