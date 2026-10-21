<?php

header('Content-Type: application/json; charset=UTF-8');

// CORS
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

// Respons JSON
function jsonResponse(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Middleware autentikasi Bearer token
function authenticateUser(PDO $pdo): array
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!preg_match('/^Bearer\s+(\S+)$/i', trim($authHeader), $matches)) {
        jsonResponse(401, [
            'status' => 'error',
            'message' => 'Bearer token wajib disertakan.',
        ]);
    }

    $tokenHash = hash('sha256', $matches[1]);

    $stmt = $pdo->prepare(
        'SELECT u.id, u.nama, u.email, u.role
         FROM public.auth_tokens t
         JOIN public.users u ON u.id = t.user_id
         WHERE t.token_hash = :token_hash
           AND t.expires_at > CURRENT_TIMESTAMP
         LIMIT 1'
    );

    $stmt->execute([
        'token_hash' => $tokenHash,
    ]);

    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(401, [
            'status' => 'error',
            'message' => 'Token tidak valid atau kedaluwarsa.',
        ]);
    }

    return $user;
}

// Routing
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Daftar endpoint yang memerlukan database
$dbRoutes = [
    '/api/health',
    '/api/register',
    '/api/login',
    '/api/me',
    '/api/orders',
    '/api/orders/upload',
    '/api/reports/order-summary',
    '/api/reports/order-details',
];

if (
    preg_match('#^/api/orders/[0-9]+/status$#', $uri)
) {
    $dbRoutes[] = $uri;
}

// Koneksi database dimuat SEBELUM endpoint dijalankan
if (in_array($uri, $dbRoutes, true)) {
    try {
        require_once __DIR__ . '/../config/pdo.php';
    } catch (Throwable $e) {
        error_log(
            'Database initialization failed: '
            . get_class($e) . ': ' . $e->getMessage()
        );

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Koneksi database gagal.',
        ]);
    }
}

// ==================================================
// CP 1: HEALTH CHECK
// ==================================================
if ($uri === '/api/health' && $method === 'GET') {
    try {
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
            'message' => 'Pemeriksaan database gagal.',
        ]);
    }
}

// ==================================================
// CP 2: REGISTER PELANGGAN
// ==================================================
if ($uri === '/api/register' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        !is_array($data) ||
        !isset($data['nama'], $data['email'], $data['password']) ||
        !is_string($data['nama']) ||
        !is_string($data['email']) ||
        !is_string($data['password']) ||
        trim($data['nama']) === '' ||
        trim($data['email']) === '' ||
        $data['password'] === ''
    ) {
        jsonResponse(400, [
            'status' => 'error',
            'message' => 'Nama, email, dan password wajib diisi.',
        ]);
    }

    $nama = trim($data['nama']);
    $email = trim($data['email']);
    $password = $data['password'];
    $noTelepon = $data['no_telepon'] ?? '';

    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8 ||
        !is_string($noTelepon)
    ) {
        jsonResponse(422, [
            'status' => 'error',
            'message' => 'Email tidak valid, password minimal 8 karakter, atau nomor telepon tidak valid.',
        ]);
    }

    try {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO public.users
                (nama, email, password, role, no_telepon)
             VALUES
                (:nama, :email, :password, :role, :no_telepon)
             RETURNING id, nama, email, role'
        );

        $stmt->execute([
            'nama' => $nama,
            'email' => $email,
            'password' => $hashed,
            'role' => 'pelanggan',
            'no_telepon' => trim($noTelepon),
        ]);

        jsonResponse(201, [
            'status' => 'success',
            'message' => 'Berhasil daftar akun pelanggan.',
            'user' => $stmt->fetch(),
        ]);
    } catch (PDOException $e) {
        error_log(
            'Register failed [' . $e->getCode() . ']: ' . $e->getMessage()
        );

        if ($e->getCode() === '23505') {
            jsonResponse(409, [
                'status' => 'error',
                'message' => 'Email sudah terdaftar.',
            ]);
        }

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Registrasi gagal karena kesalahan server.',
        ]);
    }
}

