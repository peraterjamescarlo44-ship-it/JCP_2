<?php require_once __DIR__ . '/database/config.php'; require_once __DIR__ . '/database/functions.php'; ensureDataFiles(); ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Blogs | JCP Bookworks</title><link rel="stylesheet" href="assets/css/style.css"></head><body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main>
<section class="blog-hero" style="background-image:linear-gradient(rgba(0,0,0,.72),rgba(0,0,0,.72)),url('assets/images/blog-bg.png')">
<h1>BLOGS</h1>
<div class="blog-feature"><img src="assets/images/blog-feature.png"><div><p class="eyebrow light">OUR STAFF'S FAVORITE SUMMER THRILLERS</p><p>From locked-room mysteries to psychological twist-ends, here’s what we couldn’t put down this August.</p><a href="#article" class="btn small">READ ARTICLE</a></div></div>
</section>
<section class="section" id="article"><div class="section-title"><p class="eyebrow">JCP BOOKWORKS JOURNAL</p><h2>Stories, Ideas & Reading Guides</h2></div><div class="blog-grid">
<article><img src="assets/images/blog-feature.png"><h3>How to Find Your Next Great Read</h3><p>Simple ways to choose your next book based on your mood, interests, and reading goals.</p></article>
<article><img src="assets/images/reading.png"><h3>Why Reading Still Matters</h3><p>A calm reading routine can make space for curiosity, imagination, and learning.</p></article>
<article><img src="assets/images/next-read.png"><h3>Build Your Personal Bookshelf</h3><p>Start a collection with books you will actually want to return to.</p></article>
</div></section>
</main><?php include __DIR__ . '/includes/footer.php'; ?></body></html>
