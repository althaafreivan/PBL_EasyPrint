
<?php
// CP-1: Native PHP REST API Router dan CORS

header('Content-Type: application/json; charset=utf-8');

$allowedOrigins = [
    'http://127.0.0.1:3000',
    'http://localhost:3000',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
}

header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($uri === '/api/health' && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';

    try {
        $stmt = $pdo->query(
            'SELECT current_database() AS database_name'
        );
        $result = $stmt->fetch();

        echo json_encode([
            'status' => 'success',
            'message' => 'EasyPrint Backend REST API aktif',
            'database' => $result['database_name'],
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);

        echo json_encode([
            'status' => 'error',
            'message' => 'Koneksi database gagal.',
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
}

http_response_code(404);

echo json_encode([
    'status' => 'error',
    'message' => 'Endpoint tidak ditemukan.',
], JSON_UNESCAPED_UNICODE);