// ==================================================
// CP 2: LOGIN
// ==================================================
if ($uri === '/api/login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        !is_array($data) ||
        !isset($data['email'], $data['password']) ||
        !is_string($data['email']) ||
        !is_string($data['password']) ||
        trim($data['email']) === '' ||
        $data['password'] === ''
    ) {
        jsonResponse(400, [
            'status' => 'error',
            'message' => 'Email dan password wajib diisi.',
        ]);
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id, nama, email, password, role
             FROM public.users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute([
            'email' => trim($data['email']),
        ]);

        $user = $stmt->fetch();

        if (
            !$user ||
            !password_verify($data['password'], $user['password'])
        ) {
            jsonResponse(401, [
                'status' => 'error',
                'message' => 'Email atau password salah.',
            ]);
        }

        // Buat token acak
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        // Simpan hash token ke database
        $tokenStmt = $pdo->prepare(
            'INSERT INTO public.auth_tokens
                (user_id, token_hash, expires_at)
             VALUES
                (:user_id, :token_hash, :expires_at)'
        );

        $tokenStmt->execute([
            'user_id' => $user['id'],
            'token_hash' => $tokenHash,
            'expires_at' => date(
                'Y-m-d H:i:s',
                time() + (8 * 60 * 60)
            ),
        ]);

        jsonResponse(200, [
            'status' => 'success',
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expires_in' => 28800,
            'user' => [
                'id' => $user['id'],
                'nama' => $user['nama'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ]);
    } catch (PDOException $e) {
        error_log(
            'Login failed [' . $e->getCode() . ']: ' . $e->getMessage()
        );

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Login gagal karena kesalahan server.',
        ]);
    }
}

// ==================================================
// CP 2: INFORMASI PENGGUNA LOGIN
// GET /api/me
// ==================================================
if ($uri === '/api/me' && $method === 'GET') {
    try {
        $user = authenticateUser($pdo);

        jsonResponse(200, [
            'status' => 'success',
            'message' => 'Autentikasi berhasil.',
            'user' => $user,
        ]);
    } catch (Throwable $e) {
        error_log(
            'ME endpoint error ['
            . get_class($e) . ']: ' . $e->getMessage()
        );

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Terjadi kesalahan server.',
        ]);
    }
}

