<?php

function kurangiStokPesanan(PDO $pdo, int $id_pesanan): array
{
    if ($id_pesanan < 1) {
        return [
            'cukup' => false,
            'message' => 'ID pesanan tidak valid.'
        ];
    }

    if ($pdo->inTransaction()) {
        return [
            'cukup' => false,
            'message' => 'Transaksi database sedang berjalan.'
        ];
    }

    try {
        $pdo->beginTransaction();

        $stmtPesanan = $pdo->prepare("
            SELECT id, stok_dipotong
            FROM pesanan
            WHERE id = :pesanan_id
            FOR UPDATE
        ");

        $stmtPesanan->execute([
            ':pesanan_id' => $id_pesanan
        ]);

        $pesanan = $stmtPesanan->fetch(PDO::FETCH_ASSOC);

        if (!$pesanan) {
            $pdo->rollBack();

            return [
                'cukup' => false,
                'message' => 'Pesanan tidak ditemukan.'
            ];
        }

        $stokSudahDipotong = in_array(
            strtolower((string) $pesanan['stok_dipotong']),
            ['1', 't', 'true', 'yes', 'on'],
            true
        );

        if ($stokSudahDipotong) {
            $pdo->rollBack();

            return [
                'cukup' => false,
                'message' => 'Stok pesanan ini sudah pernah dipotong.'
            ];
        }

        $stmtDetail = $pdo->prepare("
            SELECT COUNT(*)
            FROM detail_pesanan
            WHERE pesanan_id = :pesanan_id
        ");

        $stmtDetail->execute([
            ':pesanan_id' => $id_pesanan
        ]);

        if ((int) $stmtDetail->fetchColumn() === 0) {
            $pdo->rollBack();

            return [
                'cukup' => false,
                'message' => 'Detail pesanan tidak ditemukan.'
            ];
        }

        $stmtBOM = $pdo->prepare("
            SELECT DISTINCT dp.layanan_id
            FROM detail_pesanan dp
            WHERE dp.pesanan_id = :pesanan_id
            AND NOT EXISTS (
                SELECT 1
                FROM bahan_layanan bl
                WHERE bl.layanan_id = dp.layanan_id
            )
        ");

        $stmtBOM->execute([
            ':pesanan_id' => $id_pesanan
        ]);

        $layananTanpaBOM = $stmtBOM->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($layananTanpaBOM)) {
            $pdo->rollBack();

            return [
                'cukup' => false,
                'message' => 'Layanan belum memiliki komposisi bahan baku.',
                'layanan_tanpa_bom' => $layananTanpaBOM
            ];
        }

        $sqlKebutuhan = "
            SELECT
                bl.bahan_baku_id,
                bb.nama_bahan,
                SUM(
                    dp.jumlah::NUMERIC * bl.jumlah_kebutuhan
                ) AS total_butuh
            FROM detail_pesanan dp
            JOIN bahan_layanan bl
                ON dp.layanan_id = bl.layanan_id
            JOIN bahan_baku bb
                ON bl.bahan_baku_id = bb.id
            WHERE dp.pesanan_id = :pesanan_id
            GROUP BY
                bl.bahan_baku_id,
                bb.nama_bahan
            ORDER BY bl.bahan_baku_id ASC
        ";

        $stmt = $pdo->prepare($sqlKebutuhan);

        $stmt->execute([
            ':pesanan_id' => $id_pesanan
        ]);

        $kebutuhanList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($kebutuhanList)) {
            $pdo->rollBack();

            return [
                'cukup' => false,
                'message' => 'Kebutuhan bahan baku tidak ditemukan.'
            ];
        }

        $bahanKurang = [];
        $bahanTerkunci = [];

        foreach ($kebutuhanList as $item) {
            $stmtStok = $pdo->prepare("
                SELECT
                    id,
                    nama_bahan,
                    stok,
                    reorder_point,
                    CASE
                        WHEN stok >= CAST(:total_butuh AS NUMERIC)
                            THEN 1
                        ELSE 0
                    END AS cukup
                FROM bahan_baku
                WHERE id = :bahan_baku_id
                FOR UPDATE
            ");

            $stmtStok->execute([
                ':total_butuh' => $item['total_butuh'],
                ':bahan_baku_id' => $item['bahan_baku_id']
            ]);

            $stok = $stmtStok->fetch(PDO::FETCH_ASSOC);

            if (!$stok) {
                throw new RuntimeException(
                    'Data bahan baku tidak ditemukan.'
                );
            }

            if ((int) $stok['cukup'] !== 1) {
                $bahanKurang[] = [
                    'nama_bahan' => $stok['nama_bahan'],
                    'stok_tersedia' => $stok['stok'],
                    'total_dibutuhkan' => $item['total_butuh']
                ];
            } else {
                $bahanTerkunci[] = [
                    'id' => $stok['id'],
                    'nama_bahan' => $stok['nama_bahan'],
                    'total_butuh' => $item['total_butuh']
                ];
            }
        }

        if (!empty($bahanKurang)) {
            $pdo->rollBack();

            return [
                'cukup' => false,
                'message' => 'Stok bahan baku tidak mencukupi.',
                'bahan_kurang' => $bahanKurang
            ];
        }

        $sqlUpdate = "
            UPDATE bahan_baku
            SET
                stok = stok - CAST(:jumlah_update AS NUMERIC),
                status = CASE
                    WHEN stok - CAST(:jumlah_status AS NUMERIC) <= reorder_point
                        THEN 'Kritis'
                    ELSE 'Aman'
                END
            WHERE id = :bahan_baku_id
            AND stok >= CAST(:jumlah_cek AS NUMERIC)
            RETURNING
                id,
                nama_bahan,
                stok,
                reorder_point,
                status
        ";

        $updateStmt = $pdo->prepare($sqlUpdate);

        $hasilPengurangan = [];

        foreach ($bahanTerkunci as $item) {
            $updateStmt->execute([
                ':jumlah_update' => $item['total_butuh'],
                ':jumlah_status' => $item['total_butuh'],
                ':bahan_baku_id' => $item['id'],
                ':jumlah_cek' => $item['total_butuh']
            ]);

            $hasil = $updateStmt->fetch(PDO::FETCH_ASSOC);

            if (!$hasil) {
                throw new RuntimeException(
                    'Pengurangan stok bahan baku gagal.'
                );
            }

            $hasilPengurangan[] = $hasil;
        }

        $stmtTandai = $pdo->prepare("
            UPDATE pesanan
            SET stok_dipotong = TRUE
            WHERE id = :pesanan_id
            AND stok_dipotong = FALSE
        ");

        $stmtTandai->execute([
            ':pesanan_id' => $id_pesanan
        ]);

        if ($stmtTandai->rowCount() !== 1) {
            throw new RuntimeException(
                'Status pemotongan stok gagal disimpan.'
            );
        }

        $pdo->commit();

        return [
            'cukup' => true,
            'message' => 'Stok bahan baku berhasil dikurangi.',
            'data' => $hasilPengurangan
        ];

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('kurangiStokPesanan gagal: ' . $e->getMessage());

        return [
            'cukup' => false,
            'message' => 'Terjadi kesalahan saat memproses pengurangan stok.'
        ];
    }
}