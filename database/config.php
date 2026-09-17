<?php
/**
 * config.php – JCP Bookworks
 * Central configuration. Include this once at the top of every page.
 */

/* ---- Database (if/when you add one) ---- */
define('DB_HOST', 'localhost');
define('DB_NAME', 'jcp_bookworks');
define('DB_USER', 'root');
define('DB_PASS', '');               // XAMPP default: empty
define('DB_CHARSET', 'utf8mb4');

/* ---- Site ---- */
define('SITE_NAME',  'JCP Bookworks');
define('SITE_URL',   'http://localhost/JCP_Bookworks_Full_Website');
define('SITE_EMAIL', 'hello@jcpbookworks.test');

/* ---- Paths ---- */
define('BASE_PATH',   __DIR__);
define('ASSETS_URL',  SITE_URL . '/assets');
define('BOOKS_URL',   ASSETS_URL . '/books');
define('CSS_URL',     ASSETS_URL . '/css');

/* ---- Currency / shipping ---- */
define('CURRENCY',          '₱');
define('FREE_SHIPPING_MIN', 0);      // 0 = always free
define('SHIPPING_FEE',      0.00);

/* ---- Environment ---- */
define('DEBUG', true);               // set false when live

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/* ---- Timezone ---- */
date_default_timezone_set('Asia/Manila');