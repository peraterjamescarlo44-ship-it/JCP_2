<?php
/**
 * index.php – JCP Bookworks Homepage
 *
 * Displays the main landing page with hero, service highlights,
 * category navigation, best-selling books, about section,
 * discover banner, and rotating testimonials.
 */

require_once __DIR__ . '/database/functions.php';

// No need to call ensureDataFiles() here – it’s not used on this page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JCP Bookworks | Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>

    <!-- Hero Section -->
    <section class="hero-home">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <p class="eyebrow light">WELCOME TO</p>
            <h1>JCP BOOKWORKS</h1>
            <p>Great books, fast service, and good vibes arrive in perfect condition.</p>
            <a class="btn" href="shop.php">SHOP NOW</a>
        </div>
    </section>

    <!-- Service Row -->
    <section class="service-row">
        <div>
            <img src="assets/icons/wide-icon.png" alt="Wide collection icon">
            <div>
                <b>WIDE COLLECTION</b>
                <span>Thousands of titles</span>
            </div>
        </div>
        <div>
            <img src="assets/icons/collection-icon.png" alt="Quality books icon">
            <div>
                <b>QUALITY BOOKS</b>
                <span>Handpicked with care</span>
            </div>
        </div>
        <div>
            <img src="assets/icons/delivery-icon.png" alt="Fast delivery icon">
            <div>
                <b>FAST DELIVERY</b>
                <span>Quick and reliable nationwide</span>
            </div>
        </div>
        <div>
            <img src="assets/icons/search-icon.png" alt="Secure payment icon">
            <div>
                <b>SECURE PAYMENT</b>
                <span>Safe, easy, hassle-free</span>
            </div>
        </div>
    </section>

    <!-- Shop by Category -->
    <section class="section categories-home">
        <div class="section-title">
            <p class="eyebrow">EXPLORE</p>
            <h2>SHOP BY CATEGORY</h2>
        </div>
        <div class="category-grid">
            <?php
            $categories = [
                ['Fiction', 'fiction-icon.png'],
                ['Non-Fiction', 'nonfiction-icon.png'],
                ['Self-Help', 'selfhelp-icon.png'],
                ['Children', 'children-icon.png'],
                ['Collection', 'collection-icon.png'],
            ];
            foreach ($categories as $cat) :
                $catName = clean($cat[0]);
                $catIcon = clean($cat[1]);
            ?>
            <a class="category-card" href="categories.php?category=<?= urlencode($catName) ?>">
                <div class="category-img">
                    <img src="assets/icons/<?= $catIcon ?>" alt="<?= $catName ?> category icon">
                </div>
                <h3><?= $catName ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

   <!-- About Section -->
<section class="about-section" id="about">
    <div class="about-photo">
        <img src="assets/images/about.png" alt="JCP Bookworks reading area">
    </div>
    <div class="about-copy">
        <p class="eyebrow">ABOUT JCP BOOKWORKS</p>
        <h2>Books that inspire. Stories that stay.</h2>
        <p>We believe that books have the power to change lives. At JCP Bookworks, we curate stories and knowledge that inspire, educate, and entertain.</p>
        
        <!-- Hidden detailed content -->
        <div id="aboutDetails" class="about-details" style="display: none;">
            <p>Since 2010, JCP Bookworks has been a haven for readers across the country. Our shelves are filled with carefully selected titles from local and international authors. We host regular book clubs, author signings, and community reading programs. Our mission is to make every reader feel at home and to spark a lifelong love for stories.</p>
            <p>We also offer personalized recommendations, gift wrapping, and nationwide shipping. Come visit our cozy reading nook – coffee is always on us!</p>
        </div>
        
        <a href="#" id="readMoreBtn" class="btn" onclick="toggleAbout(event)">READ MORE</a>
    </div>
</section>
    </section>

    <!-- Best Sellers / Featured Books -->
    <section class="section featured" id="featured">
        <div class="section-title left">
            <p class="eyebrow">OUR PICKS</p>
            <h2>BEST SELLERS</h2>
            <a href="shop.php" class="view-all">View All →</a>
        </div>
        <div class="book-grid six">
            <?php
            $books = [
                [
                    'title'  => 'The Midnight Library',
                    'author' => 'Matt Haig',
                    'price'  => 'P349.22',
                    'cover'  => 'midnight.png',
                ],
                [
                    'title'  => 'Atomic Habits',
                    'author' => 'James Clear',
                    'price'  => 'P1123.12',
                    'cover'  => 'atomic-habits.png',
                ],
                [
                    'title'  => 'It Ends With Us',
                    'author' => 'Colleen Hoover',
                    'price'  => 'P592.55',
                    'cover'  => 'it-ends-with-us.png',
                ],
                [
                    'title'  => 'The Alchemist',
                    'author' => 'Paulo Coelho',
                    'price'  => 'P787.22',
                    'cover'  => 'alchemist.png',
                ],
                [
                    'title'  => 'The Mastercut',
                    'author' => 'JCP Collection',
                    'price'  => 'P898.66',
                    'cover'  => 'mastercut.png',
                ],
                [
                    'title'  => 'The Psychology of Money',
                    'author' => 'Morgan Housel',
                    'price'  => 'P599.00',
                    'cover'  => 'psychology_money.png', // corrected image
                ],
            ];

            foreach ($books as $book) :
                $title  = clean($book['title']);
                $author = clean($book['author']);
                $price  = clean($book['price']);
                $cover  = clean($book['cover']);
            ?>
            <article class="book-card">
                <a href="shop.php">
                    <div class="book-cover">
                        <img src="assets/books/<?= $cover ?>" alt="<?= $title ?> cover">
                    </div>
                </a>
                <h3><?= $title ?></h3>
                <p><?= $author ?></p>
                <button class="cart-btn"
                        data-title="<?= $title ?>"
                        data-price="<?= $price ?>">
                    ♧ <?= $price ?>
                </button>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Discover Banner -->
    <section class="discover-banner">
        <div class="discover-copy">
            <p class="eyebrow">FIND YOUR NEXT GREAT READ</p>
            <p>New arrivals, exclusive deals, and handpicked favorites updated weekly.</p>
            <a class="btn small" href="shop.php?new=1">EXPLORE NOW</a>
        </div>
        <img src="assets/images/next-read.png" alt="Books by a sunny window">
    </section>

    <!-- Testimonials -->
    <section class="testimonial section">
        <div class="section-title">
            <p class="eyebrow">WHAT READERS SAY</p>
            <h2>“</h2>
        </div>
        <blockquote id="quoteText">
            JCP Bookworks is my go-to Bookstore. Great selection, fast delivery, and books always arrive in perfect condition!
        </blockquote>
        <p id="quoteName">— Maria D.</p>
        <div class="dots">
            <button data-index="0" class="active" aria-label="Testimonial 1"></button>
            <button data-index="1" aria-label="Testimonial 2"></button>
            <button data-index="2" aria-label="Testimonial 3"></button>
            <button data-index="3" aria-label="Testimonial 4"></button>
        </div>
    </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>