// ==================================================
// CP 2: MEMBUAT PESANAN
// POST /api/orders
// ==================================================
if ($uri === '/api/orders' && $method === 'POST') {
    // Autentikasi pengguna
    try {
        $user = authenticateUser($pdo);
    } catch (Throwable $e) {
        error_log(
            'Order authentication failed: '
            . get_class($e) . ': ' . $e->getMessage()
        );

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Terjadi kesalahan autentikasi.',
        ]);
    }

    // Baca JSON pesanan
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        !is_array($data) ||
        !isset($data['items']) ||
        !is_array($data['items']) ||
        count($data['items']) === 0
    ) {
        jsonResponse(400, [
            'status' => 'error',
            'message' => 'Daftar items wajib diisi.',
        ]);
    }

    $catatan = $data['catatan'] ?? null;

    if ($catatan !== null && !is_string($catatan)) {
        jsonResponse(422, [
            'status' => 'error',
            'message' => 'Catatan harus berupa teks.',
        ]);
    }

    // Validasi layanan dan hitung harga dari database
    $items = [];
    $totalHarga = 0;

    try {
        foreach ($data['items'] as $item) {
            if (
                !is_array($item) ||
                !isset($item['layanan_id'], $item['jumlah']) ||
                filter_var(
                    $item['layanan_id'],
                    FILTER_VALIDATE_INT
                ) === false ||
                filter_var(
                    $item['jumlah'],
                    FILTER_VALIDATE_INT
                ) === false ||
                (int) $item['layanan_id'] < 1 ||
                (int) $item['jumlah'] < 1
            ) {
                jsonResponse(422, [
                    'status' => 'error',
                    'message' => 'Setiap item harus memiliki layanan_id dan jumlah yang valid.',
                ]);
            }

            $layananId = (int) $item['layanan_id'];
            $jumlah = (int) $item['jumlah'];

            $stmtLayanan = $pdo->prepare(
                'SELECT id, harga
                 FROM public.layanan
                 WHERE id = :id'
            );

            $stmtLayanan->execute([
                'id' => $layananId,
            ]);

            $layanan = $stmtLayanan->fetch();

            if (!$layanan) {
                jsonResponse(422, [
                    'status' => 'error',
                    'message' => "Layanan dengan ID {$layananId} tidak ditemukan.",
                ]);
            }

            $harga = (float) $layanan['harga'];
            $subtotal = $harga * $jumlah;

            $items[] = [
                'layanan_id' => $layananId,
                'jumlah' => $jumlah,
                'subtotal' => $subtotal,
            ];

            $totalHarga += $subtotal;
        }

        // Simpan pesanan dan detail dalam satu transaksi
        $pdo->beginTransaction();

        $kodePesanan = 'EP-' . date('YmdHis')
            . '-' . bin2hex(random_bytes(3));

        $stmtPesanan = $pdo->prepare(
            "INSERT INTO public.pesanan
                (kode_pesanan, user_id, total_harga, status, catatan)
             VALUES
                (:kode, :user_id, :total, :status, :catatan)
             RETURNING id, kode_pesanan, user_id,
                       total_harga, status, catatan, created_at"
        );

        $stmtPesanan->execute([
            'kode' => $kodePesanan,
            'user_id' => $user['id'],
            'total' => $totalHarga,
            'status' => 'Pending',
            'catatan' => $catatan,
        ]);

        $pesanan = $stmtPesanan->fetch();

        $stmtDetail = $pdo->prepare(
            'INSERT INTO public.detail_pesanan
                (pesanan_id, layanan_id, jumlah, subtotal)
             VALUES
                (:pesanan_id, :layanan_id, :jumlah, :subtotal)'
        );

        foreach ($items as $item) {
            $stmtDetail->execute([
                'pesanan_id' => $pesanan['id'],
                'layanan_id' => $item['layanan_id'],
                'jumlah' => $item['jumlah'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        $pdo->commit();

        jsonResponse(201, [
            'status' => 'success',
            'message' => 'Pesanan berhasil dibuat.',
            'pesanan' => $pesanan,
            'items' => $items,
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log(
            'Create order failed ['
            . get_class($e) . ']: ' . $e->getMessage()
        );

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Pesanan gagal dibuat karena kesalahan server.',
        ]);
    }
}


if ($uri === '/api/orders/upload' && $method === 'POST') {
    try {
        $user = authenticateUser($pdo);

        if (
            !isset($_POST['pesanan_id'], $_POST['layanan_id']) ||
            !ctype_digit((string) $_POST['pesanan_id']) ||
            !ctype_digit((string) $_POST['layanan_id']) ||
            !isset($_FILES['dokumen'])
        ) {
            jsonResponse(400, [
                'status' => 'error',
                'message' => 'pesanan_id, layanan_id, dan dokumen wajib diisi.',
            ]);
        }

        $pesananId = (int) $_POST['pesanan_id'];
        $layananId = (int) $_POST['layanan_id'];
        $file = $_FILES['dokumen'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            jsonResponse(400, [
                'status' => 'error',
                'message' => 'Upload file gagal.',
            ]);
        }

        if ($file['size'] <= 0 || $file['size'] > 10 * 1024 * 1024) {
            jsonResponse(422, [
                'status' => 'error',
                'message' => 'Ukuran file maksimal 10 MB.',
            ]);
        }

        $stmt = $pdo->prepare(
            'SELECT dp.id
             FROM public.detail_pesanan dp
             JOIN public.pesanan p ON p.id = dp.pesanan_id
             WHERE p.id = :pesanan_id
               AND p.user_id = :user_id
               AND dp.layanan_id = :layanan_id
             LIMIT 1'
        );

        $stmt->execute([
            'pesanan_id' => $pesananId,
            'user_id' => $user['id'],
            'layanan_id' => $layananId,
        ]);

        $detail = $stmt->fetch();

        if (!$detail) {
            jsonResponse(404, [
                'status' => 'error',
                'message' => 'Detail pesanan tidak ditemukan atau bukan milik Anda.',
            ]);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $extensions = [
            'application/pdf' => 'pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        if (!isset($extensions[$mime])) {
            jsonResponse(422, [
                'status' => 'error',
                'message' => 'File harus berupa PDF atau DOCX yang valid.',
            ]);
        }

        $uploadDir = dirname(__DIR__) . '/storage/uploads';

        if (
            !is_dir($uploadDir) &&
            !mkdir($uploadDir, 0750, true) &&
            !is_dir($uploadDir)
        ) {
            throw new RuntimeException('Folder upload tidak dapat dibuat.');
        }

        $filename = bin2hex(random_bytes(16))
            . '.' . $extensions[$mime];

        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('File gagal disimpan.');
        }

        $relativePath = 'storage/uploads/' . $filename;

        try {
            $update = $pdo->prepare(
                'UPDATE public.detail_pesanan
                 SET file_dokumen = :file
                 WHERE id = :id'
            );

            $update->execute([
                'file' => $relativePath,
                'id' => $detail['id'],
            ]);
        } catch (Throwable $e) {
            if (is_file($destination)) {
                unlink($destination);
            }

            throw $e;
        }

        jsonResponse(200, [
            'status' => 'success',
            'message' => 'Dokumen berhasil diunggah.',
            'pesanan_id' => $pesananId,
            'file_dokumen' => $relativePath,
        ]);
    } catch (Throwable $e) {
        error_log(
            'Upload failed [' . get_class($e) . ']: ' . $e->getMessage()
        );

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Upload gagal karena kesalahan server.',
        ]);
    }
}


if (
    preg_match('#^/api/orders/([0-9]+)/status$#', $uri, $matches)
    && $method === 'PATCH'
) {
    try {
        $user = authenticateUser($pdo);

        if (!in_array($user['role'], ['admin', 'owner'], true)) {
            jsonResponse(403, [
                'status' => 'error',
                'message' => 'Hanya admin atau owner yang dapat mengubah status pesanan.',
            ]);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $allowedStatuses = [
            'Pending',
            'Diverifikasi',
            'Diproses',
            'Tertunda',
            'Tertunda - Stok Tidak Tersedia',
            'Selesai',
            'Diambil',
        ];

        if (
            !is_array($data) ||
            !isset($data['status']) ||
            !is_string($data['status']) ||
            !in_array($data['status'], $allowedStatuses, true)
        ) {
            jsonResponse(422, [
                'status' => 'error',
                'message' => 'Status tidak valid.',
                'allowed_statuses' => $allowedStatuses,
            ]);
        }

        $orderId = (int) $matches[1];

        $stmt = $pdo->prepare(
            'UPDATE public.pesanan
             SET status = :status
             WHERE id = :id
             RETURNING id, kode_pesanan, status'
        );

        $stmt->execute([
            'status' => $data['status'],
            'id' => $orderId,
        ]);

        $order = $stmt->fetch();

        if (!$order) {
            jsonResponse(404, [
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ]);
        }

        jsonResponse(200, [
            'status' => 'success',
            'message' => 'Status pesanan berhasil diperbarui.',
            'order' => $order,
        ]);
    } catch (Throwable $e) {
        error_log(
            'Update order status failed: '
            . get_class($e) . ': ' . $e->getMessage()
        );

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Gagal memperbarui status pesanan.',
        ]);
    }
}


