
<?php
// backend/config/pdo.php
// Koneksi PostgreSQL untuk Native PHP.

$envFile = dirname(__DIR__) . '/.env';
$env = [];

if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' &&
            (($value[0] === '"' && str_ends_with($value, '"')) ||
             ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }

        $env[$key] = $value;
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$port = $env['DB_PORT'] ?? '5432';
$dbname = $env['DB_DATABASE'] ?? 'easyprint_db';
$username = $env['DB_USERNAME'] ?? 'easyprint_app';
$password = $env['DB_PASSWORD'] ?? '';

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('PostgreSQL connection failed: ' . $e->getMessage());
    throw new RuntimeException('Koneksi database gagal. Periksa konfigurasi database.');
}
