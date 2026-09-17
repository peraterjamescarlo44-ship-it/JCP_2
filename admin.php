<?php
require_once __DIR__ . '/database/functions.php';
require_once __DIR__ . '/database/admin_functions.php';
require_once __DIR__ . '/database/products.php';

requireAdmin();
$admin    = currentAdmin();
$admins   = getAdmins();
$products = getProducts();
$users    = getAllUsers();

/* ---------- HANDLE DELETE PRODUCT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $result = deleteProduct((int)$_POST['delete_product']);
    $_SESSION['flash'] = $result['success']
        ? ['type' => 'success', 'msg' => 'Product deleted.']
        : ['type' => 'error',   'msg' => $result['error']];
    header('Location: admin.php');
    exit;
}

/* ---------- HANDLE ADD PRODUCT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $result = addProduct($_POST);
    $_SESSION['flash'] = $result['success']
        ? ['type' => 'success', 'msg' => 'Product added successfully.']
        : ['type' => 'error',   'msg' => $result['error']];
    header('Location: admin.php');
    exit;
}

/* ---------- HANDLE DELETE USER ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $rawLine = base64_decode($_POST['delete_user']);
    $result  = deleteUserByLine($rawLine);
    $_SESSION['flash'] = $result['success']
        ? ['type' => 'success', 'msg' => 'User deleted.']
        : ['type' => 'error',   'msg' => $result['error']];
    header('Location: admin.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .tab-panel > a[name] {
    display: block;
    position: relative;
    top: -100px;       /* offset for sticky header */
    visibility: hidden;
}
    </style>
</head>
<body>

<!-- ⬇️⬇️⬇️ INCLUDE HEADER HERE ⬇️⬇️⬇️ -->
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="admin-wrap">
    <!-- ... all your admin dashboard content ... -->
</div>

<!-- TABS SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.tab-btn');
    const panels  = document.querySelectorAll('.tab-panel');

    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.dataset.tab;
            buttons.forEach(b => b.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(target)?.classList.add('active');
        });
    });
});
</script>
</body>
</html>

<?php
require_once __DIR__ . '/database/functions.php';
require_once __DIR__ . '/database/admin_functions.php';
require_once __DIR__ . '/database/products.php';

requireAdmin();
$admin    = currentAdmin();
$admins   = getAdmins();
$products = getProducts();
$users    = getAllUsers();

