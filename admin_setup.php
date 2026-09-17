<?php
/**
 * admin_setup.php – one-time creation of first super admin.
 * Auto-locks when an admin already exists.
 */
require_once __DIR__ . '/database/functions.php';       // clean(), session
require_once __DIR__ . '/database/admin_functions.php';

$locked  = adminCount() > 0;
$error   = '';
$success = '';

if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $conf  = $_POST['confirm_password'] ?? '';

    if ($pass !== $conf) {
        $error = 'Passwords do not match.';
    } else {
        $result = createAdmin($name, $email, $pass, 'super');
        if ($result['success']) {
            $success = 'Super admin created successfully. You can now log in.';
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-page" style="background-image:url('assets/images/auth.png')">
    <div class="auth-overlay"></div>
    <div class="auth-content register">
        <a href="index.php" class="auth-logo"><span>JCP</span><small>BOOKWORKS</small></a>

        <?php if ($locked): ?>
            <h1>🔒 SETUP LOCKED</h1>
            <p>An admin already exists. Please log in below.</p>
            <a class="btn" href="admin_login.php">GO TO ADMIN LOGIN</a>
        <?php else: ?>
            <h1>ADMIN SETUP</h1>
            <p>Create the first super admin account</p>

            <?php if ($error): ?>
                <div class="alert error"><?= clean($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert success"><?= clean($success) ?></div>
                <p style="margin-top:12px;">
                    <a class="btn" href="admin_login.php">GO TO ADMIN LOGIN</a>
                </p>
            <?php else: ?>
                <form method="POST" class="auth-form" novalidate>
                    <label>Full Name
                        <input type="text" name="name" required>
                    </label>
                    <label>Email Address
                        <input type="email" name="email" required>
                    </label>
                    <label>Password (min 8 chars)
                        <input type="password" name="password" minlength="8" required>
                    </label>
                    <label>Confirm Password
                        <input type="password" name="confirm_password" minlength="8" required>
                    </label>
                    <button class="btn" type="submit">CREATE SUPER ADMIN</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <a class="back-home" href="index.php">← Back to store</a>
    </div>
</div>
</body>
</html>