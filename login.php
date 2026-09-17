<?php
require_once __DIR__ . '/database/functions.php';       // clean(), session, user login
require_once __DIR__ . '/database/admin_functions.php'; // admin login
require_once __DIR__ . '/includes/header.php';

ensureDataFiles();

// If already logged in → go to correct dashboard
if (isAdminLoggedIn()) {
    redirect('admin.php');
}
if (isLoggedIn()) {
    redirect('account.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['email'] ?? '');  // accepts email OR username
    $password   = $_POST['password'] ?? '';

    if ($identifier === '') {
        $error = 'Please enter your email or username.';
    } elseif ($password === '') {
        $error = 'Please enter your password.';
    } else {
        // ------------------------------------------------------------
        // 1) Try ADMIN login first
        // ------------------------------------------------------------
        $adminResult = loginAdmin($identifier, $password);

        if (!empty($adminResult['success'])) {
            redirect('admin.php');   // ← admin goes to admin dashboard
        }

        // ------------------------------------------------------------
        // 2) Fall back to USER login
        // ------------------------------------------------------------
        $userResult = loginUser($identifier, $password);

        if (!empty($userResult['success'])) {
            redirect('account.php'); // ← normal user goes to account
        }

        // If both fail → show a generic message (don't leak which one failed)
        $error = 'Invalid credentials. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Login | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-page" style="background-image:url('assets/images/auth.png')">
    <div class="auth-overlay"></div>
    <div class="auth-content">
        <a href="index.php" class="auth-logo"><span>JCP</span><small>BOOKWORKS</small></a>
        <h1>WELCOME BACK!</h1>
        <p>Login to your account</p>

        <?php if ($error): ?>
            <div class="alert error"><?= clean($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form" novalidate>
            <label>Email or Username
                <input type="text" name="email" value="<?= clean($_POST['email'] ?? '') ?>" required>
            </label>
            <label>Password
                <input type="password" name="password" required>
            </label>
            <button class="btn" type="submit">LOGIN</button>
        </form>

        <p class="auth-switch">Don't have an account? <a href="register.php">Register here</a></p>
        <a class="back-home" href="index.php">← Back to store</a>
    </div>
</div>
</body>
</html>