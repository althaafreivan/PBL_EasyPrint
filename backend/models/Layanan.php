
<?php

// backend/models/Layanan.php

class Layanan
{
    public static function getAll(PDO $pdo): array
    {
        $sql = '
            SELECT id, nama_layanan, harga, satuan, deskripsi
            FROM layanan
            ORDER BY id ASC
        ';

        $stmt = $pdo->query($sql);

        return $stmt->fetchAll();
    }
}
