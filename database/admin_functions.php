<?php
/**
 * database/admin_functions.php – JCP Bookworks
 * Flat-file admin management + user management. No SQL required.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
   1) FILE HELPERS (ADMINS)
   ============================================================ */
function adminFile(): string
{
    $dir = __DIR__ . '/../data';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir . '/admins.json';
}

function ensureAdminFile(): void
{
    $file = adminFile();
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
    }
}

function getAdmins(): array
{
    ensureAdminFile();
    $raw = @file_get_contents(adminFile());
    if ($raw === false || trim($raw) === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveAdmins(array $admins): bool
{
    ensureAdminFile();
    return file_put_contents(
        adminFile(),
        json_encode(array_values($admins), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}

function adminCount(): int
{
    return count(getAdmins());
}

/* ============================================================
   2) CREATE ADMIN
   ============================================================ */
function createAdmin(string $name, string $email, string $password, string $role = 'editor'): array
{
    $name  = trim($name);
    $email = strtolower(trim($email));

    if (strlen($name) < 2)
        return ['success' => false, 'error' => 'Please enter a valid name.'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return ['success' => false, 'error' => 'Please enter a valid email address.'];
    if (strlen($password) < 8)
        return ['success' => false, 'error' => 'Admin password must be at least 8 characters.'];
    if (!in_array($role, ['super', 'editor', 'viewer'], true))
        $role = 'editor';

    $admins = getAdmins();
    foreach ($admins as $a) {
        if (strtolower($a['email']) === $email)
            return ['success' => false, 'error' => 'An admin with that email already exists.'];
    }

    $nextId = 1;
    foreach ($admins as $a) {
        if ((int)$a['id'] >= $nextId) $nextId = (int)$a['id'] + 1;
    }

    $admins[] = [
        'id'            => $nextId,
        'name'          => $name,
        'email'         => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role'          => $role,
        'is_active'     => 1,
        'last_login'    => null,
        'created_at'    => date('Y-m-d H:i:s'),
    ];

    if (!saveAdmins($admins))
        return ['success' => false, 'error' => 'Could not save admin. Check folder permissions.'];

    return ['success' => true, 'admin_id' => $nextId];
}

/* ============================================================
   3) LOGIN ADMIN (accepts name OR email)
   ============================================================ */
function loginAdmin(string $identifier, string $password): array
{
    $identifier = strtolower(trim($identifier));

    if ($identifier === '')
        return ['success' => false, 'error' => 'Please enter your username or email.'];
    if ($password === '')
        return ['success' => false, 'error' => 'Please enter your password.'];

    $admins = getAdmins();
    $found  = null;
    $key    = -1;

    foreach ($admins as $i => $a) {
        $byEmail = strtolower($a['email']) === $identifier;
        $byName  = strtolower($a['name'])  === $identifier;
        if ($byEmail || $byName) { $found = $a; $key = $i; break; }
    }

    if (!$found || !password_verify($password, $found['password_hash']))
        return ['success' => false, 'error' => 'Invalid credentials.'];
    if ((int)$found['is_active'] !== 1)
        return ['success' => false, 'error' => 'This admin account is disabled.'];

    $admins[$key]['last_login'] = date('Y-m-d H:i:s');
    saveAdmins($admins);

    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'    => (int)$found['id'],
        'name'  => $found['name'],
        'email' => $found['email'],
        'role'  => $found['role'],
    ];

    return ['success' => true];
}

/* ============================================================
   4) SESSION HELPERS
   ============================================================ */
function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin']['id']);
}

function currentAdmin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: admin_login.php');
        exit;
    }
}

function isSuperAdmin(): bool
{
    return isAdminLoggedIn() && ($_SESSION['admin']['role'] ?? '') === 'super';
}

function logoutAdmin(): void
{
    unset($_SESSION['admin']);
    session_regenerate_id(true);
}

/* ============================================================
   5) USER MANAGEMENT (admin views users from data/users.txt)
   ============================================================ */
function usersFile(): string
{
    return __DIR__ . '/../data/users.txt';
}

function getAllUsers(): array
{
    $file = usersFile();
    if (!file_exists($file)) return [];

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) return [];

    $users  = [];
    $autoId = 1;

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        $parts = null;
        foreach (['|', ',', ';', ':'] as $delim) {
            if (strpos($line, $delim) !== false) {
                $parts = array_map('trim', explode($delim, $line));
                break;
            }
        }
        if (!$parts || count($parts) < 2) continue;

        $email = '';
        $name  = '';
        $hash  = '';

        foreach ($parts as $p) {
            if ($email === '' && filter_var($p, FILTER_VALIDATE_EMAIL)) {
                $email = $p;
                continue;
            }
            if ($hash === '' && preg_match('/^\$(2[aby]|argon2)/', $p)) {
                $hash = $p;
                continue;
            }
            if ($name === '' && preg_match('/[A-Za-z]/', $p)) {
                $name = $p;
            }
        }

        if ($name === '' && isset($parts[0])) $name = $parts[0];
        if ($email === '') continue;

        $createdAt = '';
        $last = end($parts);
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $last)) $createdAt = $last;

        $users[] = [
            'id'         => $autoId++,
            'name'       => $name,
            'email'      => $email,
            'hash'       => $hash,
            'created_at' => $createdAt,
            'raw'        => $line,
        ];
    }

    return $users;
}

function countUsers(): int
{
    return count(getAllUsers());
}

function deleteUserByLine(string $rawLine): array
{
    $file = usersFile();
    if (!file_exists($file))
        return ['success' => false, 'error' => 'Users file not found.'];

    $lines = file($file, FILE_IGNORE_NEW_LINES);
    if ($lines === false)
        return ['success' => false, 'error' => 'Could not read users file.'];

    $newLines = [];
    $removed  = false;

    foreach ($lines as $line) {
        if (trim($line) === trim($rawLine)) { $removed = true; continue; }
        $newLines[] = $line;
    }

    if (!$removed)
        return ['success' => false, 'error' => 'User not found.'];

    if (file_put_contents($file, implode(PHP_EOL, $newLines) . PHP_EOL, LOCK_EX) === false)
        return ['success' => false, 'error' => 'Could not save users file.'];

    return ['success' => true];
}