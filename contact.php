<?php
require_once __DIR__ . '/database/functions.php';
ensureDataFiles();
$user=currentUser();
$error=''; $success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $message=trim($_POST['message']??'');
    if(strlen($name)<2) $error='Please enter your name.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $error='Please enter a valid email.';
    elseif(strlen($message)<10) $error='Please enter at least 10 characters in your message.';
    elseif(!saveMessage($name,$email,$message)) $error='We could not save your message. Please try again.';
    else $success='Thank you! Your message has been received.';
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Contact | JCP Bookworks</title><link rel="stylesheet" href="assets/css/style.css"></head><body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main>
<section class="page-hero"><h1>CONTACT US</h1><p>We would love to hear from you.</p></section>
<section class="contact-section">
<div class="contact-person"><img src="assets/images/contact-reader.png" alt="Reader with books"></div>
<div class="contact-info"><p class="eyebrow light">GET IN TOUCH</p><h2>CONTACT US</h2><p>Email: jcpbookstore@gmail.com</p><p>TEL NUM: 415-2355</p><p>Facebook: JCP BOOKSTORE</p><p>Instagram: @jcp_bkstr</p><p>Discord: JCP_CustomerService</p><p>X: jjcpbkstr</p></div>
<div class="contact-form-wrap">
<?php if($error): ?><div class="alert error"><?= clean($error) ?></div><?php endif; ?>
<?php if($success): ?><div class="alert success"><?= clean($success) ?></div><?php endif; ?>
<form method="POST" class="contact-form">
<input name="name" placeholder="Full Name" value="<?= clean($user['name'] ?? $_POST['name'] ?? '') ?>" required>
<input type="email" name="email" placeholder="Email Address" value="<?= clean($user['email'] ?? $_POST['email'] ?? '') ?>" required>
<textarea name="message" rows="6" placeholder="Your message..." required><?= clean($_POST['message'] ?? '') ?></textarea>
<button class="btn" type="submit">SEND MESSAGE</button>
</form>
</div>
</section>
</main><?php include __DIR__ . '/includes/footer.php'; ?></body></html>
