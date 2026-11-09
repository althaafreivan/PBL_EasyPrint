<?php
// Data contoh dashboard operasional admin. Nanti diganti data dari backend API.
return [
  'ringkasan' => [
    ['label' => 'Pesanan Pending', 'nilai' => '06', 'tipe' => 'amber'],
    ['label' => 'Tertunda',        'nilai' => '01', 'tipe' => 'blue'],
    ['label' => 'Sedang Diproses', 'nilai' => '08', 'tipe' => 'blue'],
    ['label' => 'Siap Diambil',    'nilai' => '04', 'tipe' => 'green'],
  ],
  'prioritas' => [
    ['kode' => '#RI-015', 'pelanggan' => 'Sinta', 'masuk' => '10:31', 'status' => 'Pending'],
    ['kode' => '#RI-013', 'pelanggan' => 'Nadia', 'masuk' => '09:05', 'status' => 'Diproses'],
    ['kode' => '#RI-012', 'pelanggan' => 'Fikri', 'masuk' => '08:48', 'status' => 'Pending'],
    ['kode' => '#RI-011', 'pelanggan' => 'Rani',  'masuk' => '08:31', 'status' => 'Diproses'],
    ['kode' => '#RI-010', 'pelanggan' => 'Bima',  'masuk' => '08:15', 'status' => 'Pending'],
  ],
  'bahan' => [
    ['nama' => 'Kertas A4',  'info' => '8 rim · Kritis',  'aman' => false],
    ['nama' => 'Kertas F4',  'info' => '0 rim · Kritis',  'aman' => false],
    ['nama' => 'Tinta Hitam', 'info' => '22 botol · Aman', 'aman' => true],
  ],
  'pengingat' => [
    'Periksa file dan ketersediaan bahan sebelum mengubah pesanan menjadi Diproses.',
    'Pemotongan stok otomatis terjadi ketika status berubah menjadi Diproses.',
  ],
];
