
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

// CP-1: Health Check
if ($uri === '/api/health' && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';

    try {
        $stmt = $pdo->query('SELECT current_database() AS database_name');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'message' => 'EasyPrint Backend REST API aktif',
            'database' => $result['database_name'],
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Koneksi database gagal.',
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Helper autentikasi token Bearer
function getAuthenticatedUser(PDO $pdo): ?array
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        return null;
    }

    $tokenHash = hash('sha256', trim($matches[1]));

    $stmt = $pdo->prepare(
        'SELECT u.id, u.nama, u.email, u.role
         FROM public.auth_tokens t
         JOIN public.users u ON u.id = t.user_id
         WHERE t.token_hash = :token_hash
           AND t.expires_at > CURRENT_TIMESTAMP'
    );
    $stmt->execute(['token_hash' => $tokenHash]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

// CP-2: Register
if ($uri === '/api/register' && $method === 'POST') {
    require_once __DIR__ . '/../config/database.php';

    $data = json_decode(file_get_contents('php://input'), true);

    if (
        !is_array($data) ||
        empty($data['nama']) ||
        empty($data['email']) ||
        empty($data['password'])
    ) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Nama, email, dan password wajib diisi.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $nama = trim($data['nama']);
    $email = strtolower(trim($data['email']));
    $password = $data['password'];
    $noTelepon = trim($data['no_telepon'] ?? '');

    if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Nama atau format email tidak valid.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Password minimal 8 karakter.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO public.users
                (nama, email, password, no_telepon, role)
             VALUES
                (:nama, :email, :password, :no_telepon, :role)
             RETURNING id, nama, email, role'
        );

        $stmt->execute([
            'nama' => $nama,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'no_telepon' => $noTelepon !== '' ? $noTelepon : null,
            'role' => 'pelanggan',
        ]);

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Registrasi berhasil.',
            'user' => $stmt->fetch(PDO::FETCH_ASSOC),
        ], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => 'Email sudah terdaftar.',
            ], JSON_UNESCAPED_UNICODE);
        } else {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Registrasi gagal.',
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    exit;
}

