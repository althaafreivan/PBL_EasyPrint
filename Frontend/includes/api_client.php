<?php
// frontend/includes/api_client.php - Helper konsumsi REST API Backend (cURL)
// Sesuai SOP CP-1 Frontend: callBackendApi() menembak http://127.0.0.1:8000/api/...

/**
 * @param array|null $payload  array => dikirim sebagai JSON.
 * @param array      $files    ['file_dokumen' => CURLFile] => request multipart.
 * @return array{code:int, data:array, ok:bool, raw?:string, headers?:array}
 */
function callBackendApi(string $endpoint, string $method = 'GET', ?array $payload = null, array $files = [], bool $raw = false): array
{
    $url = API_BASE_URL . '/' . ltrim(preg_replace('#^/?api/#', '', $endpoint), '/');
    $ch = curl_init($url);

    $headers = ['Accept: application/json'];
    if (!empty($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($files) {
        // multipart/form-data: field biasa + berkas
        curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($payload ?? [], $files));
    } elseif ($payload !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    $respHeaders = [];
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($c, $line) use (&$respHeaders) {
        $p = explode(':', $line, 2);
        if (count($p) === 2) {
            $respHeaders[strtolower(trim($p[0]))] = trim($p[1]);
        }
        return strlen($line);
    });
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    

    if ($response === false) {
        error_log('[EasyPrint FE] Backend tidak dapat dihubungi: ' . $error);
        return [
            'code' => 0,
            'ok' => false,
            'data' => ['status' => 'error', 'message' => 'Server backend tidak dapat dihubungi. Pastikan backend berjalan di ' . API_BASE_URL . '.'],
        ];
    }

    if ($raw) {
        return ['code' => $httpCode, 'ok' => $httpCode < 400, 'data' => [], 'raw' => $response, 'headers' => $respHeaders];
    }

    $decoded = json_decode($response, true);
    return [
        'code' => $httpCode,
        'ok' => $httpCode >= 200 && $httpCode < 300,
        'data' => is_array($decoded) ? $decoded : ['status' => 'error', 'message' => 'Respons backend tidak valid.'],
    ];
}

/**
 * Untuk halaman: ambil data GET. Bila token kedaluwarsa (401) pengguna diarahkan ke login.
 * Mengembalikan array respons; cek $res['ok'] dan tampilkan $res['data']['message'] bila gagal.
 */
function api_get(string $endpoint): array
{
    $res = callBackendApi($endpoint);
    if ($res['code'] === 401 && !empty($_SESSION['token'])) {
        logout_lokal();
        header('Location: login.php?expired=1&next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
        exit;
    }
    return $res;
}

function api_message(array $res, string $fallback = 'Terjadi kesalahan.'): string
{
    return (string) ($res['data']['message'] ?? $fallback);
}
