<?php
// backend/core/http.php - Helper respons JSON & pembacaan input

final class HttpError extends RuntimeException
{
    public function __construct(string $message, private int $status = 400)
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_success(array $payload = [], int $status = 200): never
{
    json_response(['status' => 'success'] + $payload, $status);
}

function json_error(string $message, int $status = 400, array $extra = []): never
{
    json_response(['status' => 'error', 'message' => $message] + $extra, $status);
}

/** Lempar error yang aman ditampilkan ke pengguna. */
function fail(string $message, int $status = 400): never
{
    throw new HttpError($message, $status);
}

/** Body JSON sebagai array (kosong bila tidak ada). */
function body(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) {
        return $data = [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        fail('Body JSON tidak valid.');
    }
    return $data = $decoded;
}

function str_input(array $src, string $key, int $max = 255, bool $wajib = false, string $label = ''): ?string
{
    $v = isset($src[$key]) ? trim((string) $src[$key]) : '';
    if ($v === '') {
        if ($wajib) {
            fail(($label ?: $key) . ' wajib diisi.');
        }
        return null;
    }
    if (mb_strlen($v) > $max) {
        fail(($label ?: $key) . " maksimal $max karakter.");
    }
    return $v;
}

function num_input(array $src, string $key, ?float $min = null, bool $wajib = false, string $label = ''): ?float
{
    if (!array_key_exists($key, $src) || $src[$key] === '' || $src[$key] === null) {
        if ($wajib) {
            fail(($label ?: $key) . ' wajib diisi.');
        }
        return null;
    }
    if (!is_numeric($src[$key])) {
        fail(($label ?: $key) . ' harus berupa angka.');
    }
    $n = (float) $src[$key];
    if ($min !== null && $n < $min) {
        fail(($label ?: $key) . ' minimal ' . rtrim(rtrim(number_format($min, 4, '.', ''), '0'), '.') . '.');
    }
    return $n;
}

function int_query(string $key, int $default, int $min = 1, int $max = 1000): int
{
    $v = filter_var($_GET[$key] ?? $default, FILTER_VALIDATE_INT);
    if ($v === false) {
        return $default;
    }
    return max($min, min($max, $v));
}

function date_query(string $key): ?string
{
    $v = $_GET[$key] ?? '';
    if ($v === '') {
        return null;
    }
    $d = DateTime::createFromFormat('Y-m-d', $v);
    if (!$d || $d->format('Y-m-d') !== $v) {
        fail("Parameter $key harus berformat YYYY-MM-DD.");
    }
    return $v;
}

/** Set variabel konteks transaksi yang dibaca trigger (fn_konteks). */
function set_konteks(PDO $pdo, array $nilai): void
{
    $stmt = $pdo->prepare('SELECT set_config(:k, :v, true)');
    foreach ($nilai as $k => $v) {
        $stmt->execute(['k' => 'easyprint.' . $k, 'v' => $v === null ? '' : (string) $v]);
    }
}
