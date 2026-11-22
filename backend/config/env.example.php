<?php
// Salin file ini menjadi config/env.php (tidak ikut di-commit) lalu isi sesuai laptop masing-masing.
putenv('DB_HOST=127.0.0.1');
putenv('DB_PORT=5432');
putenv('DB_NAME=easyprint_db');
putenv('DB_USER=postgres');
putenv('DB_PASS=');
// Origin frontend yang diizinkan CORS (pisahkan dengan koma).
putenv('CORS_ORIGINS=http://127.0.0.1:3000,http://localhost:3000');
