<?php
// Data contoh kasir. Nanti diganti data dari backend API.
// items: [nama, qty, harga satuan, satuan qty]. selesai = waktu hasil cetak selesai. bayar = uang tunai dari pelanggan.
return [
  [
    'kode' => '#RI-20261007-009', 'status' => 'selesai', 'selesai' => '07 Okt, 16.40',
    'pelanggan' => 'Andra Maheswara', 'telepon' => '0812-3456-7890',
    'metode' => 'Tunai di tempat', 'bayar' => 30000, 'dibayar' => '08/10/2026 10:42', 'nomor' => 'INV-RI-2026-0014',
    'items' => [['Print Dokumen A4 - Warna', 20, 1000, 'lbr'], ['Jilid Spiral', 1, 5000, '']],
  ],
  [
    'kode' => '#RI-20261008-013', 'status' => 'diproses', 'selesai' => '',
    'pelanggan' => 'Nadia Putri', 'telepon' => '0813-5566-1213',
    'metode' => 'Tunai di tempat', 'bayar' => 10000, 'dibayar' => '08/10/2026 11:05', 'nomor' => 'INV-RI-2026-0015',
    'items' => [['Fotokopi A4 - Hitam putih', 30, 300, 'lbr']],
  ],
  [
    'kode' => '#RI-20261008-008', 'status' => 'selesai', 'selesai' => '07 Okt, 15.10',
    'pelanggan' => 'Rama Aditya', 'telepon' => '0857-1200-0008',
    'metode' => 'Tunai di tempat', 'bayar' => 15000, 'dibayar' => '08/10/2026 09:30', 'nomor' => 'INV-RI-2026-0013',
    'items' => [['Jilid Spiral + Soft Cover', 1, 13000, '']],
  ],
];