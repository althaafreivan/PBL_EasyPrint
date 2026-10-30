
<?php
// Backend/models/StokModel.php

function kurangiStokPesanan(PDO $pdo, int $idPesanan): array
{
    // Hitung kebutuhan bahan berdasarkan detail pesanan.
    $stmt = $pdo->prepare("
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
    ");

    $stmt->execute(['id_pesanan' => $idPesanan]);
    $kebutuhan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$kebutuhan) {
        throw new RuntimeException(
            'Kebutuhan bahan untuk pesanan ini belum tersedia.'
        );
    }

    $bahanKurang = [];
    $stokTerkunci = [];

    // Kunci baris stok dan periksa semua bahan terlebih dahulu.
    foreach ($kebutuhan as $item) {
        $stmtStok = $pdo->prepare("
            SELECT id, nama_bahan, stok
            FROM public.bahan_baku
            WHERE id = :id
            FOR UPDATE
        ");

        $stmtStok->execute([
            'id' => $item['bahan_baku_id']
        ]);

        $bahan = $stmtStok->fetch(PDO::FETCH_ASSOC);

        if (!$bahan) {
            throw new RuntimeException(
                'Data bahan baku tidak ditemukan.'
            );
        }

        $dibutuhkan = (float) $item['dibutuhkan'];
        $stok = (float) $bahan['stok'];

        if ($stok < $dibutuhkan) {
            $bahanKurang[] = $bahan['nama_bahan'];
        }

        $stokTerkunci[] = [
            'id' => (int) $bahan['id'],
            'stok' => $stok,
            'dibutuhkan' => $dibutuhkan
        ];
    }

    // Jangan mengurangi stok jika ada bahan yang kurang.
    if ($bahanKurang) {
        return [
            'cukup' => false,
            'bahan_kurang' => implode(', ', $bahanKurang)
        ];
    }

    // Kurangi stok setelah seluruh kebutuhan dinyatakan cukup.
    $update = $pdo->prepare("
        UPDATE public.bahan_baku
        SET stok = stok - :jumlah
        WHERE id = :id
    ");

    foreach ($stokTerkunci as $item) {
        $update->execute([
            'jumlah' => $item['dibutuhkan'],
            'id' => $item['id']
        ]);
    }

    return [
        'cukup' => true,
        'bahan_kurang' => null
    ];
}
