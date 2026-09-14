<?php
require_once __DIR__ . '/database/functions.php';
require_once __DIR__ . '/includes/header.php';
ensureDataFiles();
if (isLoggedIn()) redirect('account.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($name) < 2) $error = 'Please enter your full name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email address.';
    elseif (strlen($password) < 6) $error = 'Password must be at least 6 characters.';
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    else {
        $result = registerUser($name, $email, $password);
        if ($result['success']) {
            $login = loginUser($email, $password);
            if ($login['success']) redirect('account.php');
            $error = 'Account created. Please login.';
        } else $error = $result['error'];
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Register | JCP Bookworks</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="auth-body">
<div class="auth-page" style="background-image:url('assets/images/auth.png')">
<div class="auth-overlay"></div>
<div class="auth-content register">
<a href="index.php" class="auth-logo"><span>JCP</span><small>BOOKWORKS</small></a>
<h1>WELCOME</h1><p>Create a new account</p>
<?php if($error): ?><div class="alert error"><?= clean($error) ?></div><?php endif; ?>
<form method="POST" class="auth-form" novalidate>
<label>Full Name<input type="text" name="name" value="<?= clean($_POST['name'] ?? '') ?>" required></label>
<label>Email Address<input type="email" name="email" value="<?= clean($_POST['email'] ?? '') ?>" required></label>
<label>Password<input type="password" name="password" required></label>
<label>Confirm Password<input type="password" name="confirm_password" required></label>
<label class="terms"><input type="checkbox" required> I agree to the Terms & Conditions</label>
<button class="btn" type="submit">REGISTER</button>
</form>
<p class="auth-switch">Already have an account? <a href="login.php">Login here</a></p>
<a class="back-home" href="index.php">← Back to store</a>
</div></div>
</body></html>
