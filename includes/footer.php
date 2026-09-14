<?php
// No PHP logic needed – just HTML
?>

<!-- ============================================================
     FLOATING NEWSLETTER (sticks on scroll, has close button)
     ============================================================ -->
<div id="floatingNewsletter" class="floating-newsletter">
    <button class="close-newsletter" id="closeNewsletter" aria-label="Close newsletter">✕</button>
    <div class="newsletter-icon">✉</div>
    <div class="newsletter-copy">
        <h3>Get Updates and Exclusive Deals!</h3>
        <p>Join our newsletter and be the first to know!</p>
    </div>
    <form id="newsletterForm" class="newsletter-form">
        <input type="email" id="newsletterEmail" placeholder="Email address" required>
        <button type="submit">SUBSCRIBE</button>
    </form>
</div>

<!-- ============================================================
     DRAGGABLE TOGGLE BUTTON (shows when newsletter is closed)
     ============================================================ -->
<button id="newsletterToggle" class="newsletter-toggle" aria-label="Open newsletter" style="display:none;">
    ▲
</button>

<!-- ============================================================
     MAIN FOOTER
     ============================================================ -->
<footer class="footer">
    <div class="footer-brand">
        <div class="brand-main">JCP</div>
        <div class="brand-sub">BOOKWORKS</div>
        <p>Books that inspire.</p>
    </div>
    <div>
        <h4>SHOP</h4>
        <a href="shop.php">All Books</a>
        <a href="shop.php">Best Sellers</a>
        <a href="shop.php?new=1">New Arrivals</a>
        <a href="shop.php?sale=1">On Sale</a>
    </div>
    <div>
        <h4>CUSTOMER CARE</h4>
        <a href="contact.php">Shopping and Delivery</a>
        <a href="contact.php">Returns & Exchanges</a>
        <a href="contact.php">FAQs</a>
        <a href="contact.php">Contact Us</a>
    </div>
    <div>
        <h4>COMPANY</h4>
        <a href="index.php#about">About Us</a>
        <a href="blog.php">Blog</a>
        <a href="contact.php">Careers</a>
        <a href="contact.php">Terms & Conditions</a>
    </div>
    <div class="copyright">© <?= date('Y') ?> JCP Bookworks. All rights reserved.</div>
</footer>

<div id="toast" class="toast"></div>
<script src="assets/js/main.js"></script>