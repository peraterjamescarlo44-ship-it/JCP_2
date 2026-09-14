<?php
require_once __DIR__ . '/database/functions.php';
requireLogin();

ensureDataFiles();

$categories = [
    'Fiction'     => ['fiction-icon.png',    120],
    'Non-Fiction' => ['nonfiction-icon.png',  95],
    'Self-Help'   => ['selfhelp-icon.png',    60],
    'Academic'    => ['children-icon.png',   150],
    'Children'    => ['children-icon.png',    80],
    'Comics'      => ['collection-icon.png',  70],
    'Poetry'      => ['poetry.png',           40],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Categories | JCP Bookworks</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main>
<section class="page-hero">
    <h1>CATEGORIES</h1>
    <p>Home › Categories</p>
</section>

<section class="section categories-page">
    <div class="section-title">
        <p class="eyebrow">EXPLORE OUR COLLECTION</p>
        <h2>ALL CATEGORIES</h2>
    </div>

    <div class="category-large-grid">
    <?php foreach ($categories as $name => $info): ?>
        <a href="shop.php?category=<?= urlencode($name) ?>" class="large-category">
            <div><img src="assets/icons/<?= clean($info[0]) ?>" alt=""></div>
            <h3><?= clean($name) ?></h3>
            <span>(<?= (int)$info[1] ?> Books)</span>
        </a>
    <?php endforeach; ?>
    </div>
</section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>