<?php
// Data contoh. Nanti diganti data dari backend API.
return [
  'user' => ['nama' => 'Andra Maheswara', 'email' => 'andra@gmail.com', 'panggilan' => 'Andra', 'inisial' => 'A'],
  'stats' => [
    ['label' => 'Pesanan aktif',        'nilai' => '03', 'tipe' => 'blue'],
    ['label' => 'Menunggu verifikasi',  'nilai' => '00', 'tipe' => 'amber'],
    ['label' => 'Pesanan diambil',      'nilai' => '12', 'tipe' => 'green'],
  ],
  'services' => [
    ['key' => 'print',    'nama' => 'Print Dokumen', 'desc' => 'PDF, Word, dan dokumen lainnya', 'harga' => 'Mulai Rp 500 / lembar'],
    ['key' => 'fotokopi', 'nama' => 'Fotokopi',      'desc' => 'Hitam-putih maupun warna',      'harga' => 'Mulai Rp 300 / lembar'],
    ['key' => 'jilid',    'nama' => 'Jilid',         'desc' => 'Finishing dokumen',             'harga' => 'Mulai Rp 5.000'],
  ],
  'orders' => [
    ['kode' => '#RI-20261008-016', 'layanan' => 'Print Dokumen F4', 'tanggal' => '08 Okt 2026', 'status' => 'Tertunda'],
    ['kode' => '#RI-20261008-014', 'layanan' => 'Print Dokumen',    'tanggal' => '08 Okt 2026', 'status' => 'Diproses'],
    ['kode' => '#RI-20261007-009', 'layanan' => 'Print Dokumen',    'tanggal' => '07 Okt 2026', 'status' => 'Selesai'],
  ],
];