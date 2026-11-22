<?php
// backend/controllers/AuthController.php
// CP-2: Register, Login, Logout & Profil (multi-role: pelanggan, admin, owner)

function auth_register(): void
{
    $data = body();
    $nama  = str_input($data, 'nama', 100, true, 'Nama');
    $email = strtolower(str_input($data, 'email', 100, true, 'Email'));
    $password = (string) ($data['password'] ?? '');
    $noTelepon = str_input($data, 'no_telepon', 20);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fail('Format email tidak valid.');
    }
    if (strlen($password) < 8) {
        fail('Password minimal 8 karakter.');
    }
    if ($noTelepon !== null && !preg_match('/^[0-9+\-\s]{8,20}$/', $noTelepon)) {
        fail('Nomor telepon hanya boleh berisi angka (8-20 digit).');
    }

    try {
        $stmt = db()->prepare(
            "INSERT INTO public.users (nama, email, password, no_telepon, role)
             VALUES (:nama, :email, :password, :no_telepon, 'pelanggan')
             RETURNING id, nama, email, role"
        );
        $stmt->execute([
            'nama' => $nama,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'no_telepon' => $noTelepon,
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            fail('Email sudah terdaftar.', 409);
        }
        throw $e;
    }

    json_success(['message' => 'Registrasi berhasil.', 'user' => $stmt->fetch()], 201);
}

function auth_login(): void
{
    $data = body();
    $email = strtolower(trim((string) ($data['email'] ?? '')));
    $password = (string) ($data['password'] ?? '');

    if ($email === '' || $password === '') {
        fail('Email dan password wajib diisi.');
    }

    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT id, nama, email, password, role, no_telepon FROM public.users WHERE email = :email'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        fail('Email atau password salah.', 401);
    }

    $token = bin2hex(random_bytes(32));

    // Bersihkan token kedaluwarsa milik user ini, lalu simpan token baru (hash SHA-256).
    $pdo->prepare('DELETE FROM public.auth_tokens WHERE user_id = :id AND expires_at <= CURRENT_TIMESTAMP')
        ->execute(['id' => $user['id']]);
    $pdo->prepare(
        "INSERT INTO public.auth_tokens (user_id, token_hash, expires_at)
         VALUES (:user_id, :token_hash, CURRENT_TIMESTAMP + INTERVAL '8 hours')"
    )->execute(['user_id' => $user['id'], 'token_hash' => hash('sha256', $token)]);

    unset($user['password']);
    $user['id'] = (int) $user['id'];

    json_success([
        'message' => 'Login berhasil.',
        'token_type' => 'Bearer',
        'access_token' => $token,
        'expires_in' => 28800,
        'user' => $user,
    ]);
}

function auth_logout(): void
{
    require_user();
    db()->prepare('DELETE FROM public.auth_tokens WHERE token_hash = :h')
        ->execute(['h' => hash('sha256', bearer_token())]);
    json_success(['message' => 'Logout berhasil.']);
}

function auth_me(): void
{
    $user = require_user();
    $stmt = db()->prepare(
        'SELECT id, nama, email, no_telepon, role, created_at FROM public.users WHERE id = :id'
    );
    $stmt->execute(['id' => $user['id']]);
    json_success(['user' => $stmt->fetch()]);
}

// Update profil sendiri (nama, no_telepon, dan opsional ganti password).
function auth_update_me(): void
{
    $user = require_user();
    $data = body();

    $nama = str_input($data, 'nama', 100, true, 'Nama');
    $noTelepon = str_input($data, 'no_telepon', 20);
    if ($noTelepon !== null && !preg_match('/^[0-9+\-\s]{8,20}$/', $noTelepon)) {
        fail('Nomor telepon hanya boleh berisi angka (8-20 digit).');
    }

    $pdo = db();
    $sets = ['nama = :nama', 'no_telepon = :tel'];
    $params = ['nama' => $nama, 'tel' => $noTelepon, 'id' => $user['id']];

    $baru = (string) ($data['password_baru'] ?? '');
    if ($baru !== '') {
        if (strlen($baru) < 8) {
            fail('Password baru minimal 8 karakter.');
        }
        $stmt = $pdo->prepare('SELECT password FROM public.users WHERE id = :id');
        $stmt->execute(['id' => $user['id']]);
        if (!password_verify((string) ($data['password_lama'] ?? ''), (string) $stmt->fetchColumn())) {
            fail('Password lama tidak sesuai.');
        }
        $sets[] = 'password = :pw';
        $params['pw'] = password_hash($baru, PASSWORD_DEFAULT);
    }

    $stmt = $pdo->prepare(
        'UPDATE public.users SET ' . implode(', ', $sets) .
        ' WHERE id = :id RETURNING id, nama, email, no_telepon, role'
    );
    $stmt->execute($params);
    json_success(['message' => 'Profil diperbarui.', 'user' => $stmt->fetch()]);
}
