
<?php

// backend/controllers/HealthController.php

class HealthController
{
    public static function index(PDO $pdo): void
    {
        try {
            $stmt = $pdo->query(
                'SELECT current_database() AS database_name'
            );

            $database = $stmt->fetch();

            jsonResponse(200, [
                'success' => true,
                'message' => 'Backend EasyPrint berjalan.',
                'database' => $database['database_name'],
            ]);
        } catch (PDOException $e) {
            error_log('Health check failed: ' . $e->getMessage());

            jsonResponse(500, [
                'success' => false,
                'message' => 'Pemeriksaan database gagal.',
            ]);
        }
    }
}
