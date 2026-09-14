<?php
require_once __DIR__ . '/database/functions.php';
require_once __DIR__ . '/includes/header.php';
ensureDataFiles();
if (isLoggedIn()) redirect('account.php');
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password === '') {
        $error = 'Please enter your password.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) redirect('account.php');
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Login | JCP Bookworks</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="auth-body">
<div class="auth-page" style="background-image:url('assets/images/auth.png')">
<div class="auth-overlay"></div>
<div class="auth-content">
<a href="index.php" class="auth-logo"><span>JCP</span><small>BOOKWORKS</small></a>
<h1>WELCOME BACK!</h1><p>Login to your account</p>
<?php if($error): ?><div class="alert error"><?= clean($error) ?></div><?php endif; ?>
<form method="POST" class="auth-form" novalidate>
<label>Email Address<input type="email" name="email" value="<?= clean($_POST['email'] ?? '') ?>" required></label>
<label>Password<input type="password" name="password" required></label>
<button class="btn" type="submit">LOGIN</button>
</form>
<p class="auth-switch">Don't have an account? <a href="register.php">Register here</a></p>
<a class="back-home" href="index.php">← Back to store</a>
</div></div>
</body></html>
