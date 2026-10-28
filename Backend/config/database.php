<?php
// backend/config/database.php
// Koneksi PDO ke PostgreSQL EasyPrint

$driver = 'pgsql';
$host   = 'localhost';
$port   = '5432';
$db     = 'easyprint_db';
$user   = 'postgres';

// Masukkan password PostgreSQL kamu di laptop sendiri.
$pass = '';

// DSN koneksi database
$dsn = "$driver:host=$host;port=$port;dbname=$db";

// Pengaturan PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'status'  => 'error',
        'message' => 'Koneksi database gagal. Periksa konfigurasi PostgreSQL.'
    ]);

    error_log('Koneksi PostgreSQL gagal: ' . $e->getMessage());
    exit;
}
?>