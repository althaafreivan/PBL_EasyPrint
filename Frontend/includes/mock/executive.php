<?php
// Data contoh dashboard eksekutif. Nanti diganti data dari backend API.
// 'Bahan kritis' dan daftar peringatan dihitung otomatis dari inventory.php.
return [
  'kpi' => [
    ['label' => 'Pendapatan bulan ini', 'value' => 'Rp 1,24 jt', 'pill' => '+12%',  'warna' => 'green', 'url' => 'owner-report.php'],
    ['label' => 'Pesanan diproses',     'value' => '08',         'pill' => '+8%',   'warna' => 'blue',  'url' => 'admin-orders.php'],
    ['label' => 'Bahan kritis',         'value' => '',           'pill' => 'Perlu aksi', 'warna' => 'red', 'url' => 'owner-inventory.php'],
    ['label' => 'Layanan terpopuler',   'value' => 'Print Dokumen', 'pill' => 'Terlaris', 'warna' => 'blue', 'url' => 'owner-services.php'],
  ],
  // [hari, jumlah pesanan]
  'tren' => [['Sen', 14], ['Sel', 19], ['Rab', 10], ['Kam', 22], ['Jum', 20], ['Sab', 28], ['Min', 23]],
  'populer' => [['Print Dokumen', 54], ['Fotokopi', 28], ['Jilid', 18]],
];