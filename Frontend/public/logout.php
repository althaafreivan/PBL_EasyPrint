<?php
// frontend/public/logout.php - hapus token di backend lalu akhiri sesi.
require_once __DIR__ . '/../includes/bootstrap.php';

if (!empty($_SESSION['token'])) {
    callBackendApi('/api/logout', 'POST', []);
}
logout_lokal();
header('Location: login.php?keluar=1');
exit;
