
<?php
// backend/controllers/StokController.php
// CP-3: Stok bahan baku, reorder point, restock,
// stock adjustment, dan riwayat mutasi stok.

/**
 * Mencatat perubahan stok.
 * Harus dipanggil di dalam transaksi database.
 */
function stok_catat_riwayat(
    PDO $pdo,
    int $bahanId,
    ?int $userId,
    ?int $pesananId,
    string $jenis,
    float $jumlah,
    float $sebelum,
    float $sesudah,
    ?string $keterangan,
    ?string $tanggal = null
): void {
    $stmt = $pdo->prepare(
        'INSERT INTO public.riwayat_stok
            (
                bahan_baku_id,
                user_id,
                pesanan_id,
                jenis,
                jumlah,
                stok_sebelum,
                stok_sesudah,
                keterangan,
                created_at
            )
         VALUES
            (
                :bahan,
                :user_id,
                :pesanan_id,
                :jenis,
                :jumlah,
                :sebelum,
                :sesudah,
                :keterangan,
                COALESCE(CAST(:tanggal AS timestamp), CURRENT_TIMESTAMP)
            )'
    );

    $stmt->execute([
        'bahan' => $bahanId,
        'user_id' => $userId,
        'pesanan_id' => $pesananId,
        'jenis' => $jenis,
        'jumlah' => $jumlah,
        'sebelum' => $sebelum,
        'sesudah' => $sesudah,
        'keterangan' => $keterangan,
        'tanggal' => $tanggal,
    ]);
}


// ============================================================
// GET /api/stok
// ============================================================

function stok_index(): void
{
    require_user(['admin', 'owner']);

    $rows = db()->query(
        "SELECT
            b.id,
            b.nama_bahan,
            b.stok,
            b.satuan,
            b.reorder_point,
            b.status,
            b.harga_satuan,
            (b.stok <= b.reorder_point) AS stok_kritis,

            COALESCE(m.masuk_bulan_ini, 0) AS masuk_bulan_ini,
            COALESCE(m.keluar_bulan_ini, 0) AS keluar_bulan_ini,

            (
                SELECT COUNT(*)
                FROM public.bahan_layanan bl
                WHERE bl.bahan_baku_id = b.id
            ) AS dipakai_layanan

         FROM public.bahan_baku b

         LEFT JOIN (
            SELECT
                bahan_baku_id,
                SUM(jumlah) FILTER (
                    WHERE jenis = 'masuk'
                      AND jumlah > 0
                ) AS masuk_bulan_ini,
                SUM(ABS(jumlah)) FILTER (
                    WHERE jenis = 'keluar'
                ) AS keluar_bulan_ini
            FROM public.riwayat_stok
            WHERE created_at >= date_trunc('month', CURRENT_DATE)
            GROUP BY bahan_baku_id
         ) m ON m.bahan_baku_id = b.id

         ORDER BY b.nama_bahan"
    )->fetchAll();

    json_success(['data' => $rows]);
}


// ============================================================
// POST /api/stok
// Menambahkan bahan baku baru (owner)
// ============================================================

function stok_store(): void
{
    $aktor = require_user(['owner']);
    $d = body();

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $nama = str_input($d, 'nama_bahan', 100, true, 'Nama bahan');
        $stok = num_input($d, 'stok', 0, false, 'Stok') ?? 0;
        $satuan = str_input($d, 'satuan', 20, true, 'Satuan');
        $rop = num_input($d, 'reorder_point', 0, false, 'Reorder point') ?? 5;
        $harga = num_input($d, 'harga_satuan', 0, false, 'Harga satuan') ?? 0;

        set_konteks($pdo, [
            'user_id' => $aktor['id'],
            'keterangan_stok' => 'Stok awal bahan baru',
        ]);

        $stmt = $pdo->prepare(
            'INSERT INTO public.bahan_baku
                (nama_bahan, stok, satuan, reorder_point, harga_satuan)
             VALUES
                (:nama, :stok, :satuan, :rop, :harga)
             RETURNING
                id, nama_bahan, stok, satuan,
                reorder_point, status, harga_satuan'
        );

        $stmt->execute([
            'nama' => $nama,
            'stok' => $stok,
            'satuan' => $satuan,
            'rop' => $rop,
            'harga' => $harga,
        ]);

        $row = $stmt->fetch();

        // Catat stok awal jika bahan dibuat dengan stok > 0.
        if ($stok > 0) {
            stok_catat_riwayat(
                $pdo,
                (int) $row['id'],
                (int) $aktor['id'],
                null,
                'masuk',
                (float) $stok,
                0,
                (float) $stok,
                'Stok awal bahan baru'
            );
        }

        $pdo->commit();

        json_success([
            'message' => 'Bahan baku ditambahkan.',
            'data' => $row,
        ], 201);

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}


