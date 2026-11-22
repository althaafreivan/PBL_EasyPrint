<?php
// frontend/public/file.php?id=123 - pratinjau/unduh berkas pesanan lewat backend (cegah misprint).
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['pelanggan', 'admin', 'owner']);

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('ID pesanan tidak valid.');
}

$res = callBackendApi("/api/orders/$id/file", 'GET', null, [], true);
if (!$res['ok']) {
    http_response_code($res['code'] ?: 502);
    $pesan = json_decode($res['raw'] ?? '', true)['message'] ?? 'Berkas tidak tersedia.';
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Berkas</title><body style="font-family:Inter,system-ui,sans-serif;padding:40px">';
    echo '<h2>Berkas tidak dapat ditampilkan</h2><p>' . e($pesan) . '</p><p><a href="javascript:history.back()">‹ Kembali</a></p>';
    exit;
}
foreach (['content-type', 'content-disposition'] as $h) {
    if (isset($res['headers'][$h])) {
        header(ucwords($h, '-') . ': ' . $res['headers'][$h]);
    }
}
header('X-Content-Type-Options: nosniff');
echo $res['raw'];
