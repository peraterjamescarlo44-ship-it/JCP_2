<?php
require_once __DIR__ . '/database/functions.php';
require_once __DIR__ . '/database/admin_functions.php';

if (isAdminLoggedIn()) {
    header('Location: admin.php');
    exit;
}
if (adminCount() === 0) {
    header('Location: admin_setup.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $result = loginAdmin($email, $pass);
    if ($result['success']) {
        header('Location: admin.php');
        exit;
    }
    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-page" style="background-image:url('assets/images/auth.png')">
    <div class="auth-overlay"></div>
    <div class="auth-content">
        <a href="index.php" class="auth-logo"><span>JCP</span><small>BOOKWORKS</small></a>
        <h1>ADMIN LOGIN</h1>
        <p>Restricted area — admins only</p>

        <?php if ($error): ?>
            <div class="alert error"><?= clean($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form" novalidate>
            <label>Email Address
                <input type="email" name="email" value="<?= clean($_POST['email'] ?? '') ?>" required>
            </label>
            <label>Password
                <input type="password" name="password" required>
            </label>
            <button class="btn" type="submit">LOGIN AS ADMIN</button>
        </form>

        <p class="auth-switch">Not an admin? <a href="login.php">User login</a></p>
        <a class="back-home" href="index.php">← Back to store</a>
    </div>
</div>
</body>
</html>