// GET /api/reports/order-summary?start_date=2026-10-01&end_date=2026-10-31
if ($uri === '/api/reports/order-summary' && $method === 'GET') {
    $user = authenticateUser($pdo);

    if (!in_array($user['role'], ['admin', 'owner'], true)) {
        jsonResponse(403, [
            'status' => 'error',
            'message' => 'Hanya admin atau owner yang dapat melihat laporan.',
        ]);
    }

    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';

    $isValidDate = static function (string $date): bool {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false
            && $parsed->format('Y-m-d') === $date;
    };

    if (
        !$isValidDate($startDate) ||
        !$isValidDate($endDate) ||
        $startDate > $endDate
    ) {
        jsonResponse(422, [
            'status' => 'error',
            'message' => 'Tanggal tidak valid. Gunakan YYYY-MM-DD dan pastikan tanggal awal tidak melebihi tanggal akhir.',
        ]);
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT jumlah_pesanan, total_nilai_pesanan
             FROM public.fn_ringkasan_pesanan(
                 CAST(:tanggal_awal AS date),
                 CAST(:tanggal_akhir AS date)
             )'
        );

        $stmt->execute([
            'tanggal_awal' => $startDate,
            'tanggal_akhir' => $endDate,
        ]);

        jsonResponse(200, [
            'status' => 'success',
            'message' => 'Ringkasan pesanan berhasil diambil.',
            'periode' => [
                'tanggal_awal' => $startDate,
                'tanggal_akhir' => $endDate,
            ],
            'data' => $stmt->fetch(),
        ]);
    } catch (Throwable $e) {
        error_log('Order summary report failed: ' . $e->getMessage());

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Gagal mengambil ringkasan pesanan.',
        ]);
    }
}

