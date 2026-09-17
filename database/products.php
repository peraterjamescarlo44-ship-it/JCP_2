<?php
/**
 * database/products.php – JCP Bookworks
 * Flat-file product CRUD. No SQL needed.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
   FILE HELPERS
   ============================================================ */
function productsFile(): string
{
    $dir = __DIR__ . '/../data';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir . '/products.json';
}

function ensureProductsFile(): void
{
    if (!file_exists(productsFile())) {
        file_put_contents(productsFile(), json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
    }
}

function getProducts(): array
{
    ensureProductsFile();

    $raw = @file_get_contents(productsFile());
    $data = ($raw === false || trim($raw) === '') ? [] : json_decode($raw, true);

    // If JSON is invalid or empty → seed with defaults
    if (!is_array($data) || empty($data)) {
        $data = defaultProducts();
        saveProducts($data);
    }

    // Normalize each record
    $clean = [];
    foreach ($data as $p) {
        if (!is_array($p)) continue;

        // Skip records missing essential keys
        if (!isset($p['title']) || $p['title'] === '') continue;

        $clean[] = [
            'id'       => (int)($p['id']       ?? 0),
            'title'    => (string)$p['title'],
            'author'   => (string)($p['author']   ?? ''),
            'price'    => (float)($p['price']     ?? 0),
            'image'    => (string)($p['image']    ?? ''),
            'category' => (string)($p['category'] ?? ''),
        ];
    }

    // If all records were malformed → fall back to defaults
    if (empty($clean)) {
        $clean = defaultProducts();
        saveProducts($clean);
    }

    return $clean;
}

function defaultProducts(): array
{
    return [
        ['id'=>1,'title'=>'Atomic Habits','author'=>'James Clear','price'=>1123.12,'image'=>'atomic-habits.png','category'=>'Self-Help'],
        ['id'=>2,'title'=>'The Psychology of Money','author'=>'Morgan Housel','price'=>599.00,'image'=>'psychology_money.png','category'=>'Non-Fiction'],
        ['id'=>3,'title'=>'It Ends With Us','author'=>'Colleen Hoover','price'=>592.55,'image'=>'it-ends-with-us.png','category'=>'Fiction'],
        ['id'=>4,'title'=>'Rich Dad Poor Dad','author'=>'Robert Kiyosaki','price'=>599.00,'image'=>'rich-dad.png','category'=>'Non-Fiction'],
        ['id'=>5,'title'=>'The Subtle Art of Not Giving a F*ck','author'=>'Mark Manson','price'=>499.00,'image'=>'subtle-art.png','category'=>'Self-Help'],
        ['id'=>6,'title'=>'The Alchemist','author'=>'Paulo Coelho','price'=>399.00,'image'=>'alchemist.png','category'=>'Fiction'],
        ['id'=>7,'title'=>'The Midnight Library','author'=>'Matt Haig','price'=>349.22,'image'=>'midnight.png','category'=>'Fiction'],
        ['id'=>8,'title'=>'The Mastercut','author'=>'JCP Collection','price'=>898.66,'image'=>'mastercut.png','category'=>'Collection'],
    ];
}

function saveProducts(array $products): bool
{
    ensureProductsFile();
    return file_put_contents(
        productsFile(),
        json_encode(array_values($products), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}

function getProduct(int $id): ?array
{
    foreach (getProducts() as $p) {
        if ((int)$p['id'] === $id) return $p;
    }
    return null;
}

/* ============================================================
   ADD PRODUCT
   ============================================================ */
function addProduct(array $data): array
{
    $title    = trim($data['title']    ?? '');
    $author   = trim($data['author']   ?? '');
    $price    = trim($data['price']    ?? '');
    $image    = trim($data['image']    ?? '');
    $category = trim($data['category'] ?? '');

    if (strlen($title) < 1)  return ['success' => false, 'error' => 'Title is required.'];
    if (strlen($author) < 1) return ['success' => false, 'error' => 'Author is required.'];
    if (!is_numeric($price) || (float)$price < 0) {
        return ['success' => false, 'error' => 'Please enter a valid price.'];
    }
    if ($image === '')   return ['success' => false, 'error' => 'Image filename is required.'];
    if ($category === '') return ['success' => false, 'error' => 'Category is required.'];

    $products = getProducts();

    // Generate next id
    $nextId = 1;
    foreach ($products as $p) {
        if ((int)$p['id'] >= $nextId) $nextId = (int)$p['id'] + 1;
    }

    $products[] = [
        'id'         => $nextId,
        'title'      => $title,
        'author'     => $author,
        'price'      => (float)$price,
        'image'      => $image,
        'category'   => $category,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    if (!saveProducts($products)) {
        return ['success' => false, 'error' => 'Could not save product. Check folder permissions.'];
    }
    return ['success' => true, 'id' => $nextId];
}

/* ============================================================
   DELETE PRODUCT
   ============================================================ */
function deleteProduct(int $id): array
{
    $products = getProducts();
    $found = false;
    foreach ($products as $i => $p) {
        if ((int)$p['id'] === $id) {
            unset($products[$i]);
            $found = true;
            break;
        }
    }
    if (!$found) return ['success' => false, 'error' => 'Product not found.'];
    saveProducts(array_values($products));
    return ['success' => true];
}

/* ============================================================
   UPDATE PRODUCT (bonus)
   ============================================================ */
function updateProduct(int $id, array $data): array
{
    $products = getProducts();
    $found = false;
    foreach ($products as $i => $p) {
        if ((int)$p['id'] === $id) {
            $products[$i]['title']    = trim($data['title']    ?? $p['title']);
            $products[$i]['author']   = trim($data['author']   ?? $p['author']);
            $products[$i]['price']    = (float)($data['price'] ?? $p['price']);
            $products[$i]['image']    = trim($data['image']    ?? $p['image']);
            $products[$i]['category'] = trim($data['category'] ?? $p['category']);
            $found = true;
            break;
        }
    }
    if (!$found) return ['success' => false, 'error' => 'Product not found.'];
    saveProducts($products);
    return ['success' => true];
}