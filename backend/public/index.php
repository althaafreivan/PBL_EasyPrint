<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

echo json_encode([
    'status' => 'success',
    'message' => 'EasyPrint Backend REST API aktif',
    'database' => 'easyprint_db'
], JSON_PRETTY_PRINT);