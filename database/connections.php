<?php
/**
 * connections.php – JCP Bookworks
 * Opens a PDO connection to MySQL, exposed as $pdo.
 * Include AFTER config.php.
 */

require_once __DIR__ . '/config.php';

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw on error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // rows as arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                    // real prepared statements
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    if (DEBUG) {
        die('DB connection failed: ' . $e->getMessage());
    }
    die('Database is temporarily unavailable. Please try again later.');
}