// ==================================================
// ENDPOINT TIDAK DITEMUKAN
// ==================================================


if ($uri === '/api/orders' && $method === 'GET') {
    try {
        $user = authenticateUser($pdo);

        $stmt = $pdo->prepare(
            'SELECT
                p.id,
                p.kode_pesanan,
                p.total_harga,
                p.status,
                p.catatan,
                p.created_at
             FROM public.pesanan p
             WHERE p.user_id = :user_id
             ORDER BY p.created_at DESC'
        );

        $stmt->execute([
            'user_id' => $user['id'],
        ]);

        $orders = $stmt->fetchAll();

        jsonResponse(200, [
            'status' => 'success',
            'message' => 'Daftar pesanan berhasil diambil.',
            'orders' => $orders,
        ]);
    } catch (Throwable $e) {
        error_log(
            'Get orders failed [' .
            get_class($e) . ']: ' . $e->getMessage()
        );

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Gagal mengambil daftar pesanan.',
        ]);
    }
}


 // GET /api/reports/order-details?start_date=2026-10-01&end_date=2026-10-31
if ($uri === '/api/reports/order-details' && $method === 'GET') {
    $user = authenticateUser($pdo);

    if (!in_array($user['role'], ['admin', 'owner'], true)) {
        jsonResponse(403, [
            'status' => 'error',
            'message' => 'Hanya admin atau owner yang dapat melihat laporan.',
        ]);
    }

    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';

    $isValidDate = static function (string $date): bool {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false
            && $parsed->format('Y-m-d') === $date;
    };

    if (
        !$isValidDate($startDate) ||
        !$isValidDate($endDate) ||
        $startDate > $endDate
    ) {
        jsonResponse(422, [
            'status' => 'error',
            'message' => 'Tanggal tidak valid. Gunakan YYYY-MM-DD.',
        ]);
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT *
             FROM public.v_detail_pesanan
             WHERE created_at >= CAST(:tanggal_awal AS date)
               AND created_at < CAST(:tanggal_akhir AS date) + INTERVAL \'1 day\'
             ORDER BY created_at DESC, pesanan_id DESC'
        );

        $stmt->execute([
            'tanggal_awal' => $startDate,
            'tanggal_akhir' => $endDate,
        ]);

        $data = $stmt->fetchAll();

        jsonResponse(200, [
            'status' => 'success',
            'message' => 'Detail laporan pesanan berhasil diambil.',
            'periode' => [
                'tanggal_awal' => $startDate,
                'tanggal_akhir' => $endDate,
            ],
            'jumlah_detail' => count($data),
            'data' => $data,
        ]);

    } catch (Throwable $e) {
        error_log('Order details report failed: ' . $e->getMessage());

        jsonResponse(500, [
            'status' => 'error',
            'message' => 'Gagal mengambil detail laporan pesanan.',
        ]);
    }
}



jsonResponse(404, [
    'status' => 'error',
    'message' => 'Endpoint tidak ditemukan.',
    'path' => $uri,
    'method' => $method,
]);
