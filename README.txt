JCP BOOKWORKS - FULL PHP/JAVASCRIPT/CSS WEBSITE

WHAT IS INCLUDED
- Home page based closely on the supplied JCP Bookworks mockups.
- Categories page.
- Bestsellers/shop page with filters, sorting, cart counter and session cart in browser localStorage.
- Working Register page using PHP file handling.
- Working Login page using PHP sessions and password_hash/password_verify.
- Logout page.
- Account page.
- Blog page based on the supplied blog mockup.
- Contact page with PHP file handling that saves messages to data/messages.txt.
- Error handling for invalid email, duplicate accounts, missing fields, password mismatch, and file errors.
- Responsive CSS for desktop and mobile.
- High-resolution embedded images extracted from the supplied PDF mockups, preserving the original visual assets as closely as possible.
- A generated HD design-reference image is also included at assets/images/generated_hd_reference.png.

IMPORTANT SECURITY NOTE
This is an academic/basic PHP file-handling project. It is not intended as a production authentication system. For a real public website, use a database, HTTPS, rate limiting, stronger account recovery, and a proper server configuration.

HOW TO RUN WITH XAMPP
1. Extract this folder into:
   C:\xampp\htdocs\
2. Start Apache in XAMPP.
3. Open:
   http://localhost/JCP_Bookworks_Full_Website/
4. Go to Register and create an account.
5. You will be logged in automatically.
6. Logout, then test Login using the same email/password.
7. Contact messages are stored in data/messages.txt.
8. Registered users are stored in data/users.txt with hashed passwords.

FILES
index.php
categories.php
shop.php
blog.php
contact.php
login.php
register.php
account.php
logout.php
includes/functions.php
includes/header.php
includes/footer.php
assets/css/style.css
assets/js/main.js
data/users.txt
data/messages.txt