// CP-2: Login
if ($uri === '/api/login' && $method === 'POST') {
    require_once __DIR__ . '/../config/database.php';

    $data = json_decode(file_get_contents('php://input'), true);

    if (
        !is_array($data) ||
        empty($data['email']) ||
        !isset($data['password']) ||
        $data['password'] === ''
    ) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Email dan password wajib diisi.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id, nama, email, password, role
             FROM public.users
             WHERE email = :email'
        );
        $stmt->execute([
            'email' => strtolower(trim($data['email'])),
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Email atau password salah.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare(
            "INSERT INTO public.auth_tokens
                (user_id, token_hash, expires_at)
             VALUES
                (:user_id, :token_hash, CURRENT_TIMESTAMP + INTERVAL '8 hours')"
        );
        $stmt->execute([
            'user_id' => $user['id'],
            'token_hash' => hash('sha256', $token),
        ]);

        unset($user['password']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expires_in' => 28800,
            'user' => $user,
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Login gagal.',
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// CP-2: Profil pengguna
if ($uri === '/api/me' && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';

    $user = getAuthenticatedUser($pdo);

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Token tidak valid, kedaluwarsa, atau tidak disertakan.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT id, nama, email, no_telepon, role
         FROM public.users
         WHERE id = :id'
    );
    $stmt->execute(['id' => $user['id']]);

    echo json_encode([
        'status' => 'success',
        'user' => $stmt->fetch(PDO::FETCH_ASSOC),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// CP-2: Membuat pesanan, dengan dukungan upload PDF/DOCX
if ($uri === '/api/orders' && $method === 'POST') {
    require_once __DIR__ . '/../config/database.php';

    $filePath = null;
    $fileName = null;
    $fileMoved = false;

    try {
        $user = getAuthenticatedUser($pdo);

        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Login diperlukan untuk mengajukan pesanan.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isMultipart = stripos($contentType, 'multipart/form-data') === 0;

        if ($isMultipart) {
            $data = $_POST;
            $items = json_decode($data['items'] ?? '', true);
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            $items = is_array($data) ? ($data['items'] ?? null) : null;
        }

        if (
            !is_array($data) ||
            !is_array($items) ||
            count($items) < 1 ||
            count($items) > 50
        ) {
            throw new InvalidArgumentException(
                'Pesanan harus memiliki 1 sampai 50 item layanan.'
            );
        }

        // Validasi dokumen bila dikirim.
        if (
            $isMultipart &&
            isset($_FILES['file_dokumen']) &&
            $_FILES['file_dokumen']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $file = $_FILES['file_dokumen'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException('Upload dokumen gagal.');
            }

            if ($file['size'] > 10 * 1024 * 1024) {
                throw new InvalidArgumentException('Ukuran dokumen maksimal 10 MB.');
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, ['pdf', 'docx'], true)) {
                throw new InvalidArgumentException('Dokumen harus PDF atau DOCX.');
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $allowedMimes = [
                'pdf' => ['application/pdf'],
                'docx' => [
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/zip',
                    'application/x-zip',
                ],
            ];

            if (!in_array($mime, $allowedMimes[$extension], true)) {
                throw new InvalidArgumentException(
                    'Isi dokumen tidak sesuai dengan format berkas.'
                );
            }

            $uploadDir = __DIR__ . '/../uploads/';

            if (!is_dir($uploadDir) &&
                !mkdir($uploadDir, 0755, true) &&
                !is_dir($uploadDir)
            ) {
                throw new RuntimeException('Folder upload tidak dapat dibuat.');
            }

            $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new RuntimeException('Dokumen gagal disimpan.');
            }

            $fileMoved = true;
        }

        $itemsValid = [];
        $totalHarga = 0;

        foreach ($items as $item) {
            $layananId = filter_var(
                $item['layanan_id'] ?? null,
                FILTER_VALIDATE_INT
            );
            $jumlah = filter_var(
                $item['jumlah'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$layananId || !$jumlah || $jumlah < 1) {
                throw new InvalidArgumentException(
                    'ID layanan tidak valid atau jumlah minimal 1.'
                );
            }

            $stmt = $pdo->prepare(
                'SELECT id, nama_layanan, harga
                 FROM public.layanan
                 WHERE id = :id'
            );
            $stmt->execute(['id' => $layananId]);
            $layanan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$layanan) {
                throw new InvalidArgumentException(
                    'Layanan ID ' . $layananId . ' tidak ditemukan.'
                );
            }

            $subtotal = (float) $layanan['harga'] * $jumlah;
            $totalHarga += $subtotal;

            $itemsValid[] = [
                'layanan_id' => (int) $layanan['id'],
                'jumlah' => $jumlah,
                'subtotal' => $subtotal,
            ];
        }

        $kodePesanan = null;

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $candidate = 'RI-' . date('Ymd') . '-'
                . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);

            $check = $pdo->prepare(
                'SELECT 1 FROM public.pesanan WHERE kode_pesanan = :kode'
            );
            $check->execute(['kode' => $candidate]);

            if (!$check->fetchColumn()) {
                $kodePesanan = $candidate;
                break;
            }
        }

        if ($kodePesanan === null) {
            throw new RuntimeException('Gagal membuat kode pesanan unik.');
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "INSERT INTO public.pesanan
                (kode_pesanan, user_id, total_harga, status, catatan)
             VALUES
                (:kode, :user_id, :total, 'Pending', :catatan)
             RETURNING id, kode_pesanan, total_harga, status, created_at"
        );
        $stmt->execute([
            'kode' => $kodePesanan,
            'user_id' => $user['id'],
            'total' => $totalHarga,
            'catatan' => trim($data['catatan'] ?? '') ?: null,
        ]);

        $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);

        // Pastikan kolom file_dokumen tersedia di detail_pesanan.
        $stmtDetail = $pdo->prepare(
            'INSERT INTO public.detail_pesanan
                (pesanan_id, layanan_id, jumlah, subtotal, file_dokumen)
             VALUES
                (:pesanan_id, :layanan_id, :jumlah, :subtotal, :file_dokumen)'
        );

        foreach ($itemsValid as $index => $item) {
            $stmtDetail->execute([
                'pesanan_id' => $pesanan['id'],
                'layanan_id' => $item['layanan_id'],
                'jumlah' => $item['jumlah'],
                'subtotal' => $item['subtotal'],
                'file_dokumen' => $index === 0 ? $fileName : null,
            ]);
        }

        $pdo->commit();

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Pesanan berhasil diajukan.',
            'pesanan' => $pesanan,
            'items' => $itemsValid,
            'file_dokumen' => $fileName,
        ], JSON_UNESCAPED_UNICODE);
    } catch (InvalidArgumentException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($fileMoved && $filePath && is_file($filePath)) {
            unlink($filePath);
        }

        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($fileMoved && $filePath && is_file($filePath)) {
            unlink($filePath);
        }

        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Pengajuan pesanan gagal.',
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// CP-3: Update status pesanan, pengurangan stok, dan notifikasi
if ($uri === '/api/orders/status' && $method === 'PATCH') {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/StokModel.php';

    $data = json_decode(file_get_contents('php://input'), true);
    $idPesanan = filter_var(
        is_array($data) ? ($data['id_pesanan'] ?? null) : null,
        FILTER_VALIDATE_INT
    );
    $statusBaru = is_array($data) ? ($data['status_pesanan'] ?? '') : '';

    $statusValid = [
        'Pending',
        'Diverifikasi',
        'Diproses',
        'Tertunda',
        'Tertunda - Stok Tidak Tersedia',
        'Selesai',
        'Diambil',
    ];

    if (!$idPesanan || !in_array($statusBaru, $statusValid, true)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'ID pesanan atau status tidak valid.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $aktor = getAuthenticatedUser($pdo);

        if (!$aktor) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token tidak valid atau kedaluwarsa.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($aktor['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Hanya admin yang dapat mengubah status.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'SELECT id, kode_pesanan, user_id, status, stok_dikurangi
             FROM public.pesanan
             WHERE id = :id
             FOR UPDATE'
        );
        $stmt->execute(['id' => $idPesanan]);
        $pesanan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pesanan) {
            throw new InvalidArgumentException('Pesanan tidak ditemukan.');
        }

        $statusLama = $pesanan['status'];
        $pesanNotif = 'Status pesanan diperbarui menjadi: ' . $statusBaru;

        if ($statusBaru === 'Diproses' && $statusLama !== 'Diproses') {
            if ($pesanan['stok_dikurangi']) {
                throw new InvalidArgumentException(
                    'Stok pesanan ini sudah ditandai pernah dikurangi.'
                );
            }

            $cekStok = kurangiStokPesanan($pdo, (int) $idPesanan);

            if (!$cekStok['cukup']) {
                $statusBaru = 'Tertunda - Stok Tidak Tersedia';
                $pesanNotif = 'Pesanan tertunda karena stok '
                    . $cekStok['bahan_kurang'] . ' tidak mencukupi.';
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE public.pesanan
                     SET stok_dikurangi = TRUE
                     WHERE id = :id'
                );
                $stmt->execute(['id' => $idPesanan]);

                $pesanNotif = 'Pesanan Anda sedang diproses cetak oleh operator.';
            }
        }

        $stmt = $pdo->prepare(
            'UPDATE public.pesanan SET status = :status WHERE id = :id'
        );
        $stmt->execute([
            'status' => $statusBaru,
            'id' => $idPesanan,
        ]);

        // Trigger PostgreSQL membuat satu notifikasi ketika status berubah.
        if ($statusLama !== $statusBaru) {
            $stmt = $pdo->prepare(
                'SELECT id FROM public.notifikasi
                 WHERE pesanan_id = :pesanan_id
                 ORDER BY id DESC LIMIT 1'
            );
            $stmt->execute(['pesanan_id' => $idPesanan]);
            $idNotifikasi = $stmt->fetchColumn();

            if (!$idNotifikasi) {
                throw new RuntimeException(
                    'Notifikasi perubahan status tidak ditemukan.'
                );
            }

            $stmt = $pdo->prepare(
                'INSERT INTO public.notifikasi_users (notifikasi_id, user_id)
                 VALUES (:notifikasi_id, :user_id)
                 ON CONFLICT DO NOTHING'
            );
            $stmt->execute([
                'notifikasi_id' => $idNotifikasi,
                'user_id' => $pesanan['user_id'],
            ]);
        }

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'id_pesanan' => (int) $idPesanan,
            'status_pesanan' => $statusBaru,
            'message' => $pesanNotif,
        ], JSON_UNESCAPED_UNICODE);
    } catch (InvalidArgumentException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memperbarui status pesanan.',
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// CP-3: Dashboard owner/admin
if ($uri === '/api/dashboard' && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';

    try {
        $aktor = getAuthenticatedUser($pdo);

        if (!$aktor) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token tidak valid atau kedaluwarsa.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!in_array($aktor['role'], ['admin', 'owner'], true)) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Akses dashboard ditolak.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $rekapBulanan = $pdo->query(
            "SELECT
                EXTRACT(YEAR FROM created_at)::integer AS tahun,
                EXTRACT(MONTH FROM created_at)::integer AS bulan,
                SUM(total_harga) AS pendapatan,
                COUNT(id) AS total_pesanan
             FROM public.pesanan
             WHERE status IN ('Selesai', 'Diambil')
             GROUP BY
                EXTRACT(YEAR FROM created_at),
                EXTRACT(MONTH FROM created_at)
             ORDER BY tahun, bulan"
        )->fetchAll(PDO::FETCH_ASSOC);

        $stokKritis = $pdo->query(
            'SELECT id, nama_bahan, stok, satuan, reorder_point
             FROM public.bahan_baku
             WHERE stok <= reorder_point
             ORDER BY stok ASC'
        )->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'rekap_omzet' => $rekapBulanan,
            'stok_kritis' => $stokKritis,
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data dashboard.',
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}


// CP-2: Katalog layanan
if ($uri === '/api/layanan' && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';
    try {
        $stmt = $pdo->query(
            'SELECT id, nama_layanan, harga
             FROM public.layanan
             ORDER BY id'
        );
        echo json_encode([
            'status' => 'success',
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil katalog layanan.']);
    }
    exit;
}

// CP-2: CRUD layanan untuk admin/owner (kolom yang digunakan: nama_layanan, harga)
if (in_array($uri, ['/api/layanan', '/api/layanan/'], true)
    && in_array($method, ['POST'], true)) {
    require_once __DIR__ . '/../config/database.php';
    try {
        $actor = getAuthenticatedUser($pdo);
        if (!$actor) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token Bearer tidak valid atau tidak disertakan.']);
            exit;
        }
        if (!in_array($actor['role'], ['admin', 'owner'], true)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Hanya admin/owner yang dapat mengelola layanan.']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $nama = trim((string)($data['nama_layanan'] ?? ''));
        $harga = filter_var($data['harga'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($nama === '' || $harga === false || $harga < 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'nama_layanan dan harga valid wajib diisi.']);
            exit;
        }
        $stmt = $pdo->prepare(
            'INSERT INTO public.layanan (nama_layanan, harga)
             VALUES (:nama, :harga)
             RETURNING id, nama_layanan, harga'
        );
        $stmt->execute(['nama' => $nama, 'harga' => $harga]);
        http_response_code(201);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetch(PDO::FETCH_ASSOC)], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan layanan.']);
    }
    exit;
}

// CP-2: Update/hapus layanan berdasarkan ID
if (preg_match('#^/api/layanan/([0-9]+)$#', $uri, $m)
    && in_array($method, ['PATCH', 'PUT', 'DELETE'], true)) {
    require_once __DIR__ . '/../config/database.php';
    try {
        $actor = getAuthenticatedUser($pdo);
        if (!$actor) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token Bearer tidak valid atau tidak disertakan.']);
            exit;
        }
        if (!in_array($actor['role'], ['admin', 'owner'], true)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Hanya admin/owner yang dapat mengelola layanan.']);
            exit;
        }
        $id = (int)$m[1];
        if ($method === 'DELETE') {
            $stmt = $pdo->prepare('DELETE FROM public.layanan WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if (!$stmt->rowCount()) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Layanan tidak ditemukan.']);
            } else {
                echo json_encode(['status' => 'success', 'message' => 'Layanan dihapus.']);
            }
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Body JSON tidak valid.']);
            exit;
        }
        $nama = trim((string)($data['nama_layanan'] ?? ''));
        $harga = filter_var($data['harga'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($nama === '' || $harga === false || $harga < 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'nama_layanan dan harga valid wajib diisi.']);
            exit;
        }
        $stmt = $pdo->prepare(
            'UPDATE public.layanan
             SET nama_layanan = :nama, harga = :harga
             WHERE id = :id
             RETURNING id, nama_layanan, harga'
        );
        $stmt->execute(['nama' => $nama, 'harga' => $harga, 'id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Layanan tidak ditemukan.']);
        } else {
            echo json_encode(['status' => 'success', 'data' => $row], JSON_UNESCAPED_UNICODE);
        }
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengelola layanan. Pastikan layanan belum digunakan oleh pesanan jika ingin menghapusnya.']);
    }
    exit;
}

