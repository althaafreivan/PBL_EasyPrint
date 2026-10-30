<?php
// Data contoh. Nanti diganti data dari backend API.
return [
  'user' => ['nama' => 'Andra'],
  'active_order' => [
    'kode'     => '#RI-20261008-014',
    'status'   => 'Diproses',
    'estimasi' => 'Hari ini, 16:30',
    'total'    => 18500,
  ],
  'services' => [
    ['key' => 'print',    'nama' => 'Print Dokumen', 'desc' => 'PDF, Word, dll.',     'harga' => 'Rp 500 / lembar'],
    ['key' => 'fotokopi', 'nama' => 'Fotokopi',      'desc' => 'Hitam-putih / warna', 'harga' => 'Mulai Rp 300'],
    ['key' => 'jilid',    'nama' => 'Jilid',         'desc' => 'Finishing dokumen',   'harga' => 'Mulai Rp 5.000'],
  ],
];