/* ---------- HANDLE DELETE PRODUCT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $result = deleteProduct((int)$_POST['delete_product']);
    $_SESSION['flash'] = $result['success']
        ? ['type' => 'success', 'msg' => 'Product deleted.']
        : ['type' => 'error',   'msg' => $result['error']];
    header('Location: admin.php');
    exit;
}

/* ---------- HANDLE ADD PRODUCT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $result = addProduct($_POST);
    $_SESSION['flash'] = $result['success']
        ? ['type' => 'success', 'msg' => 'Product added successfully.']
        : ['type' => 'error',   'msg' => $result['error']];
    header('Location: admin.php');
    exit;
}

/* ---------- HANDLE DELETE USER ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $rawLine = base64_decode($_POST['delete_user']);
    $result  = deleteUserByLine($rawLine);
    $_SESSION['flash'] = $result['success']
        ? ['type' => 'success', 'msg' => 'User deleted.']
        : ['type' => 'error',   'msg' => $result['error']];
    header('Location: admin.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | JCP Bookworks</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .admin-wrap { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        .admin-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 18px 24px; background: var(--green); color: #fff;
            border-radius: 12px; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
        }
        .admin-header h1 { font-family: Georgia, serif; font-size: 22px; letter-spacing: 2px; }
        .admin-header .who { font-size: 12px; opacity: .85; margin-top: 4px; }
        .admin-logout {
            background: #f5dede; color: #7d2c2c; padding: 8px 16px;
            border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none;
        }
        .admin-logout:hover { background: #e8bebe; }
        .role-badge {
            display: inline-block; padding: 2px 10px; border-radius: 12px;
            font-size: 10px; letter-spacing: 1px; text-transform: uppercase;
            background: #dfeee1; color: #28562f; margin-left: 6px;
        }

        /* Tabs */
        .tabs {
            display: flex; gap: 6px; margin-bottom: 22px;
            border-bottom: 2px solid var(--line); flex-wrap: wrap;
        }
        .tabs button {
            background: none; border: none; padding: 10px 20px;
            font-size: 12px; letter-spacing: 1.5px; font-weight: 600;
            cursor: pointer; color: var(--muted);
            border-bottom: 3px solid transparent; margin-bottom: -2px;
            transition: .2s;
        }
        .tabs button:hover { color: var(--green); }
        .tabs button.active {
            color: var(--green);
            border-bottom-color: var(--green);
        }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* Cards */
        .admin-cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px; margin-bottom: 30px;
        }
        .admin-card {
            background: #fff; border: 1px solid var(--line);
            border-radius: 12px; padding: 20px;
        }
        .admin-card h3 { font-size: 11px; letter-spacing: 2px; color: var(--muted); margin-bottom: 8px; }
        .admin-card strong { font-size: 26px; color: var(--green); font-family: Georgia, serif; }

        /* Panels */
        .panel {
            background: #fff; border: 1px solid var(--line);
            border-radius: 12px; padding: 24px; margin-bottom: 30px;
        }
        .panel h2 {
            font-family: Georgia, serif; font-weight: 500;
            font-size: 22px; margin-bottom: 18px;
            border-bottom: 2px solid var(--green); padding-bottom: 8px;
        }

        /* Tables */
        .prod-grid {
            display: grid; grid-template-columns: 80px 2fr 1.5fr 1fr 1fr 90px;
            gap: 10px; padding: 10px 0; align-items: center;
            border-bottom: 1px solid #eee; font-size: 13px;
        }
        .prod-grid.head {
            font-weight: 600; color: var(--muted); font-size: 10px;
            letter-spacing: 1px; text-transform: uppercase;
        }
        .prod-grid img {
            width: 60px; height: 60px; object-fit: contain;
            background: #f7f7f4; border-radius: 6px; padding: 4px;
        }
        .prod-grid.user-cols {
            grid-template-columns: 50px 2fr 2fr 1.5fr 100px;
        }
        .prod-grid.admin-cols {
            grid-template-columns: 50px 2fr 2fr 1fr 1fr 1fr;
        }
        .btn-del {
            background: #fee2e2; color: #b91c1c; border: none;
            padding: 6px 12px; border-radius: 6px; font-size: 12px;
            font-weight: 600; cursor: pointer; transition: .2s;
        }
        .btn-del:hover { background: #fecaca; }

        .add-form {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px; margin-top: 6px;
        }
        .add-form input, .add-form select {
            padding: 10px 12px; border: 1px solid var(--line);
            border-radius: 6px; font-size: 13px; outline: none;
        }
        .add-form input:focus, .add-form select:focus { border-color: var(--green); }
        .add-form button {
            grid-column: 1 / -1; padding: 12px;
            background: var(--green); color: #fff; border: none;
            border-radius: 6px; font-weight: 600; letter-spacing: 1px; cursor: pointer;
        }
        .add-form button:hover { background: var(--green2); }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; }
        .alert.success { background: #dfeee1; color: #28562f; border: 1px solid #bcd5bf; }
        .alert.error   { background: #f5dede; color: #7d2c2c; border: 1px solid #e8bebe; }

        .avatar-mini {
            width: 36px; height: 36px; border-radius: 50%;
            background: #e4e8e4; color: var(--green);
            display: flex; align-items: center; justify-content: center;
            font-family: Georgia, serif; font-size: 16px; font-weight: 600;
        }

        .empty-note { color: #888; font-size: 13px; padding: 14px 0; }

        @media (max-width: 720px) {
            .prod-grid { grid-template-columns: 60px 1fr 1fr 70px; font-size: 11px; }
            .prod-grid > div:nth-child(3),
            .prod-grid > div:nth-child(5) { display: none; }
            .prod-grid.user-cols { grid-template-columns: 40px 1fr 1fr 80px; }
            .prod-grid.user-cols > div:nth-child(4) { display: none; }
            .prod-grid.admin-cols { grid-template-columns: 40px 1fr 1fr 80px; }
            .prod-grid.admin-cols > div:nth-child(4),
            .prod-grid.admin-cols > div:nth-child(5) { display: none; }
        }
    </style>
</head>
<body>
<div class="admin-wrap">

    <!-- HEADER -->
    <div class="admin-header">
        <div>
            <h1>ADMIN DASHBOARD</h1>
            <div class="who">
                Logged in as <strong><?= clean($admin['name']) ?></strong>
                <span class="role-badge"><?= clean($admin['role']) ?></span>
            </div>
        </div>
        <a href="admin_logout.php" class="admin-logout">LOGOUT</a>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
            <?= clean($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <div class="admin-cards">
        <div class="admin-card"><h3>PRODUCTS</h3><strong><?= count($products) ?></strong></div>
        <div class="admin-card"><h3>USERS</h3><strong><?= count($users) ?></strong></div>
        <div class="admin-card"><h3>ADMINS</h3><strong><?= count($admins) ?></strong></div>
        <div class="admin-card"><h3>MESSAGES</h3><strong><?= file_exists(__DIR__.'/data/messages.txt')
            ? count(file(__DIR__.'/data/messages.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES)) : 0 ?></strong></div>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="tab-products">📚 PRODUCTS</button>
        <button class="tab-btn" data-tab="tab-users">👥 USERS</button>
        <button class="tab-btn" data-tab="tab-admins">🔑 ADMINS</button>
    </div>

    <!-- ============ PRODUCTS TAB ============ -->
<div id="tab-products" class="tab-panel active">
    <a name="products" id="products"></a>

        <div class="panel">
            <h2>➕ Add New Product</h2>
            <form method="POST" class="add-form">
                <input type="text"   name="title"    placeholder="Book title" required>
                <input type="text"   name="author"   placeholder="Author" required>
                <input type="number" name="price"    placeholder="Price (e.g. 599.00)" step="0.01" min="0" required>
                <input type="text"   name="image"    placeholder="Image filename (e.g. book.png)" required>
                <select name="category" required>
                    <option value="">-- Category --</option>
                    <option>Fiction</option>
                    <option>Non-Fiction</option>
                    <option>Self-Help</option>
                    <option>Children</option>
                    <option>Collection</option>
                    <option>Academic</option>
                    <option>Comics</option>
                    <option>Poetry</option>
                </select>
                <button type="submit" name="add_product" value="1">ADD PRODUCT</button>
            </form>
        </div>

        <div class="panel">
            <h2>📚 All Products (<?= count($products) ?>)</h2>

            <?php if (empty($products)): ?>
                <p class="empty-note">No products yet. Add one above.</p>
            <?php else: ?>
                <div class="prod-grid head">
                    <div>Image</div>
                    <div>Title</div>
                    <div>Author</div>
                    <div>Price</div>
                    <div>Category</div>
                    <div>Action</div>
                </div>
                <?php foreach ($products as $p): 
    // Safe defaults for missing keys
    $p_id       = $p['id']       ?? 0;
    $p_title    = $p['title']    ?? '(untitled)';
    $p_author   = $p['author']   ?? '—';
    $p_price    = isset($p['price']) ? (float)$p['price'] : 0;
    $p_image    = $p['image']    ?? '';
    $p_category = $p['category'] ?? '—';
?>
    <div class="prod-grid">
        <div>
            <?php if ($p_image !== ''): ?>
                <img src="assets/books/<?= clean($p_image) ?>"
                     alt="<?= clean($p_title) ?>"
                     onerror="this.src='assets/images/about.png'">
            <?php else: ?>
                <img src="assets/images/about.png" alt="No image">
            <?php endif; ?>
        </div>
        <div><strong><?= clean($p_title) ?></strong></div>
        <div><?= clean($p_author) ?></div>
        <div>₱<?= number_format($p_price, 2) ?></div>
        <div><?= clean($p_category) ?></div>
        <div>
            <form method="POST" onsubmit="return confirm('Delete this product?')" style="display:inline;">
                <input type="hidden" name="delete_product" value="<?= (int)$p_id ?>">
                <button class="btn-del" type="submit">🗑 Delete</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============ USERS TAB ============ -->
<div id="tab-users" class="tab-panel">
    <a name="users" id="users"></a>

        <div class="panel">
            <h2>👥 Registered Users (<?= count($users) ?>)</h2>

            <?php if (empty($users)): ?>
                <p class="empty-note">No registered users yet.</p>
            <?php else: ?>
                <div class="prod-grid user-cols head">
                    <div>#</div>
                    <div>Name</div>
                    <div>Email</div>
                    <div>Registered</div>
                    <div>Action</div>
                </div>

                <?php foreach ($users as $u): ?>
                    <div class="prod-grid user-cols">
                        <div class="avatar-mini"><?= strtoupper(substr($u['name'] ?: 'U', 0, 1)) ?></div>
                        <div><strong><?= clean($u['name'] ?: '—') ?></strong></div>
                        <div><?= clean($u['email']) ?></div>
                        <div><?= clean($u['created_at'] ?: '—') ?></div>
                        <div>
                            <form method="POST" onsubmit="return confirm('Delete this user?')" style="display:inline;">
                                <input type="hidden" name="delete_user"
                                       value="<?= base64_encode($u['raw']) ?>">
                                <button class="btn-del" type="submit">🗑 Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============ ADMINS TAB ============ -->
    <div id="tab-admins" class="tab-panel">
    <a name="admins" id="admins"></a>

        <div class="panel">
            <h2>🔑 All Admins (<?= count($admins) ?>)</h2>

            <?php if (empty($admins)): ?>
                <p class="empty-note">No admins found.</p>
            <?php else: ?>
                <div class="prod-grid admin-cols head">
                    <div>#</div><div>Name</div><div>Email</div>
                    <div>Role</div><div>Status</div><div>Last login</div>
                </div>

                <?php foreach ($admins as $a): ?>
                    <div class="prod-grid admin-cols">
                        <div><?= (int)$a['id'] ?></div>
                        <div><strong><?= clean($a['name']) ?></strong></div>
                        <div><?= clean($a['email']) ?></div>
                        <div><span class="role-badge"><?= clean($a['role']) ?></span></div>
                        <div><?= ((int)$a['is_active'] === 1) ? '✅ Active' : '⛔ Disabled' ?></div>
                        <div><?= clean($a['last_login'] ?? '—') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- TABS SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.tab-btn');
    const panels  = document.querySelectorAll('.tab-panel');

    // Map URL hash → tab id
    const hashMap = {
        '#products': 'tab-products',
        '#users':    'tab-users',
        '#admins':   'tab-admins',
    };

    function activateTab(tabId) {
        buttons.forEach(b => b.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));

        const panel = document.getElementById(tabId);
        if (panel) panel.classList.add('active');

        const btn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
        if (btn) btn.classList.add('active');
    }

    // Click handler
    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.dataset.tab;
            activateTab(target);
            // Update URL hash (without jumping)
            history.replaceState(null, '', '#' + target.replace('tab-', ''));
        });
    });

    // On page load, check hash
    const hash = window.location.hash.toLowerCase();
    if (hashMap[hash]) {
        activateTab(hashMap[hash]);
        // Slight scroll into view so user sees the tab
        setTimeout(() => {
            document.querySelector('.tabs')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 60);
    }
});
</script>
</body>
</html>