<?php
// Data contoh. Nanti diganti data dari backend API.
return [
  'order' => [
    'kode'          => '#RI-20261008-014',
    'status'        => 'Diproses',
    'total'         => 18500,
    'langkah'       => ['Pending', 'Diverifikasi', 'Diproses', 'Selesai', 'Diambil'],
    'langkah_aktif' => 2, // indeks langkah saat ini (0 = Pending)
    'rincian'       => [
      ['Layanan', 'Print Dokumen'],
      ['Jenis kertas', 'A4'],
      ['Mode', 'Warna'],
      ['Jumlah', '20 lembar'],
      ['Jilid', 'Tanpa jilid'],
      ['Catatan', 'Mohon dipotong rapi'],
    ],
  ],
  'lainnya' => [
    ['kode' => '#RI-20261002-001', 'status' => 'Selesai', 'total' => 12000],
    ['kode' => '#RI-20261003-002', 'status' => 'Selesai', 'total' => 12000],
  ],
];