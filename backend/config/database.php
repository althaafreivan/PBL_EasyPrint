<?php
// backend/config/database.php
// Koneksi PDO ke PostgreSQL EasyPrint (mendukung .env lokal, config/env.php, dan DATABASE_URL untuk deploy).

(function (): void {
    $envFiles = [
        __DIR__ . '/../../.env.local', // Neon CLI (`neon deploy` / `neon env pull`)
        __DIR__ . '/../../.env',
        __DIR__ . '/../.env',
    ];
    foreach ($envFiles as $envFile) {
        if (!is_file($envFile)) {
            continue;
        }
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            continue;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if (strlen($val) >= 2 && (
                ($val[0] === '"' && $val[strlen($val) - 1] === '"') ||
                ($val[0] === "'" && $val[strlen($val) - 1] === "'")
            )) {
                $val = substr($val, 1, -1);
            }
            if ($key !== '' && getenv($key) === false) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
            }
        }
    }
})();

if (is_file(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $databaseUrl = getenv('DATABASE_URL') ?: '';
    if ($databaseUrl !== '') {
        $parts = parse_url($databaseUrl);
        $driver  = 'pgsql';
        $host    = $parts['host'] ?? '127.0.0.1';
        $port    = (string) ($parts['port'] ?? '5432');
        $name    = ltrim($parts['path'] ?? '/easyprint_db', '/');
        $user    = isset($parts['user']) ? urldecode($parts['user']) : 'postgres';
        $pass    = isset($parts['pass']) ? urldecode($parts['pass']) : '';
        parse_str($parts['query'] ?? '', $query);
        $isLocal = in_array($host, ['127.0.0.1', 'localhost'], true);
        $sslmode = $query['sslmode'] ?? (getenv('DB_SSLMODE') ?: ($isLocal ? 'disable' : 'require'));
    } else {
        $driver  = getenv('DB_DRIVER') ?: 'pgsql';
        $host    = getenv('DB_HOST')   ?: '127.0.0.1';
        $port    = getenv('DB_PORT')   ?: '5432';
        $name    = getenv('DB_NAME')   ?: 'easyprint_db';
        $user    = getenv('DB_USER')   ?: 'postgres';
        $pass    = getenv('DB_PASS')   ?: '';
        $sslmode = getenv('DB_SSLMODE') ?: '';
    }

    $dsn = "$driver:host=$host;port=$port;dbname=$name" . ($sslmode !== '' ? ";sslmode=$sslmode" : '');

    $isPooler = str_contains($host, '-pooler.');
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => $isPooler,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $pdo->exec("SET TIME ZONE 'Asia/Jakarta'");
    } catch (PDOException $e) {
        error_log('Koneksi PostgreSQL gagal: ' . $e->getMessage());
        if (function_exists('json_error')) {
            json_error('Koneksi database gagal. Periksa konfigurasi PostgreSQL.', 500);
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error',
            'message' => 'Koneksi database gagal. Periksa konfigurasi PostgreSQL.'
        ]);
        exit;
    }

    return $pdo;
}