// CP-2: Riwayat pesanan pelanggan / seluruh pesanan untuk admin-owner
if ($uri === '/api/orders' && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';
    try {
        $actor = getAuthenticatedUser($pdo);
        if (!$actor) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token Bearer wajib disertakan.']);
            exit;
        }
        $sql = 'SELECT p.id, p.kode_pesanan, p.total_harga, p.status, p.catatan, p.created_at
                FROM public.pesanan p';
        if (!in_array($actor['role'], ['admin', 'owner'], true)) {
            $sql .= ' WHERE p.user_id = :uid';
        }
        $sql .= ' ORDER BY p.created_at DESC';
        $stmt = $pdo->prepare($sql);
        if (!in_array($actor['role'], ['admin', 'owner'], true)) {
            $stmt->execute(['uid' => $actor['id']]);
        } else {
            $stmt->execute();
        }
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil riwayat pesanan.']);
    }
    exit;
}

// CP-2: Detail, tracking status, dan data invoice (JSON)
if (preg_match('#^/api/orders/([0-9]+)(?:/(invoice))?$#', $uri, $m)
    && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';
    try {
        $actor = getAuthenticatedUser($pdo);
        if (!$actor) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token Bearer wajib disertakan.']);
            exit;
        }
        $id = (int)$m[1];
        $staff = in_array($actor['role'], ['admin', 'owner'], true);
        $sql = 'SELECT p.id, p.kode_pesanan, p.user_id, p.total_harga, p.status,
                       p.catatan, p.created_at, u.nama, u.email
                FROM public.pesanan p
                JOIN public.users u ON u.id = p.user_id
                WHERE p.id = :id';
        if (!$staff) {
            $sql .= ' AND p.user_id = :uid';
        }
        $stmt = $pdo->prepare($sql);
        $params = ['id' => $id];
        if (!$staff) {
            $params['uid'] = $actor['id'];
        }
        $stmt->execute($params);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.']);
            exit;
        }
        $stmt = $pdo->prepare(
            'SELECT dp.layanan_id, l.nama_layanan, dp.jumlah, dp.subtotal, dp.file_dokumen
             FROM public.detail_pesanan dp
             JOIN public.layanan l ON l.id = dp.layanan_id
             WHERE dp.pesanan_id = :id ORDER BY dp.id'
        );
        $stmt->execute(['id' => $id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (($m[2] ?? '') === 'invoice') {
            echo json_encode(['status' => 'success', 'invoice' => [
                'nomor' => $order['kode_pesanan'],
                'tanggal' => $order['created_at'],
                'pelanggan' => $order['nama'],
                'email' => $order['email'],
                'status' => $order['status'],
                'items' => $items,
                'total' => $order['total_harga'],
            ]], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['status' => 'success', 'pesanan' => $order, 'items' => $items,
                'tracking' => ['status_saat_ini' => $order['status'], 'dibuat_pada' => $order['created_at']]],
                JSON_UNESCAPED_UNICODE);
        }
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil detail pesanan.']);
    }
    exit;
}

