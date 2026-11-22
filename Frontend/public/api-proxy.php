<?php
// frontend/public/api-proxy.php
// Jembatan fetch() JavaScript -> REST API backend (:8000) dengan token dari sesi PHP.
// Alasan: token Bearer tetap di server (httpOnly session) sehingga tidak bisa dicuri lewat XSS,
// dan browser tidak perlu request lintas origin.
//   fetch('api-proxy.php?path=/api/orders/status', {method:'PATCH', headers:{'X-Requested-With':'EasyPrint'}, body: ...})

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$path = (string) ($_GET['path'] ?? '');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!preg_match('#^/api/[a-z0-9\-/]+$#i', $path)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Path API tidak valid.']);
    exit;
}
// Endpoint katalog layanan bersifat publik,
// sehingga pengunjung yang belum login tetap bisa melihat katalog.
$aksesPublik = (
    $method === 'GET' &&
    $path === '/api/layanan'
);

// Endpoint lainnya tetap membutuhkan sesi login.
if (!$aksesPublik && empty($_SESSION['token'])) {
    http_response_code(401);

    echo json_encode([
        'status' => 'error',
        'message' => 'Sesi berakhir. Silakan masuk kembali.'
    ]);

    exit;
}
// Lindungi dari CSRF: request yang mengubah data wajib membawa header khusus
// (header kustom memaksa preflight CORS sehingga situs lain tidak bisa mengirimnya).
if ($method !== 'GET' && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'EasyPrint') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Permintaan ditolak.']);
    exit;
}

// Teruskan query string selain "path".
$query = $_GET;
unset($query['path']);
$endpoint = $path . ($query ? '?' . http_build_query($query) : '');

$files = [];
$payload = null;
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') === 0) {
    $payload = $_POST;
    foreach ($_FILES as $field => $f) {
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $files[$field] = new CURLFile($f['tmp_name'], $f['type'] ?: 'application/octet-stream', $f['name']);
        } elseif (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $f['error'] === UPLOAD_ERR_INI_SIZE
                ? 'Ukuran berkas melebihi batas server.' : 'Upload berkas gagal.']);
            exit;
        }
    }
    if (!$files && isset($payload['items']) && is_string($payload['items'])) {
        // Tanpa berkas: kirim sebagai JSON biasa (items didekode dari string FormData).
        $payload['items'] = json_decode($payload['items'], true);
    }
} elseif ($method !== 'GET') {
    $raw = file_get_contents('php://input');
    $payload = $raw !== '' ? (json_decode($raw, true) ?? []) : [];
}

$res = callBackendApi($endpoint, $method, $payload, $files);

if ($res['code'] === 401) {
    logout_lokal();
}
// Perbarui nama di sesi setelah ubah profil.
if ($res['ok'] && $path === '/api/me' && $method === 'PATCH' && isset($res['data']['user'])) {
    $_SESSION['user'] = array_merge($_SESSION['user'], $res['data']['user']);
}

http_response_code($res['code'] ?: 502);
echo json_encode($res['data'], JSON_UNESCAPED_UNICODE);
