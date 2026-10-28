<?php
// Backend/controllers/InventoryController.php

/**
 * Mengambil daftar bahan baku beserta status stoknya.
 */
function getMaterials(PDO $pdo): void
{
    header('Content-Type: application/json; charset=utf-8');

    try {
        $stmt = $pdo->query("
            SELECT
                id_bahan,
                nama_bahan,
                stok,
                satuan,
                reorder_point,
                harga_bahan_baku,
                status,
                CASE
                    WHEN stok <= reorder_point THEN 'Kritis'
                    ELSE 'Aman'
                END AS status_stok
            FROM bahan_baku
            ORDER BY nama_bahan ASC
        ");

        echo json_encode([
            'status' => 'success',
            'data' => $stmt->fetchAll()
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        http_response_code(500);

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data bahan baku.'
        ], JSON_UNESCAPED_UNICODE);

        error_log('getMaterials gagal: ' . $e->getMessage());
    }
}


/**
 * Menyesuaikan stok bahan baku secara manual.
 * Input yang dibutuhkan: id_bahan dan stok.
 */
function adjustStock(PDO $pdo, array $input): void
{
    header('Content-Type: application/json; charset=utf-8');

    // Validasi ID bahan baku.
    $idBahan = filter_var(
        $input['id_bahan'] ?? null,
        FILTER_VALIDATE_INT
    );

    // Validasi jumlah stok baru.
    $stokBaru = filter_var(
        $input['stok'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        $idBahan === false ||
        $idBahan === null ||
        $idBahan < 1 ||
        $stokBaru === false ||
        $stokBaru === null ||
        $stokBaru < 0
    ) {
        http_response_code(400);

        echo json_encode([
            'status' => 'error',
            'message' => 'ID bahan harus positif dan stok tidak boleh negatif.'
        ], JSON_UNESCAPED_UNICODE);

        return;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE bahan_baku
            SET
                stok = :stok_baru,
                status = CASE
                    WHEN CAST(:stok_cek AS NUMERIC) <= reorder_point
                        THEN 'Kritis'
                    ELSE 'Aman'
                END
            WHERE id_bahan = :id_bahan
            RETURNING
                id_bahan,
                nama_bahan,
                stok,
                satuan,
                reorder_point,
                harga_bahan_baku,
                status
        ");

        $stmt->execute([
            ':stok_baru' => $stokBaru,
            ':stok_cek' => $stokBaru,
            ':id_bahan' => $idBahan
        ]);

        $data = $stmt->fetch();

        if (!$data) {
            http_response_code(404);

            echo json_encode([
                'status' => 'error',
                'message' => 'Bahan baku tidak ditemukan.'
            ], JSON_UNESCAPED_UNICODE);

            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Stok bahan baku berhasil diperbarui.',
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        http_response_code(500);

        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memperbarui stok bahan baku.'
        ], JSON_UNESCAPED_UNICODE);

        error_log('adjustStock gagal: ' . $e->getMessage());
    }
}