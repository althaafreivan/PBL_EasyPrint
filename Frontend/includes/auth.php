<?php
// frontend/includes/auth.php - Sesi login & proteksi halaman per peran.
// Token Bearer dari backend disimpan di sesi PHP (server-side), tidak pernah dikirim ke JavaScript.

function mulai_sesi(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('EASYPRINTSESS');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function home_for_role(?string $role): string
{
    return match ($role) {
        'owner' => 'owner-dashboard.php',
        'admin' => 'admin-dashboard.php',
        'pelanggan' => 'dashboard.php',
        default => 'login.php',
    };
}

/**
 * Halaman wajib login dengan peran tertentu.
 * @param string[] $roles
 */
function require_role(array $roles): array
{
    $user = current_user();
    if (!$user || empty($_SESSION['token'])) {
        header('Location: login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
        exit;
    }
    if (!in_array($user['role'], $roles, true)) {
        header('Location: ' . home_for_role($user['role']));
        exit;
    }
    return $user;
}

function login_lokal(string $token, array $user): void
{
    session_regenerate_id(true);
    $_SESSION['token'] = $token;
    $_SESSION['user'] = $user;
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

function logout_lokal(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf']) && hash_equals(csrf_token(), (string) $_POST['csrf']);
}

/** Pesan flash satu kali tampil. */
function flash(?string $pesan = null, string $tipe = 'ok'): ?array
{
    if ($pesan !== null) {
        $_SESSION['flash'] = ['pesan' => $pesan, 'tipe' => $tipe];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function inisial(string $nama): string
{
    $kata = preg_split('/\s+/', trim($nama)) ?: [''];
    $s = mb_substr($kata[0], 0, 1) . (isset($kata[1]) ? mb_substr($kata[1], 0, 1) : '');
    return mb_strtoupper($s ?: '?');
}
