<?php
require_once __DIR__ . '/database/config.php';
require_once __DIR__ . '/database/functions.php';
require_once __DIR__ . '/database/admin_functions.php';

ensureDataFiles();

// If admin is logged in, send them to their dashboard
if (isAdminLoggedIn()) {
    redirect('admin.php');
}

requireLogin();
if (!isLoggedIn()) redirect('login.php');
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>My Account | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main>
    <section class="page-hero"><h1>MY ACCOUNT</h1><p>Home › Account</p></section>
    <section class="section account-card">
        <div class="account-avatar">👤︎</div>
        <div>
            <p class="eyebrow">WELCOME BACK</p>
            <h2><?= clean($user['name']) ?></h2>
            <p><?= clean($user['email']) ?></p>
            <p class="account-note">Your account is active. You can continue shopping or sign out below.</p>
            <a class="btn" href="shop.php">CONTINUE SHOPPING</a>
            <a class="btn outline" href="logout.php">LOGOUT</a>
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>