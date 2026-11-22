<?php
// backend/controllers/OrderController.php
// CP-2: Pengajuan pesanan + upload, riwayat, detail/tracking, invoice
// CP-3: Verifikasi admin, perubahan status, potong stok otomatis

const STATUS_PESANAN = [
    'Pending', 'Diverifikasi', 'Diproses', 'Tertunda',
    'Tertunda - Stok Tidak Tersedia', 'Selesai', 'Diambil', 'Ditolak',
];

const TRANSISI_STATUS = [
    'Pending' => ['Diverifikasi', 'Diproses', 'Ditolak'],
    'Diverifikasi' => ['Diproses', 'Tertunda', 'Ditolak'],
    'Tertunda' => ['Diproses', 'Ditolak'],
    'Tertunda - Stok Tidak Tersedia' => ['Diproses', 'Ditolak'],
    'Diproses' => ['Selesai', 'Tertunda'],
    'Selesai' => ['Diambil'],
    'Diambil' => [],
    'Ditolak' => [],
];

const UPLOAD_MAKS = 10 * 1024 * 1024;

function upload_dir(): string
{
    return __DIR__ . '/../uploads/';
}

function order_find_for(array $aktor, int $id): array
{
    $sql = 'SELECT p.id, p.kode_pesanan, p.user_id, p.total_harga, p.status, p.catatan,
                   p.alasan_tolak, p.metode_bayar, p.dibayar_at, p.stok_dikurangi,
                   p.created_at, p.updated_at,
                   u.nama, u.email, u.no_telepon
            FROM public.pesanan p
            JOIN public.users u ON u.id = p.user_id
            WHERE p.id = :id';

    $params = ['id' => $id];

    if (!is_staff($aktor)) {
        $sql .= ' AND p.user_id = :uid';
        $params['uid'] = $aktor['id'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetch() ?: fail('Pesanan tidak ditemukan.', 404);
}

function order_items(int $id): array
{
    $stmt = db()->prepare(
        'SELECT dp.id, dp.layanan_id, l.nama_layanan, l.kategori, l.satuan,
                l.harga AS harga_satuan, dp.jumlah, dp.subtotal,
                dp.file_dokumen, dp.spesifikasi
         FROM public.detail_pesanan dp
         JOIN public.layanan l ON l.id = dp.layanan_id
         WHERE dp.pesanan_id = :id
         ORDER BY dp.id'
    );

    $stmt->execute(['id' => $id]);

    return $stmt->fetchAll();
}

function order_riwayat(int $id): array
{
    $stmt = db()->prepare(
        'SELECT r.status_lama, r.status_baru, r.keterangan,
                r.created_at, u.nama AS oleh, u.role AS peran
         FROM public.riwayat_status r
         LEFT JOIN public.users u ON u.id = r.diubah_oleh
         WHERE r.pesanan_id = :id
         ORDER BY r.created_at, r.id'
    );

    $stmt->execute(['id' => $id]);

    return $stmt->fetchAll();
}

// ------------------------------------------------------------
// POST /api/orders
// ------------------------------------------------------------
function order_store(): void
{
    $user = require_user();
    $pdo = db();

    $isMultipart = stripos(
        $_SERVER['CONTENT_TYPE'] ?? '',
        'multipart/form-data'
    ) === 0;

    if ($isMultipart) {
        $data = $_POST;
        $items = json_decode((string) ($data['items'] ?? ''), true);
    } else {
        $data = body();
        $items = $data['items'] ?? null;
    }

    if (!is_array($items) || count($items) < 1 || count($items) > 50) {
        fail('Pesanan harus memiliki 1 sampai 50 item layanan.');
    }

    $catatan = str_input($data, 'catatan', 500);

    $stmtLayanan = $pdo->prepare(
        'SELECT id, nama_layanan, harga, aktif
         FROM public.layanan
         WHERE id = :id'
    );

    $itemsValid = [];
    $totalHarga = 0.0;

    foreach ($items as $item) {
        $layananId = filter_var(
            $item['layanan_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $jumlah = filter_var(
            $item['jumlah'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$layananId || !$jumlah || $jumlah < 1 || $jumlah > 100000) {
            fail('ID layanan tidak valid atau jumlah minimal 1.');
        }

        $stmtLayanan->execute(['id' => $layananId]);
        $layanan = $stmtLayanan->fetch();

        if (!$layanan || !$layanan['aktif']) {
            fail('Layanan ID ' . $layananId . ' tidak ditemukan atau tidak aktif.');
        }

        $spek = isset($item['spesifikasi'])
            ? mb_substr(trim((string) $item['spesifikasi']), 0, 255)
            : null;

        $subtotal = round((float) $layanan['harga'] * $jumlah, 2);
        $totalHarga += $subtotal;

        $itemsValid[] = [
            'layanan_id' => (int) $layanan['id'],
            'nama_layanan' => $layanan['nama_layanan'],
            'jumlah' => $jumlah,
            'subtotal' => $subtotal,
            'spesifikasi' => $spek !== '' ? $spek : null,
        ];
    }

    $fileName = null;
    $filePath = null;

    if (
        $isMultipart &&
        isset($_FILES['file_dokumen']) &&
        $_FILES['file_dokumen']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['file_dokumen'];

        if (
            $file['error'] === UPLOAD_ERR_INI_SIZE ||
            $file['error'] === UPLOAD_ERR_FORM_SIZE ||
            $file['size'] > UPLOAD_MAKS
        ) {
            fail('Ukuran dokumen maksimal 10 MB.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            fail('Upload dokumen gagal.');
        }

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        $allowedMimes = [
            'pdf' => ['application/pdf'],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'application/x-zip',
                'application/octet-stream',
            ],
        ];

        if (!isset($allowedMimes[$extension])) {
            fail('Dokumen harus PDF atau DOCX.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

        if (!in_array($mime, $allowedMimes[$extension], true)) {
            fail('Isi dokumen tidak sesuai dengan format berkas.');
        }

        if (
            !is_dir(upload_dir()) &&
            !mkdir(upload_dir(), 0755, true) &&
            !is_dir(upload_dir())
        ) {
            throw new RuntimeException('Folder upload tidak dapat dibuat.');
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $filePath = upload_dir() . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new RuntimeException('Dokumen gagal disimpan.');
        }
    }

    try {
        $pdo->beginTransaction();

        $check = $pdo->prepare(
            'SELECT 1 FROM public.pesanan WHERE kode_pesanan = :kode'
        );

        $kodePesanan = null;

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $candidate = 'RI-' . date('Ymd') . '-' .
                str_pad(
                    (string) random_int(1, 999),
                    3,
                    '0',
                    STR_PAD_LEFT
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
            'catatan' => $catatan,
        ]);

        $pesanan = $stmt->fetch();

        $stmtDetail = $pdo->prepare(
            'INSERT INTO public.detail_pesanan
                (pesanan_id, layanan_id, jumlah, subtotal, file_dokumen, spesifikasi)
             VALUES
                (:pesanan_id, :layanan_id, :jumlah, :subtotal, :file_dokumen, :spesifikasi)'
        );

        foreach ($itemsValid as $index => $item) {
            $stmtDetail->execute([
                'pesanan_id' => $pesanan['id'],
                'layanan_id' => $item['layanan_id'],
                'jumlah' => $item['jumlah'],
                'subtotal' => $item['subtotal'],
                'file_dokumen' => $index === 0 ? $fileName : null,
                'spesifikasi' => $item['spesifikasi'],
            ]);
        }

        $stmtNotif = $pdo->prepare(
            "INSERT INTO public.notifikasi (pesanan_id, judul, pesan)
             VALUES (:pesanan_id, 'Pesanan Dibuat', :pesan)
             RETURNING id"
        );

        $stmtNotif->execute([
            'pesanan_id' => $pesanan['id'],
            'pesan' => 'Pesanan ' . $pesanan['kode_pesanan'] .
                ' berhasil dibuat dan menunggu verifikasi.',
        ]);

        $notifikasiId = $stmtNotif->fetchColumn();

        $pdo->prepare(
            "INSERT INTO public.notifikasi_users
                (notifikasi_id, user_id, is_read)
             SELECT :nid, u.id, FALSE
             FROM public.users u
             WHERE u.id = :uid OR u.role = 'admin'
             ON CONFLICT DO NOTHING"
        )->execute([
            'nid' => $notifikasiId,
            'uid' => $user['id'],
        ]);

        $pdo->commit();

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($filePath && is_file($filePath)) {
            unlink($filePath);
        }

        throw $e;
    }

    $pesanan['id'] = (int) $pesanan['id'];

    json_success([
        'message' => 'Pesanan berhasil diajukan.',
        'pesanan' => $pesanan,
        'kode_pesanan' => $pesanan['kode_pesanan'],
        'id_pesanan' => $pesanan['id'],
        'items' => $itemsValid,
        'file_dokumen' => $fileName,
    ], 201);
}

// ------------------------------------------------------------
// GET /api/orders
// ------------------------------------------------------------
function order_index(): void
{
    $actor = require_user();
    $where = [];
    $params = [];

    if (!is_staff($actor)) {
        $where[] = 'p.user_id = :uid';
        $params['uid'] = $actor['id'];
    }

    if (!empty($_GET['status'])) {
        $status = array_values(
            array_intersect(
                explode(',', (string) $_GET['status']),
                STATUS_PESANAN
            )
        );

        if (!$status) {
            fail('Filter status tidak valid.');
        }

        $in = [];

        foreach ($status as $i => $s) {
            $in[] = ":st$i";
            $params["st$i"] = $s;
        }

        $where[] = 'p.status IN (' . implode(',', $in) . ')';
    }

    $q = trim((string) ($_GET['q'] ?? ''));

    if ($q !== '') {
        $q = ltrim($q, '#');
        $digits = preg_replace('/\D+/', '', $q);

        $cond = 'p.kode_pesanan ILIKE :q OR u.nama ILIKE :q';
        $params['q'] = '%' . $q . '%';

        if (strlen($digits) >= 4) {
            $cond .= " OR regexp_replace(COALESCE(u.no_telepon, ''), '\\D', '', 'g') LIKE :tel";
            $params['tel'] = '%' . $digits . '%';
        }

        $where[] = "($cond)";
    }

    $sql = "
        SELECT p.id, p.kode_pesanan, p.user_id,
               u.nama AS nama_pelanggan, u.no_telepon,
               STRING_AGG(DISTINCT l.nama_layanan, ', ') AS layanan,
               p.total_harga, p.status, p.catatan, p.alasan_tolak,
               p.metode_bayar,
               STRING_AGG(DISTINCT dp.file_dokumen, ', ') AS berkas,
               COUNT(dp.id) AS jumlah_item,
               p.created_at, p.updated_at
        FROM public.pesanan p
        JOIN public.users u ON u.id = p.user_id
        LEFT JOIN public.detail_pesanan dp ON dp.pesanan_id = p.id
        LEFT JOIN public.layanan l ON l.id = dp.layanan_id
        " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
        GROUP BY p.id, u.nama, u.no_telepon
        ORDER BY p.created_at DESC
        LIMIT :limit";

    $params['limit'] = int_query('limit', 500, 1, 1000);

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    json_success(['data' => $stmt->fetchAll()]);
}

// ------------------------------------------------------------
// GET /api/orders/{id}[/invoice]
// ------------------------------------------------------------
function order_show(string $id, string $invoice = ''): void
{
    $actor = require_user();
    $order = order_find_for($actor, (int) $id);
    $items = order_items((int) $id);

    if ($invoice === 'invoice') {
        json_success([
            'invoice' => [
                'nomor' => 'INV-' . $order['kode_pesanan'],
                'kode_pesanan' => $order['kode_pesanan'],
                'tanggal' => $order['created_at'],
                'pelanggan' => $order['nama'],
                'email' => $order['email'],
                'no_telepon' => $order['no_telepon'],
                'status' => $order['status'],
                'metode_bayar' => $order['metode_bayar'],
                'dibayar_at' => $order['dibayar_at'],
                'lunas' => $order['dibayar_at'] !== null,
                'catatan' => $order['catatan'],
                'items' => $items,
                'total' => $order['total_harga'],
            ],
        ]);
    }

    $kebutuhan = [];

    if (is_staff($actor)) {
        $stmt = db()->prepare(
            'SELECT
                bl.bahan_baku_id,
                bb.nama_bahan,
                bb.satuan,
                SUM(bl.jumlah_kebutuhan * dp.jumlah) AS dibutuhkan,
                bb.stok AS stok_tersedia,
                (bb.stok >= SUM(bl.jumlah_kebutuhan * dp.jumlah)) AS cukup
             FROM public.detail_pesanan dp
             JOIN public.bahan_layanan bl
                ON bl.layanan_id = dp.layanan_id
             JOIN public.bahan_baku bb
                ON bb.id = bl.bahan_baku_id
             WHERE dp.pesanan_id = :id
             GROUP BY
                bl.bahan_baku_id,
                bb.nama_bahan,
                bb.satuan,
                bb.stok
             ORDER BY bb.nama_bahan'
        );

        $stmt->execute(['id' => (int) $id]);
        $kebutuhan = $stmt->fetchAll();
    }

    json_success([
        'pesanan' => $order,
        'items' => $items,
        'riwayat' => order_riwayat((int) $id),
        'kebutuhan_bahan' => $kebutuhan,
        'tracking' => [
            'status_saat_ini' => $order['status'],
            'dibuat_pada' => $order['created_at'],
        ],
    ]);
}

// ------------------------------------------------------------
// GET /api/orders/{id}/file
// ------------------------------------------------------------
function order_file(string $id): void
{
    $actor = require_user();
    order_find_for($actor, (int) $id);

    $stmt = db()->prepare(
        'SELECT file_dokumen
         FROM public.detail_pesanan
         WHERE pesanan_id = :id
           AND file_dokumen IS NOT NULL
         ORDER BY id
         LIMIT 1'
    );

    $stmt->execute(['id' => (int) $id]);
    $nama = $stmt->fetchColumn();

    $path = $nama ? upload_dir() . basename($nama) : null;

    if (!$path || !is_file($path)) {
        fail('Berkas dokumen tidak tersedia di server.', 404);
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path)
        ?: 'application/octet-stream';

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    header_remove('Content-Type');
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header(
        'Content-Disposition: ' .
        ($ext === 'pdf' ? 'inline' : 'attachment') .
        '; filename="' . basename($path) . '"'
    );
    header('X-Content-Type-Options: nosniff');

    readfile($path);
    exit;
}

// ------------------------------------------------------------
// PATCH /api/orders/status
// Body: {id_pesanan, status_pesanan, alasan?, metode_bayar?}
// ------------------------------------------------------------
function order_update_status(): void
{
    require_once __DIR__ . '/../models/StokModel.php';

    $aktor = require_user(['admin']);
    $data = body();

    $idPesanan = filter_var(
        $data['id_pesanan'] ?? null,
        FILTER_VALIDATE_INT
    );

    $statusBaru = (string) ($data['status_pesanan'] ?? '');

    if (!$idPesanan || !in_array($statusBaru, STATUS_PESANAN, true)) {
        fail('ID pesanan atau status tidak valid.');
    }

    $alasan = str_input($data, 'alasan', 500);
    $metode = str_input($data, 'metode_bayar', 20);

    if ($statusBaru === 'Ditolak' && $alasan === null) {
        fail('Alasan penolakan wajib diisi.');
    }

    if (
        $statusBaru === 'Diambil' &&
        !in_array($metode ?? 'Tunai', ['Tunai', 'QRIS', 'Transfer'], true)
    ) {
        fail('Metode pembayaran tidak valid.');
    }

    $pdo = db();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'SELECT id, kode_pesanan, user_id, status, stok_dikurangi
             FROM public.pesanan
             WHERE id = :id
             FOR UPDATE'
        );

        $stmt->execute(['id' => $idPesanan]);

        $pesanan = $stmt->fetch()
            ?: fail('Pesanan tidak ditemukan.', 404);

        $statusLama = $pesanan['status'];

        if ($statusLama === $statusBaru) {
            fail("Pesanan sudah berstatus $statusBaru.");
        }

        if (!in_array(
            $statusBaru,
            TRANSISI_STATUS[$statusLama] ?? [],
            true
        )) {
            fail(
                "Status tidak dapat diubah dari $statusLama menjadi $statusBaru.",
                409
            );
        }

        $pesanNotif = 'Status pesanan diperbarui menjadi: ' . $statusBaru;
        $keterangan = null;
        $extra = [];

        if ($statusBaru === 'Diproses') {
            if ($pesanan['stok_dikurangi']) {
                $pesanNotif = 'Pesanan Anda sedang diproses cetak oleh operator.';
            } else {
                $cekStok = kurangiStokPesanan(
                    $pdo,
                    (int) $idPesanan,
                    (int) $aktor['id']
                );

                if (!$cekStok['cukup']) {
                    $statusBaru = 'Tertunda - Stok Tidak Tersedia';

                    $keterangan = 'Stok ' .
                        $cekStok['bahan_kurang'] .
                        ' tidak mencukupi';

                    $pesanNotif = 'Pesanan tertunda karena stok ' .
                        $cekStok['bahan_kurang'] .
                        ' tidak mencukupi.';

                    if ($statusLama === $statusBaru) {
                        $pdo->rollBack();

                        json_success([
                            'id_pesanan' => (int) $idPesanan,
                            'status_pesanan' => $statusBaru,
                            'message' => $pesanNotif,
                            'stok_cukup' => false,
                        ]);
                    }
                } else {
                    $pdo->prepare(
                        'UPDATE public.pesanan
                         SET stok_dikurangi = TRUE
                         WHERE id = :id'
                    )->execute(['id' => $idPesanan]);

                    $pesanNotif = 'Pesanan Anda sedang diproses cetak oleh operator.';
                    $extra['stok_dipotong'] = $cekStok['dipotong'];
                }
            }
        } elseif ($statusBaru === 'Ditolak') {
            $keterangan = 'Alasan: ' . $alasan;

            $pdo->prepare(
                'UPDATE public.pesanan
                 SET alasan_tolak = :a
                 WHERE id = :id'
            )->execute([
                'a' => $alasan,
                'id' => $idPesanan,
            ]);

            $pesanNotif = 'Pesanan ditolak. ' . $keterangan;

        } elseif ($statusBaru === 'Diambil') {
            $metode ??= 'Tunai';
            $keterangan = 'Dibayar ' . $metode . ' di kasir';

            $pdo->prepare(
                'UPDATE public.pesanan
                 SET metode_bayar = :m,
                     dibayar_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            )->execute([
                'm' => $metode,
                'id' => $idPesanan,
            ]);

            $pesanNotif = 'Pesanan telah diambil dan dibayar (' . $metode . ').';

        } elseif ($statusBaru === 'Selesai') {
            $pesanNotif = 'Pesanan selesai dan siap diambil di outlet.';

        } elseif ($statusBaru === 'Tertunda') {
            $keterangan = $alasan;
        }

        // Trigger riwayat status dan notifikasi membaca konteks ini.
        set_konteks($pdo, [
            'user_id' => $aktor['id'],
            'keterangan' => $keterangan,
        ]);

        $pdo->prepare(
            'UPDATE public.pesanan
             SET status = :status
             WHERE id = :id'
        )->execute([
            'status' => $statusBaru,
            'id' => $idPesanan,
        ]);

        $pdo->commit();

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }

    json_success([
        'id_pesanan' => (int) $idPesanan,
        'status_pesanan' => $statusBaru,
        'message' => $pesanNotif,
        'stok_cukup' => $statusBaru !== 'Tertunda - Stok Tidak Tersedia',
    ] + $extra);
}

// ------------------------------------------------------------
// DELETE /api/orders/{id}
// ------------------------------------------------------------
function order_destroy(string $id): void
{
    $actor = require_user();
    $order = order_find_for($actor, (int) $id);

    if ($order['status'] !== 'Pending') {
        fail('Hanya pesanan berstatus Pending yang dapat dibatalkan.', 409);
    }

    if (is_staff($actor)) {
        fail('Admin menolak pesanan melalui status Ditolak, bukan menghapus.', 403);
    }

    $files = array_filter(
        array_column(order_items((int) $id), 'file_dokumen')
    );

    $stmt = db()->prepare(
        "DELETE FROM public.pesanan
         WHERE id = :id AND status = 'Pending'"
    );

    $stmt->execute(['id' => (int) $id]);

    $masihDipakai = db()->prepare(
        'SELECT 1
         FROM public.detail_pesanan
         WHERE file_dokumen = :f
         LIMIT 1'
    );

    foreach ($files as $f) {
        $masihDipakai->execute(['f' => $f]);

        $path = upload_dir() . basename($f);

        if (!$masihDipakai->fetchColumn() && is_file($path)) {
            unlink($path);
        }
    }

    json_success([
        'message' => 'Pesanan ' . $order['kode_pesanan'] . ' dibatalkan.',
    ]);
}

// ------------------------------------------------------------
// GET /api/track?kode=&telepon=
// ------------------------------------------------------------
function order_track_public(): void
{
    $kode = strtoupper(
        ltrim(trim((string) ($_GET['kode'] ?? '')), '#')
    );

    $telepon = preg_replace(
        '/\D+/',
        '',
        (string) ($_GET['telepon'] ?? '')
    );

    if ($kode === '' || $telepon === '') {
        fail('Nomor pesanan dan nomor telepon wajib diisi.');
    }

    $stmt = db()->prepare(
        "SELECT p.id, p.kode_pesanan, p.status, p.total_harga,
                p.created_at, p.alasan_tolak,
                u.no_telepon,
                (
                    SELECT STRING_AGG(l.nama_layanan, ', ' ORDER BY dp.id)
                    FROM public.detail_pesanan dp
                    JOIN public.layanan l ON l.id = dp.layanan_id
                    WHERE dp.pesanan_id = p.id
                ) AS layanan
         FROM public.pesanan p
         JOIN public.users u ON u.id = p.user_id
         WHERE upper(p.kode_pesanan) = :kode
           AND regexp_replace(
                COALESCE(u.no_telepon, ''),
                '\\D',
                '',
                'g'
           ) = :tel"
    );

    $stmt->execute([
        'kode' => $kode,
        'tel' => $telepon,
    ]);

    $order = $stmt->fetch()
        ?: fail(
            'Pesanan tidak ditemukan. Periksa kembali nomor pesanan dan nomor telepon Anda.',
            404
        );

    $riwayat = array_map(
        fn ($r) => [
            'status' => $r['status_baru'],
            'waktu' => $r['created_at'],
            'keterangan' => $r['keterangan'],
        ],
        order_riwayat((int) $order['id'])
    );

    $tel = $order['no_telepon'];
    $digits = preg_replace('/\D+/', '', (string) $tel);

    unset($order['id'], $order['no_telepon']);

    $order['telepon_samaran'] =
        substr($digits, 0, 4) . '-****-' . substr($digits, -4);

    json_success([
        'pesanan' => $order,
        'riwayat' => $riwayat,
    ]);
}