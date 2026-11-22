<?php
// backend/controllers/LayananController.php
// CP-2: Katalog layanan (publik) & master layanan + Bill of Materials (owner)

const KATEGORI_LAYANAN = ['cetak', 'fotokopi', 'finishing'];
const SATUAN_LAYANAN = ['lembar', 'buku', 'eksemplar', 'paket', 'pcs'];

function layanan_index(): void
{
    $pdo = db();
    // ?semua=1 (owner) menampilkan juga layanan nonaktif.
    $semua = ($_GET['semua'] ?? '') === '1' && bearer_token() !== null
        && (getAuthenticatedUser($pdo)['role'] ?? '') === 'owner';

    $sql = 'SELECT l.id, l.nama_layanan, l.harga, l.satuan, l.deskripsi, l.kategori, l.aktif,
                   (SELECT COUNT(*) FROM public.detail_pesanan dp WHERE dp.layanan_id = l.id) AS dipakai
            FROM public.layanan l';
    if (!$semua) {
        $sql .= ' WHERE l.aktif';
    }
    $sql .= " ORDER BY CASE l.kategori WHEN 'cetak' THEN 1 WHEN 'fotokopi' THEN 2 ELSE 3 END, l.harga, l.id";
    $layanan = $pdo->query($sql)->fetchAll();

    $bom = $pdo->query(
        'SELECT bl.id, bl.layanan_id, bl.bahan_baku_id, bb.nama_bahan, bb.satuan, bl.jumlah_kebutuhan
         FROM public.bahan_layanan bl
         JOIN public.bahan_baku bb ON bb.id = bl.bahan_baku_id
         ORDER BY bl.layanan_id, bb.nama_bahan'
    )->fetchAll();

    $perLayanan = [];
    foreach ($bom as $b) {
        $perLayanan[$b['layanan_id']][] = $b;
    }
    foreach ($layanan as &$l) {
        $l['bahan'] = $perLayanan[$l['id']] ?? [];
        $l['dipakai'] = (int) $l['dipakai'];
    }

    json_success(['data' => $layanan]);
}

function layanan_validasi(array $data): array
{
    $nama = str_input($data, 'nama_layanan', 100, true, 'Nama layanan');
    $harga = num_input($data, 'harga', 0, true, 'Harga');
    $satuan = str_input($data, 'satuan', 20) ?? 'lembar';
    $kategori = str_input($data, 'kategori', 20) ?? 'cetak';
    if (!in_array($satuan, SATUAN_LAYANAN, true)) {
        fail('Satuan harus salah satu dari: ' . implode(', ', SATUAN_LAYANAN) . '.');
    }
    if (!in_array($kategori, KATEGORI_LAYANAN, true)) {
        fail('Kategori harus cetak, fotokopi, atau finishing.');
    }
    return [
        'nama' => $nama,
        'harga' => $harga,
        'satuan' => $satuan,
        'kategori' => $kategori,
        'deskripsi' => str_input($data, 'deskripsi', 500),
        'aktif' => array_key_exists('aktif', $data) ? (filter_var($data['aktif'], FILTER_VALIDATE_BOOL) ? 'true' : 'false') : 'true',
    ];
}

function layanan_store(): void
{
    require_user(['owner']);
    $v = layanan_validasi(body());
    $stmt = db()->prepare(
        'INSERT INTO public.layanan (nama_layanan, harga, satuan, kategori, deskripsi, aktif)
         VALUES (:nama, :harga, :satuan, :kategori, :deskripsi, :aktif)
         RETURNING id, nama_layanan, harga, satuan, kategori, deskripsi, aktif'
    );
    $stmt->execute($v);
    json_success(['message' => 'Layanan ditambahkan.', 'data' => $stmt->fetch()], 201);
}

function layanan_update(string $id): void
{
    require_user(['owner']);
    $v = layanan_validasi(body()) + ['id' => (int) $id];
    $stmt = db()->prepare(
        'UPDATE public.layanan
         SET nama_layanan = :nama, harga = :harga, satuan = :satuan,
             kategori = :kategori, deskripsi = :deskripsi, aktif = :aktif
         WHERE id = :id
         RETURNING id, nama_layanan, harga, satuan, kategori, deskripsi, aktif'
    );
    $stmt->execute($v);
    $row = $stmt->fetch() ?: fail('Layanan tidak ditemukan.', 404);
    json_success(['message' => 'Layanan diperbarui.', 'data' => $row]);
}

