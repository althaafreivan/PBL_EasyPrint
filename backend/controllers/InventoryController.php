<?php
function getMaterials(PDO $pdo): void
{
    header('Content-Type: application/json; charset=utf-8');

    try {
        $stmt = $pdo->query("
            SELECT
                id,
                nama_bahan,
                stok,
                satuan,
                reorder_point,
                harga_satuan,
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
 *
 * Input:
 * - bahan_baku_id: ID bahan baku yang akan diperbarui
 * - stok: jumlah stok baru, boleh berupa angka desimal
 */
function adjustStock(PDO $pdo, array $input): void
{
    header('Content-Type: application/json; charset=utf-8');

    // Validasi ID bahan baku.
    $idBahan = filter_var(
        $input['bahan_baku_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    // Validasi stok: NUMERIC(10,2), sehingga desimal diperbolehkan.
    $stokInput = $input['stok'] ?? null;

    if (
        $idBahan === false ||
        $idBahan === null ||
        $idBahan < 1 ||
        !is_numeric($stokInput) ||
        !is_finite((float) $stokInput) ||
        (float) $stokInput < 0 ||
        (float) $stokInput > 99999999.99
    ) {
        http_response_code(400);

        echo json_encode([
            'status' => 'error',
            'message' => 'ID bahan harus valid dan stok harus berupa angka nonnegatif dengan maksimal dua angka desimal.'
        ], JSON_UNESCAPED_UNICODE);

        return;
    }

    // Simpan sebagai string numerik agar nilai desimal diteruskan ke PostgreSQL.
    $stokBaru = trim((string) $stokInput);

    try {
        $stmt = $pdo->prepare("
            UPDATE bahan_baku
            SET
                stok = CAST(:stok_baru AS NUMERIC),
                status = CASE
                    WHEN CAST(:stok_cek AS NUMERIC) <= reorder_point
                        THEN 'Kritis'
                    ELSE 'Aman'
                END
            WHERE id = :bahan_baku_id
            RETURNING
                id,
                nama_bahan,
                stok,
                satuan,
                reorder_point,
                harga_satuan,
                status
        ");

        $stmt->execute([
            ':stok_baru' => $stokBaru,
            ':stok_cek' => $stokBaru,
            ':bahan_baku_id' => $idBahan
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