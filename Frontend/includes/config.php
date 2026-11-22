<?php
// frontend/includes/config.php
// Frontend TIDAK terhubung ke database. Semua data diambil dari REST API backend.
// Ubah lewat environment variable bila backend tidak di 127.0.0.1:8000:
//   $env:API_BASE_URL="http://192.168.1.10:8000/api"; php -S 127.0.0.1:3000 -t public

// Di Laragon/Apache (tanpa php -S) API dilayani oleh .htaccess di folder induk: <host>/<subfolder>/api.
if (!defined('API_BASE_URL')) {
    $apiBase = getenv('API_BASE_URL') ?: '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($apiBase === '' && PHP_SAPI !== 'cli-server' && PHP_SAPI !== 'cli'
        && preg_match('/^[A-Za-z0-9.\-]+(:\d+)?$/', $host)) {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $pos = strrpos($script, '/frontend/public/');
        $base = $pos ? rtrim(str_replace('\\', '/', dirname(substr($script, 0, $pos))), '/') : '';
        $apiBase = 'http://' . $host . $base . '/api';
    }
    define('API_BASE_URL', rtrim($apiBase ?: 'http://127.0.0.1:8000/api', '/'));
}

date_default_timezone_set('Asia/Jakarta');

const NAMA_TOKO   = 'Restu Ibu Print & Fotocopy';
const ALAMAT_TOKO = 'Jl. Soekarno Hatta No. 9, Lowokwaru, Malang';
const TELP_TOKO   = '0812-3456-0009';
const JAM_BUKA    = '08.00 – 20.00 WIB';
const HARI_BUKA   = 'Senin – Sabtu';