function layanan_destroy(string $id): void
{
    require_user(['owner']);
    $pdo = db();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM public.detail_pesanan WHERE layanan_id = :id');
    $stmt->execute(['id' => (int) $id]);

    if ((int) $stmt->fetchColumn() > 0) {
        // FK detail_pesanan -> layanan bersifat RESTRICT: layanan yang punya histori
        // pesanan dinonaktifkan agar laporan lama tetap utuh.
        $stmt = $pdo->prepare('UPDATE public.layanan SET aktif = false WHERE id = :id');
        $stmt->execute(['id' => (int) $id]);
        json_success(['message' => 'Layanan sudah dipakai pada pesanan, sehingga dinonaktifkan (tidak tampil di katalog).', 'nonaktif' => true]);
    }

    $stmt = $pdo->prepare('DELETE FROM public.layanan WHERE id = :id');
    $stmt->execute(['id' => (int) $id]);
    if (!$stmt->rowCount()) {
        fail('Layanan tidak ditemukan.', 404);
    }
    json_success(['message' => 'Layanan dihapus.']);
}

// ---------------- Bill of Materials (bahan_layanan) ----------------

function bom_index(): void
{
    require_user(['admin', 'owner']);
    $rows = db()->query(
        'SELECT bl.id, bl.layanan_id, l.nama_layanan, l.satuan AS satuan_layanan,
                bl.bahan_baku_id, bb.nama_bahan, bb.satuan AS satuan_bahan, bl.jumlah_kebutuhan
         FROM public.bahan_layanan bl
         JOIN public.layanan l ON l.id = bl.layanan_id
         JOIN public.bahan_baku bb ON bb.id = bl.bahan_baku_id
         ORDER BY l.nama_layanan, bb.nama_bahan'
    )->fetchAll();
    json_success(['data' => $rows]);
}

function bom_store(): void
{
    require_user(['owner']);
    $d = body();
    $layanan = (int) num_input($d, 'layanan_id', 1, true, 'Layanan');
    $bahan = (int) num_input($d, 'bahan_baku_id', 1, true, 'Bahan baku');
    $jumlah = num_input($d, 'jumlah_kebutuhan', 0.0001, true, 'Jumlah kebutuhan');

    try {
        $stmt = db()->prepare(
            'INSERT INTO public.bahan_layanan (layanan_id, bahan_baku_id, jumlah_kebutuhan)
             VALUES (:l, :b, :j) RETURNING id, layanan_id, bahan_baku_id, jumlah_kebutuhan'
        );
        $stmt->execute(['l' => $layanan, 'b' => $bahan, 'j' => $jumlah]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            fail('Bahan tersebut sudah dikonfigurasi untuk layanan ini. Ubah jumlahnya saja.', 409);
        }
        if ($e->getCode() === '23503') {
            fail('Layanan atau bahan baku tidak ditemukan.', 404);
        }
        throw $e;
    }
    json_success(['message' => 'Konfigurasi bahan ditambahkan.', 'data' => $stmt->fetch()], 201);
}

function bom_update(string $id): void
{
    require_user(['owner']);
    $jumlah = num_input(body(), 'jumlah_kebutuhan', 0.0001, true, 'Jumlah kebutuhan');
    $stmt = db()->prepare(
        'UPDATE public.bahan_layanan SET jumlah_kebutuhan = :j WHERE id = :id
         RETURNING id, layanan_id, bahan_baku_id, jumlah_kebutuhan'
    );
    $stmt->execute(['j' => $jumlah, 'id' => (int) $id]);
    $row = $stmt->fetch() ?: fail('Konfigurasi tidak ditemukan.', 404);
    json_success(['message' => 'Konfigurasi bahan diperbarui.', 'data' => $row]);
}

function bom_destroy(string $id): void
{
    require_user(['owner']);
    $stmt = db()->prepare('DELETE FROM public.bahan_layanan WHERE id = :id');
    $stmt->execute(['id' => (int) $id]);
    if (!$stmt->rowCount()) {
        fail('Konfigurasi tidak ditemukan.', 404);
    }
    json_success(['message' => 'Konfigurasi bahan dihapus.']);
}
