
<?php
// backend/models/StokModel.php
// CP-3: Pemotongan stok otomatis berbasis Bill of Materials.
// Mencatat setiap pemakaian bahan ke tabel riwayat_stok.
// Harus dipanggil di dalam transaksi yang sedang berjalan.

function kurangiStokPesanan(PDO $pdo, int $idPesanan, ?int $aktorId = null): array
{
    // 1. Hitung kebutuhan bahan berdasarkan detail pesanan x bahan_layanan.
    $stmt = $pdo->prepare('
        SELECT
            bl.bahan_baku_id,
            bb.nama_bahan,
            SUM(bl.jumlah_kebutuhan * dp.jumlah) AS dibutuhkan
        FROM public.detail_pesanan dp
        JOIN public.bahan_layanan bl
            ON bl.layanan_id = dp.layanan_id
        JOIN public.bahan_baku bb
            ON bb.id = bl.bahan_baku_id
        WHERE dp.pesanan_id = :id_pesanan
        GROUP BY bl.bahan_baku_id, bb.nama_bahan
        ORDER BY bl.bahan_baku_id
    ');

    $stmt->execute(['id_pesanan' => $idPesanan]);
    $kebutuhan = $stmt->fetchAll();

    // Layanan tanpa konfigurasi bahan tidak memotong stok.
    if (!$kebutuhan) {
        return [
            'cukup' => true,
            'bahan_kurang' => null,
            'dipotong' => [],
        ];
    }

    $bahanKurang = [];
    $stokTerkunci = [];

    // 2. Kunci baris stok dan periksa semua bahan terlebih dahulu.
    $stmtStok = $pdo->prepare('
        SELECT id, nama_bahan, stok
        FROM public.bahan_baku
        WHERE id = :id
        FOR UPDATE
    ');

    foreach ($kebutuhan as $item) {
        $stmtStok->execute([
            'id' => $item['bahan_baku_id'],
        ]);

        $bahan = $stmtStok->fetch();

        if (!$bahan) {
            throw new RuntimeException(
                'Data bahan baku tidak ditemukan.'
            );
        }

        // Bandingkan dalam satuan terkecil (4 desimal).
        $dibutuhkan = (int) round(
            (float) $item['dibutuhkan'] * 10000
        );

        $stok = (int) round(
            (float) $bahan['stok'] * 10000
        );

        if ($stok < $dibutuhkan) {
            $bahanKurang[] = $bahan['nama_bahan'];
        }

        $stokTerkunci[] = [
            'id' => (int) $bahan['id'],
            'nama' => $bahan['nama_bahan'],
            'dibutuhkan' => (float) $item['dibutuhkan'],
            'stok_sebelum' => (float) $bahan['stok'],
        ];
    }

    // 3. Jangan mengurangi stok jika ada bahan yang kurang.
    if ($bahanKurang) {
        return [
            'cukup' => false,
            'bahan_kurang' => implode(', ', $bahanKurang),
            'dipotong' => [],
        ];
    }

    // 4. Ambil kode pesanan untuk keterangan riwayat.
    $kode = $pdo->prepare('
        SELECT kode_pesanan
        FROM public.pesanan
        WHERE id = :id
    ');

    $kode->execute(['id' => $idPesanan]);
    $kodePesanan = $kode->fetchColumn();

    $keterangan = 'Pemakaian pesanan ' . $kodePesanan;

    // 5. Siapkan query pengurangan stok.
    $update = $pdo->prepare('
        UPDATE public.bahan_baku
        SET stok = stok - :jumlah
        WHERE id = :id
        RETURNING stok
    ');

    // 6. Siapkan query pencatatan riwayat stok.
    $riwayat = $pdo->prepare('
        INSERT INTO public.riwayat_stok (
            bahan_baku_id,
            user_id,
            pesanan_id,
            jenis,
            jumlah,
            stok_sebelum,
            stok_sesudah,
            keterangan
        )
        VALUES (
            :bahan_id,
            :user_id,
            :pesanan_id,
            :jenis,
            :jumlah,
            :sebelum,
            :sesudah,
            :keterangan
        )
    ');

    // 7. Kurangi stok dan catat riwayat untuk setiap bahan.
    foreach ($stokTerkunci as $item) {
        $update->execute([
            'jumlah' => $item['dibutuhkan'],
            'id' => $item['id'],
        ]);

        $stokSesudah = $update->fetchColumn();

        if ($stokSesudah === false) {
            throw new RuntimeException(
                'Gagal memperbarui stok bahan ' . $item['nama']
            );
        }

        $stokSebelum = $item['stok_sebelum'];
        $jumlahKeluar = (float) $item['dibutuhkan'];

        $riwayat->execute([
            'bahan_id' => $item['id'],
            'user_id' => $aktorId,
            'pesanan_id' => $idPesanan,
            'jenis' => 'keluar',
            'jumlah' => -$jumlahKeluar,
            'sebelum' => $stokSebelum,
            'sesudah' => (float) $stokSesudah,
            'keterangan' => $keterangan,
        ]);
    }

    // 8. Kembalikan konteks transaksi.
    // set_konteks($pdo, [
    //     'jenis_stok' => null,
    //     'pesanan_id' => null,
    //     'keterangan_stok' => null,
    // ]);

    return [
        'cukup' => true,
        'bahan_kurang' => null,
        'dipotong' => $stokTerkunci,
    ];
}