// CP-3: Daftar notifikasi pengguna
if ($uri === '/api/notifications' && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';
    try {
        $actor = getAuthenticatedUser($pdo);
        if (!$actor) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token Bearer wajib disertakan.']);
            exit;
        }
        $stmt = $pdo->prepare(
            'SELECT n.id, n.pesanan_id, n.judul, n.pesan, n.created_at, nu.is_read
             FROM public.notifikasi_users nu
             JOIN public.notifikasi n ON n.id = nu.notifikasi_id
             WHERE nu.user_id = :uid
             ORDER BY n.created_at DESC'
        );
        $stmt->execute(['uid' => $actor['id']]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil notifikasi.']);
    }
    exit;
}

// CP-3: Tandai notifikasi dibaca
if ($uri === '/api/notifications/read' && $method === 'PATCH') {
    require_once __DIR__ . '/../config/database.php';
    try {
        $actor = getAuthenticatedUser($pdo);
        if (!$actor) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token Bearer wajib disertakan.']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $id = filter_var(is_array($data) ? ($data['id_notifikasi'] ?? null) : null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'id_notifikasi wajib berupa angka positif.']);
            exit;
        }
        $stmt = $pdo->prepare(
            'UPDATE public.notifikasi_users SET is_read = TRUE
             WHERE notifikasi_id = :id AND user_id = :uid'
        );
        $stmt->execute(['id' => $id, 'uid' => $actor['id']]);
        if (!$stmt->rowCount()) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Notifikasi tidak ditemukan atau sudah dibaca.']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Notifikasi ditandai sudah dibaca.']);
        }
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui notifikasi.']);
    }
    exit;
}

