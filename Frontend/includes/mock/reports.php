<?php
// Data contoh laporan. Nanti diganti data dari backend API.
return [
  'periode' => ['dari' => '2026-10-01', 'sampai' => '2026-10-08'],
  'kpi' => [
    'penjualan' => 'Rp 1,24 jt',
    'pesanan'   => '84',
    'bahan'     => 'Rp 254 rb',
    'teratas'   => 'Print Dokumen',
  ],
  'transaksi' => [
    ['tanggal' => '2026-10-08', 'invoice' => 'INV-014', 'pelanggan' => 'Andra', 'layanan' => 'Print Dokumen', 'total' => 25000],
    ['tanggal' => '2026-10-08', 'invoice' => 'INV-013', 'pelanggan' => 'Putri', 'layanan' => 'Fotokopi',      'total' => 9000],
    ['tanggal' => '2026-10-07', 'invoice' => 'INV-008', 'pelanggan' => 'Yoga',  'layanan' => 'Jilid',         'total' => 13000],
  ],
  'sirkulasi' => [
    ['Kertas A4', '2,5', 'rim'], ['Kertas F4', '0,4', 'rim'],
    ['Tinta Hitam', '1', 'botol'], ['Ring Spiral', '4', 'pcs'],
  ],
];