<?php

header('Content-Type: application/json; charset=UTF-8');

$allowedOrigins = [
    'http://127.0.0.1:3000',
    'http://localhost:3000',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Vary: Origin');
}

header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function jsonResponse(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Koneksi database hanya dimuat ketika endpoint membutuhkannya.
if (in_array($uri, ['/api/health', '/api/register', '/api/login'], true)) {
    try {
        require_once __DIR__ . '/../config/pdo.php';
    } catch (Throwable $e) {
        error_log('Database initialization failed: ' . $e->getMessage());

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Koneksi database gagal.',
        ]);
    }
}

// CP 1: Health check
if ($uri === '/api/health' && $method === 'GET') {
    try {
        $stmt = $pdo->query(
            'SELECT current_database() AS database_name'
        );

        $database = $stmt->fetch();

        jsonResponse(200, [
            'success' => true,
            'message' => 'Backend EasyPrint berjalan.',
            'database' => $database['database_name'],
        ]);
    } catch (Throwable $e) {
        error_log('Health check failed: ' . $e->getMessage());

        jsonResponse(500, [
            'success' => false,
            'message' => 'Pemeriksaan database gagal.',
        ]);
    }
}

// CP 2: Register pelanggan
if ($uri === '/api/register' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);


    if (
        !is_array($data) ||
        !isset($data['nama'], $data['email'], $data['password']) ||
        !is_string($data['nama']) ||
        !is_string($data['email']) ||
        !is_string($data['password']) ||
        trim($data['nama']) === '' ||
        trim($data['email']) === '' ||
        $data['password'] === ''
    ) {
        jsonResponse(400, [
            'status' => 'error',
            'message' => 'Nama, email, dan password wajib diisi.',
        ]);
    }

    $nama = trim($data['nama']);
    $email = trim($data['email']);
    $password = $data['password'];
    $noTelepon = $data['no_telepon'] ?? '';

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8 ||
        !is_string($noTelepon)
    ) {
        jsonResponse(422, [
            'status' => 'error',
            'message' => 'Email tidak valid, password minimal 8 karakter, atau nomor telepon tidak valid.',
        ]);
    }

    try {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (nama, email, password, role, no_telepon)
             VALUES (:nama, :email, :password, :role, :no_telepon)
             RETURNING id, nama, email, role'
        );

        $stmt->execute([
            'nama' => $nama,
            'email' => $email,
            'password' => $hashed,
            'role' => 'pelanggan',
            'no_telepon' => trim($noTelepon),
        ]);

        jsonResponse(201, [
            'status' => 'success',
            'message' => 'Berhasil daftar akun pelanggan.',
            'user' => $stmt->fetch(),
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            jsonResponse(409, [
                'status' => 'error',
                'message' => 'Email sudah terdaftar.',
            ]);
        }

        error_log('Register failed: ' . $e->getMessage());

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Registrasi gagal.',
        ]);
    }
}

// CP 2: Login semua role yang terdaftar
if ($uri === '/api/login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        !is_array($data) ||
        !isset($data['email'], $data['password']) ||
        !is_string($data['email']) ||
        !is_string($data['password']) ||
        trim($data['email']) === '' ||
        $data['password'] === ''
    ) {
        jsonResponse(400, [
            'status' => 'error',
            'message' => 'Email dan password wajib diisi.',
        ]);
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id, nama, email, password, role
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute([
            'email' => trim($data['email']),
        ]);

        $user = $stmt->fetch();

        if (
            !$user ||
            !password_verify($data['password'], $user['password'])
        ) {
            jsonResponse(401, [
                'status' => 'error',
                'message' => 'Email atau password salah.',
            ]);
        }

        jsonResponse(200, [
            'status' => 'success',
            'message' => 'Login berhasil.',
            'user' => [
                'id' => $user['id'],
                'nama' => $user['nama'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ]);
    } catch (PDOException $e) {
        error_log('Login failed: ' . $e->getMessage());

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Login gagal karena kesalahan server.',
        ]);
    }
}

// Endpoint lain belum tersedia.
jsonResponse(404, [
    'status' => 'error',
    'message' => 'Endpoint tidak ditemukan.',
    'path' => $uri,
    'method' => $method,
]);
