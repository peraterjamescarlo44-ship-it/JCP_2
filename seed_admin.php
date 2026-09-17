<?php
require_once __DIR__ . '/database/functions.php';
require_once __DIR__ . '/database/admin_functions.php';

if (adminCount() > 0) {
    die('An admin already exists. Delete this file.');
}

$result = createAdmin('Admin', 'admin@jcpbookworks.test', 'Admin123456789', 'super');

if ($result['success']) {
    echo "✅ Admin created!<br><br>";
    echo "Username: <strong>Admin</strong><br>";
    echo "Password: <strong>Admin123456789</strong><br>";
    echo '<br><a href="login.php">→ Go to login</a>';
    echo '<br><br>⚠️ DELETE this file (seed_admin.php) now.';
} else {
    echo '❌ ' . $result['error'];
}