<?php
/**
 * admin_logout.php – JCP Bookworks
 * Logs out the admin and redirects to login.
 */
require_once __DIR__ . '/database/functions.php';
require_once __DIR__ . '/database/admin_functions.php';

logoutAdmin();

header('Location: admin_login.php');
exit;