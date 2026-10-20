
<?php

// backend/controllers/LayananController.php

class LayananController
{
    public static function index(PDO $pdo): void
    {
        try {
            $layanan = Layanan::getAll($pdo);

            jsonResponse(200, [
                'success' => true,
                'message' => 'Daftar layanan berhasil diambil.',
                'data' => $layanan,
            ]);
        } catch (PDOException $e) {
            error_log('Get layanan failed: ' . $e->getMessage());

            jsonResponse(500, [
                'success' => false,
                'message' => 'Gagal mengambil daftar layanan.',
            ]);
        }
    }
}
