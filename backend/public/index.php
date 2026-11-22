<?php
// backend/public/index.php
// CP-1: Front Controller Native PHP REST API (router + CORS).
// Jalankan: cd backend && php -S 127.0.0.1:8000 -t public

declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');
ini_set('display_errors', '0');   // jangan pernah kirim stack trace mentah ke klien

require_once __DIR__ . '/../core/http.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/auth.php';
foreach (glob(__DIR__ . '/../controllers/*.php') as $controller) {
    require_once $controller;
}

// ------------------------------------------------------------------ CORS
$allowedOrigins = array_filter(array_map('trim', explode(',',
    getenv('CORS_ORIGINS') ?: 'http://127.0.0.1:3000,http://localhost:3000'
)));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
}
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
// Di Apache/Laragon aplikasi bisa berada di subfolder (mis. /easyprint/api/login): buang prefiksnya.
$uri = preg_replace('#^.*?(?=/api(?:/|$))#', '', $uri) ?: '/';

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ------------------------------------------------------------------ ROUTES
// [method, pola regex path, handler]. Grup tangkapan regex dikirim sebagai argumen.
$routes = [
    ['GET',    '/api/health',                        'health'],
    // Auth (CP-2)
    ['POST',   '/api/register',                      'auth_register'],
    ['POST',   '/api/login',                         'auth_login'],
    ['POST',   '/api/logout',                        'auth_logout'],
    ['GET',    '/api/me',                            'auth_me'],
    ['PATCH',  '/api/me',                            'auth_update_me'],
    // Layanan & BOM
    ['GET',    '/api/layanan',                       'layanan_index'],
    ['POST',   '/api/layanan',                       'layanan_store'],
    ['PATCH',  '/api/layanan/(\d+)',                 'layanan_update'],
    ['PUT',    '/api/layanan/(\d+)',                 'layanan_update'],
    ['DELETE', '/api/layanan/(\d+)',                 'layanan_destroy'],
    ['GET',    '/api/bahan-layanan',                 'bom_index'],
    ['POST',   '/api/bahan-layanan',                 'bom_store'],
    ['PATCH',  '/api/bahan-layanan/(\d+)',           'bom_update'],
    ['DELETE', '/api/bahan-layanan/(\d+)',           'bom_destroy'],
    // Pesanan (CP-2 & CP-3)
    ['POST',   '/api/orders',                        'order_store'],
    ['GET',    '/api/orders',                        'order_index'],
    ['PATCH',  '/api/orders/status',                 'order_update_status'],
    ['GET',    '/api/orders/(\d+)/file',             'order_file'],
    ['GET',    '/api/orders/(\d+)(?:/(invoice))?',   'order_show'],
    ['DELETE', '/api/orders/(\d+)',                  'order_destroy'],
    ['GET',    '/api/track',                         'order_track_public'],
    // Notifikasi (CP-3)
    ['GET',    '/api/notifications',                 'notif_index'],
    ['PATCH',  '/api/notifications/read',            'notif_read'],
    ['DELETE', '/api/notifications',                 'notif_destroy'],
    ['DELETE', '/api/notifications/(\d+)',           'notif_destroy'],
    // Stok & laporan (CP-3)
    ['GET',    '/api/stok',                          'stok_index'],
    ['POST',   '/api/stok',                          'stok_store'],
    ['GET',    '/api/stok/riwayat',                  'stok_riwayat'],
    ['PATCH',  '/api/stok/(\d+)',                    'stok_update'],
    ['DELETE', '/api/stok/(\d+)',                    'stok_destroy'],
    ['POST',   '/api/stok/(\d+)/restock',            'stok_restock'],
    ['GET',    '/api/laporan/stok',                  'laporan_stok'],
    ['GET',    '/api/laporan/transaksi',             'laporan_transaksi'],
    ['GET',    '/api/dashboard',                     'dashboard'],
];

function health(): void
{
    $nama = db()->query('SELECT current_database()')->fetchColumn();
    json_success(['message' => 'EasyPrint Backend REST API aktif', 'database' => $nama]);
}

// ------------------------------------------------------------------ DISPATCH
try {
    $pathCocok = false;
    foreach ($routes as [$m, $pattern, $handler]) {
        if (!preg_match('#^' . $pattern . '$#', $uri, $args)) {
            continue;
        }
        $pathCocok = true;
        if ($m !== $method) {
            continue;
        }
        array_shift($args);
        $handler(...$args);
        exit;
    }
    if ($pathCocok) {
        json_error('Metode HTTP tidak diizinkan untuk endpoint ini.', 405);
    }
    json_error('Endpoint tidak ditemukan.', 404);
} catch (HttpError $e) {
    if (db_aktif() && db()->inTransaction()) {
        db()->rollBack();
    }
    json_error($e->getMessage(), $e->status());
} catch (Throwable $e) {
    error_log('[EasyPrint API] ' . $e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (db_aktif() && db()->inTransaction()) {
        db()->rollBack();
    }
    [$pesan, $kode] = ['Terjadi kesalahan pada server.', 500];
    if ($e instanceof PDOException) {
        // Pesan dari trigger validasi (RAISE EXCEPTION) aman ditampilkan.
        if ($e->getCode() === 'P0001' && preg_match('/ERROR:\s+(.+?)(\n|CONTEXT|$)/s', $e->getMessage(), $m)) {
            json_error(trim($m[1]), 400);
        }
        [$pesan, $kode] = match ($e->getCode()) {
            '23505' => ['Data duplikat: nilai tersebut sudah digunakan.', 409],
            '23503' => ['Data masih berelasi dengan data lain sehingga tidak dapat diproses.', 409],
            '23514' => ['Data tidak memenuhi aturan validasi database.', 400],
            '22P02', '22003' => ['Format atau rentang angka tidak valid.', 400],
            default => ['Terjadi kesalahan pada database.', 500],
        };
    }
    json_error($pesan, $kode);
}

function db_aktif(): bool
{
    try {
        return db() instanceof PDO;
    } catch (Throwable) {
        return false;
    }
}
