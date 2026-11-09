<?php
if (!function_exists('rupiah')) {
  function rupiah(int $n): string { return 'Rp ' . number_format($n, 0, ',', '.'); }
}
if (!function_exists('e')) {
  function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
}
// Kelas pill per status pesanan (pelanggan).
function kelas_status(string $status): string {
  return [
    'Pending' => 'pill--yellow', 'Tertunda' => 'pill--tertunda', 'Diproses' => 'pill--blue',
    'Selesai' => 'pill--green', 'Diambil' => 'pill--green', 'Ditolak' => 'pill--red',
  ][$status] ?? 'pill--blue';
}
// Tautan pelacakan sesuai status.
function link_lacak(string $status): string {
  return ['Tertunda' => 'tracking-tertunda.php', 'Ditolak' => 'tracking-ditolak.php'][$status] ?? 'tracking.php';
}
