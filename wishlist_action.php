<?php
/**
 * wishlist_action.php – toggles a book in/out of the wishlist, then redirects back.
 */
require_once __DIR__ . '/database/functions.php';
requireLogin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: wishlist.php');
    exit;
}

$action = $_POST['action'] ?? 'toggle';
$slug   = trim($_POST['slug'] ?? '');
$back   = $_POST['back']   ?? 'wishlist.php';

/* Only allow local relative redirects */
if (preg_match('#^https?://#i', $back) || strpos($back, '//') === 0) {
    $back = 'wishlist.php';
}

if ($action === 'clear') {
    $_SESSION['wishlist'] = [];
} elseif ($slug !== '') {
    if ($action === 'remove') {
        unset($_SESSION['wishlist'][$slug]);
    } else {
        /* toggle */
        if (isset($_SESSION['wishlist'][$slug])) {
            unset($_SESSION['wishlist'][$slug]);
        } else {
            $_SESSION['wishlist'][$slug] = time();
        }
    }
}

header('Location: ' . $back);
exit;