// CP-3: Lihat stok dan reorder point
if ($uri === '/api/stok' && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';
    try {
        $actor = getAuthenticatedUser($pdo);
        if (!$actor) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token Bearer wajib disertakan.']);
            exit;
        }
        if (!in_array($actor['role'], ['admin', 'owner'], true)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Akses stok hanya untuk admin/owner.']);
            exit;
        }
        $stmt = $pdo->query(
            'SELECT id, nama_bahan, stok, satuan, reorder_point, status, harga_satuan
             FROM public.bahan_baku ORDER BY nama_bahan'
        );
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data stok.']);
    }
    exit;
}

// CP-3: Update stok/reorder point
if (preg_match('#^/api/stok/([0-9]+)$#', $uri, $m) && $method === 'PATCH') {
    require_once __DIR__ . '/../config/database.php';
    try {
        $actor = getAuthenticatedUser($pdo);
        if (!$actor) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token Bearer wajib disertakan.']);
            exit;
        }
        if (!in_array($actor['role'], ['admin', 'owner'], true)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Akses stok hanya untuk admin/owner.']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data) || (!array_key_exists('stok', $data) && !array_key_exists('reorder_point', $data))) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Kirim stok dan/atau reorder_point.']);
            exit;
        }
        $fields = [];
        $params = ['id' => (int)$m[1]];
        foreach (['stok', 'reorder_point'] as $field) {
            if (array_key_exists($field, $data)) {
                if (!is_numeric($data[$field]) || (float)$data[$field] < 0) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => "$field harus angka nol atau lebih."]);
                    exit;
                }
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        $stmt = $pdo->prepare(
            'UPDATE public.bahan_baku SET ' . implode(', ', $fields) .
            ' WHERE id = :id RETURNING id, nama_bahan, stok, satuan, reorder_point'
        );
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Bahan baku tidak ditemukan.']);
        } else {
            echo json_encode(['status' => 'success', 'data' => $row], JSON_UNESCAPED_UNICODE);
        }
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui stok.']);
    }
    exit;
}

// CP-3: Laporan kondisi stok dan bahan di bawah reorder point
if ($uri === '/api/laporan/stok' && $method === 'GET') {
    require_once __DIR__ . '/../config/database.php';
    try {
        $actor = getAuthenticatedUser($pdo);
        if (!$actor) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token Bearer wajib disertakan.']);
            exit;
        }
        if (!in_array($actor['role'], ['admin', 'owner'], true)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Akses laporan stok ditolak.']);
            exit;
        }
        $stmt = $pdo->query(
            'SELECT id, nama_bahan, stok, satuan, reorder_point,
                    CASE WHEN stok <= reorder_point THEN TRUE ELSE FALSE END AS stok_kritis
             FROM public.bahan_baku ORDER BY nama_bahan'
        );
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil laporan stok.']);
    }
    exit;
}


// Route tidak ditemukan
http_response_code(404);
echo json_encode([
    'status' => 'error',
    'message' => 'Endpoint tidak ditemukan.',
], JSON_UNESCAPED_UNICODE);
