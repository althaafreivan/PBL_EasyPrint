
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

if ($uri === '/api/health' && $method === 'GET') {
    try {
        require_once __DIR__ . '/../config/pdo.php';

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
            'message' => 'Koneksi database gagal.',
        ]);
    }
}

jsonResponse(404, [
    'success' => false,
    'message' => 'Endpoint tidak ditemukan.',
    'path' => $uri,
]);
