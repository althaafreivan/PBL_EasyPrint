
<?php
// backend/public/index.php
// Front Controller REST API - Native PHP

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

// Muat controller
require_once __DIR__ . '/../controllers/HealthController.php';
require_once __DIR__ . '/../models/Layanan.php';
require_once __DIR__ . '/../controllers/LayananController.php';

// Koneksi database PostgreSQL melalui PDO
try {
    require_once __DIR__ . '/../config/pdo.php';
} catch (Throwable $e) {
    error_log('Backend initialization failed: ' . $e->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Inisialisasi backend gagal.',
    ]);
}

// Baca path URL tanpa query string
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Routing endpoint
// Routing endpoint
if ($uri === '/api/health' && $method === 'GET') {
    HealthController::index($pdo);
}

if ($uri === '/api/layanan' && $method === 'GET') {
    LayananController::index($pdo);
}

// Endpoint yang belum tersedia
jsonResponse(404, [
    'success' => false,
    'message' => 'Endpoint tidak ditemukan.',
    'path' => $uri,
    'method' => $method,
]);
