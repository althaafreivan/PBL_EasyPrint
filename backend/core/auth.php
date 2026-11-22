<?php
// backend/core/auth.php - Autentikasi token Bearer & otorisasi multi-role

function bearer_token(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '');

    if (!preg_match('/^Bearer\s+([A-Fa-f0-9]{64})$/', trim($header), $m)) {
        return null;
    }
    return $m[1];
}

// Helper autentikasi token Bearer (nama fungsi dari kode CP-2 dipertahankan).
function getAuthenticatedUser(PDO $pdo): ?array
{
    $token = bearer_token();
    if ($token === null) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT u.id, u.nama, u.email, u.role, u.no_telepon
         FROM public.auth_tokens t
         JOIN public.users u ON u.id = t.user_id
         WHERE t.token_hash = :token_hash
           AND t.expires_at > CURRENT_TIMESTAMP'
    );
    $stmt->execute(['token_hash' => hash('sha256', $token)]);

    $user = $stmt->fetch();
    if ($user) {
        $user['id'] = (int) $user['id'];
    }
    return $user ?: null;
}

/**
 * Wajib login; bila $roles diisi, peran pengguna harus salah satunya.
 * @param string[] $roles
 */
function require_user(array $roles = []): array
{
    $user = getAuthenticatedUser(db());
    if (!$user) {
        fail('Token tidak valid, kedaluwarsa, atau tidak disertakan.', 401);
    }
    if ($roles && !in_array($user['role'], $roles, true)) {
        fail('Anda tidak memiliki akses ke fitur ini.', 403);
    }
    return $user;
}

function is_staff(array $user): bool
{
    return in_array($user['role'], ['admin', 'owner'], true);
}
