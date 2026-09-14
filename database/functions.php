<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DATA_DIR', __DIR__ . '/../data');
define('USERS_FILE', DATA_DIR . '/users.txt');
define('MESSAGES_FILE', DATA_DIR . '/messages.txt');

function ensureDataFiles() {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, '');
    }
    if (!file_exists(MESSAGES_FILE)) {
        file_put_contents(MESSAGES_FILE, '');
    }
}

function clean($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function redirect($page) {
    header('Location: ' . $page);
    exit;
}

function findUserByEmail($email) {
    ensureDataFiles();
    $email = strtolower(trim($email));
    $handle = fopen(USERS_FILE, 'r');

    if (!$handle) {
        return null;
    }

    while (($line = fgets($handle)) !== false) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = explode('|', $line);
        if (count($parts) >= 4 && strtolower(trim($parts[1])) === $email) {
            fclose($handle);
            return [
                'name' => $parts[0],
                'email' => $parts[1],
                'password' => $parts[2],
                'created_at' => $parts[3]
            ];
        }
    }

    fclose($handle);
    return null;
}

function registerUser($name, $email, $password) {
    ensureDataFiles();

    if (findUserByEmail($email)) {
        return ['success' => false, 'error' => 'An account with this email already exists.'];
    }

    $handle = fopen(USERS_FILE, 'a');
    if (!$handle) {
        return ['success' => false, 'error' => 'Unable to open the user file. Please try again.'];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $line = str_replace(['|', "\r", "\n"], '', $name) . '|' .
            strtolower(trim($email)) . '|' .
            $hashedPassword . '|' .
            date('Y-m-d H:i:s') . PHP_EOL;

    if (fwrite($handle, $line) === false) {
        fclose($handle);
        return ['success' => false, 'error' => 'Unable to save your account.'];
    }

    fclose($handle);
    return ['success' => true];
}

function loginUser($email, $password) {
    $user = findUserByEmail($email);

    if (!$user) {
        return ['success' => false, 'error' => 'Email or password is incorrect.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'Email or password is incorrect.'];
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'name' => $user['name'],
        'email' => $user['email']
    ];

    return ['success' => true];
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function saveMessage($name, $email, $message) {
    ensureDataFiles();

    $handle = fopen(MESSAGES_FILE, 'a');
    if (!$handle) {
        return false;
    }

    $safeLine = date('Y-m-d H:i:s') . '|' .
                str_replace(['|', "\r", "\n"], ' ', trim($name)) . '|' .
                str_replace(['|', "\r", "\n"], ' ', trim($email)) . '|' .
                str_replace(['|', "\r", "\n"], ' ', trim($message)) . PHP_EOL;

    $saved = fwrite($handle, $safeLine) !== false;
    fclose($handle);
    return $saved;
}

function requirePost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('index.php');
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/* ---------- CART HELPERS ---------- */

/** Total quantity in the cart (sums every item's qty). */
function cartCount(): int {
    $n = 0;
    foreach ($_SESSION['cart'] ?? [] as $item) {
        if (is_array($item)) {
            $n += (int)($item['qty'] ?? 1);
        } else {
            $n += (int)$item;
        }
    }
    return $n;
}

/** Subtotal in pesos. */
function cartSubtotal(): float {
    $t = 0.0;
    foreach ($_SESSION['cart'] ?? [] as $item) {
        if (!is_array($item)) continue;
        $t += (float)($item['price'] ?? 0) * (int)($item['qty'] ?? 1);
    }
    return $t;
}

/** Turn a book title into a stable key: "Atomic Habits" → "atomic-habits" */
function slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}
?>