// ============================================================
// PATCH /api/stok/{id}
// Admin: koreksi stok
// Owner: koreksi stok dan data master
// ============================================================

function stok_update(string $id): void
{
    $aktor = require_user(['admin', 'owner']);
    $d = body();

    $kolomOwner = [
        'nama_bahan',
        'satuan',
        'reorder_point',
        'harga_satuan',
    ];

    $fields = [];
    $params = ['id' => (int) $id];

    $adaKoreksiStok = array_key_exists('stok', $d);
    $stokBaru = null;

    if ($adaKoreksiStok) {
        $stokBaru = num_input($d, 'stok', 0, true, 'Stok');
        $fields[] = 'stok = :stok';
        $params['stok'] = $stokBaru;
    }

    foreach ($kolomOwner as $k) {
        if (!array_key_exists($k, $d)) {
            continue;
        }

        if ($aktor['role'] !== 'owner') {
            fail(
                'Hanya owner yang dapat mengubah data master bahan / reorder point.',
                403
            );
        }

        $fields[] = "$k = :$k";

        $params[$k] = in_array($k, ['nama_bahan', 'satuan'], true)
            ? str_input(
                $d,
                $k,
                $k === 'satuan' ? 20 : 100,
                true,
                $k
            )
            : num_input($d, $k, 0, true, $k);
    }

    if (!$fields) {
        fail('Kirim stok dan/atau data master bahan.');
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stokLama = null;

        // Ambil stok lama dan kunci baris jika melakukan koreksi.
        if ($adaKoreksiStok) {
            $cek = $pdo->prepare(
                'SELECT stok
                 FROM public.bahan_baku
                 WHERE id = :id
                 FOR UPDATE'
            );
            $cek->execute(['id' => (int) $id]);
            $stokLama = $cek->fetchColumn();

            if ($stokLama === false) {
                fail('Bahan baku tidak ditemukan.', 404);
            }
        }

        $keterangan = str_input($d, 'keterangan', 255)
            ?? 'Penyesuaian stok manual (stock opname)';

        set_konteks($pdo, [
            'user_id' => $aktor['id'],
            'jenis_stok' => 'koreksi',
            'keterangan_stok' => $keterangan,
        ]);

        $stmt = $pdo->prepare(
            'UPDATE public.bahan_baku
             SET ' . implode(', ', $fields) . '
             WHERE id = :id
             RETURNING
                id, nama_bahan, stok, satuan,
                reorder_point, status, harga_satuan'
        );

        $stmt->execute($params);
        $row = $stmt->fetch();

        if (!$row) {
            fail('Bahan baku tidak ditemukan.', 404);
        }

        // Catat histori hanya jika kolom stok yang diubah.
        if ($adaKoreksiStok) {
            $stokSesudah = (float) $row['stok'];
            $stokSebelum = (float) $stokLama;

            stok_catat_riwayat(
                $pdo,
                (int) $id,
                (int) $aktor['id'],
                null,
                'koreksi',
                $stokSesudah - $stokSebelum,
                $stokSebelum,
                $stokSesudah,
                $keterangan
            );
        }

        $pdo->commit();

        json_success([
            'message' => 'Data bahan diperbarui.',
            'data' => $row,
        ]);

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}


// ============================================================
// POST /api/stok/{id}/restock
// Menambahkan pasokan bahan baku
// ============================================================

function stok_restock(string $id): void
{
    $aktor = require_user(['admin', 'owner']);
    $d = body();

    $jumlah = num_input($d, 'jumlah', 0.0001, true, 'Jumlah');
    $tanggal = str_input($d, 'tanggal', 10);

    if ($tanggal !== null) {
        $t = DateTime::createFromFormat('Y-m-d', $tanggal);

        if (!$t || $t->format('Y-m-d') !== $tanggal) {
            fail('Tanggal harus berformat YYYY-MM-DD.');
        }

        if ($tanggal > date('Y-m-d')) {
            fail('Tanggal pasokan tidak boleh di masa depan.');
        }

        $tanggal .= ' ' . date('H:i:s');
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        // Ambil stok lama dan kunci baris.
        $cek = $pdo->prepare(
            'SELECT stok
             FROM public.bahan_baku
             WHERE id = :id
             FOR UPDATE'
        );

        $cek->execute(['id' => (int) $id]);
        $stokLama = $cek->fetchColumn();

        if ($stokLama === false) {
            fail('Bahan baku tidak ditemukan.', 404);
        }

        $stokSebelum = (float) $stokLama;

        $keterangan = str_input($d, 'keterangan', 255)
            ?? 'Pasokan masuk dari supplier';

        set_konteks($pdo, [
            'user_id' => $aktor['id'],
            'jenis_stok' => 'masuk',
            'keterangan_stok' => $keterangan,
            'tanggal_stok' => $tanggal,
        ]);

        $stmt = $pdo->prepare(
            'UPDATE public.bahan_baku
             SET stok = stok + :j
             WHERE id = :id
             RETURNING
                id, nama_bahan, stok, satuan,
                reorder_point, status'
        );

        $stmt->execute([
            'j' => $jumlah,
            'id' => (int) $id,
        ]);

        $row = $stmt->fetch();

        if (!$row) {
            fail('Bahan baku tidak ditemukan.', 404);
        }

        $stokSesudah = (float) $row['stok'];

        // Catat transaksi restock.
        stok_catat_riwayat(
            $pdo,
            (int) $id,
            (int) $aktor['id'],
            null,
            'masuk',
            (float) $jumlah,
            $stokSebelum,
            $stokSesudah,
            $keterangan,
            $tanggal
        );

        $pdo->commit();

        json_success([
            'message' => 'Pasokan tercatat. Stok ' .
                $row['nama_bahan'] . ' bertambah.',
            'data' => $row,
        ]);

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}


// ============================================================
// DELETE /api/stok/{id}
// Menghapus bahan baku (owner)
// ============================================================

function stok_destroy(string $id): void
{
    require_user(['owner']);

    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM public.bahan_layanan
         WHERE bahan_baku_id = :id'
    );

    $stmt->execute(['id' => (int) $id]);

    if ((int) $stmt->fetchColumn() > 0) {
        fail(
            'Bahan masih dipakai pada konfigurasi layanan. Hapus konfigurasinya terlebih dahulu.',
            409
        );
    }

    $stmt = $pdo->prepare(
        'DELETE FROM public.bahan_baku WHERE id = :id'
    );

    $stmt->execute(['id' => (int) $id]);

    if (!$stmt->rowCount()) {
        fail('Bahan baku tidak ditemukan.', 404);
    }

    json_success([
        'message' => 'Bahan baku dihapus.',
    ]);
}


// ============================================================
// GET /api/stok/riwayat?bahan_id=&limit=
// ============================================================

function stok_riwayat(): void
{
    require_user(['admin', 'owner']);

    $params = [
        'limit' => int_query('limit', 20, 1, 200),
    ];

    $where = '';

    if (!empty($_GET['bahan_id'])) {
        $where = 'WHERE r.bahan_baku_id = :bahan';
        $params['bahan'] = (int) $_GET['bahan_id'];
    }

    $stmt = db()->prepare(
        "SELECT
            r.id,
            r.bahan_baku_id,
            b.nama_bahan,
            b.satuan,
            r.jenis,
            r.jumlah,
            r.stok_sebelum,
            r.stok_sesudah,
            r.keterangan,
            r.created_at,
            p.kode_pesanan,
            u.nama AS oleh

         FROM public.riwayat_stok r

         JOIN public.bahan_baku b
            ON b.id = r.bahan_baku_id

         LEFT JOIN public.pesanan p
            ON p.id = r.pesanan_id

         LEFT JOIN public.users u
            ON u.id = r.user_id

         $where

         ORDER BY r.created_at DESC, r.id DESC
         LIMIT :limit"
    );

    $stmt->execute($params);

    json_success([
        'data' => $stmt->fetchAll(),
    ]);
}


// ============================================================
// GET laporan kondisi stok
// ============================================================

function laporan_stok(): void
{
    require_user(['admin', 'owner']);

    $rows = db()->query(
        'SELECT
            id,
            nama_bahan,
            stok,
            satuan,
            reorder_point,
            CASE
                WHEN stok <= reorder_point THEN TRUE
                ELSE FALSE
            END AS stok_kritis
         FROM public.bahan_baku
         ORDER BY nama_bahan'
    )->fetchAll();

    json_success([
        'data' => $rows,
    ]);
}
