<?php
// Data contoh antrean admin. Nanti diganti data dari backend API.
// nada: bad = bahan bermasalah, ok = bahan cukup. blokir = stok tidak cukup (tombol Proses nonaktif).
return [
  // Jumlah per status (seluruh antrean aktif; tabel hanya menampilkan sebagian)
  'hitung' => ['semua' => 19, 'pending' => 6, 'tertunda' => 1, 'diproses' => 8, 'selesai' => 4],
  'orders' => [
    ['kode' => '#RI-20261008-015', 'pelanggan' => 'Sinta Rahma',     'jam' => '10.31', 'layanan' => 'Print Dokumen F4 · 25 lbr',       'catatan' => 'Kertas F4 habis',                   'nada' => 'bad', 'berkas' => 'proposal-kegiatan.pdf', 'total' => 15000, 'status' => 'pending',  'blokir' => true,  'habis' => 'Kertas F4 HVS 70 gsm', 'butuh' => 'stok 0 rim · dibutuhkan 0,05 rim (25 lembar)'],
    ['kode' => '#RI-20261008-016', 'pelanggan' => 'Andra Maheswara', 'jam' => '09.50', 'layanan' => 'Print Dokumen F4 · 30 lbr',       'catatan' => 'Menunggu Kertas F4 (est. 10 Okt)',  'nada' => 'bad', 'berkas' => 'laporan-praktikum.pdf', 'total' => 18000, 'status' => 'tertunda', 'blokir' => true,  'habis' => 'Kertas F4 HVS 70 gsm', 'butuh' => 'stok 0 rim · dibutuhkan 0,06 rim (30 lembar)'],
    ['kode' => '#RI-20261008-013', 'pelanggan' => 'Nadia Putri',     'jam' => '09.05', 'layanan' => 'Fotokopi A4 · 30 lbr',            'catatan' => '',                                  'nada' => '',    'berkas' => 'modul-kuliah.pdf',      'total' => 9000,  'status' => 'diproses', 'blokir' => false, 'habis' => '', 'butuh' => ''],
    ['kode' => '#RI-20261008-012', 'pelanggan' => 'Fikri Ramadhan',  'jam' => '08.48', 'layanan' => 'Print Dokumen A4 Warna · 12 lbr', 'catatan' => 'Bahan cukup',                      'nada' => 'ok',  'berkas' => 'poster-seminar.pdf',    'total' => 12000, 'status' => 'pending',  'blokir' => false, 'habis' => '', 'butuh' => ''],
    ['kode' => '#RI-20261008-010', 'pelanggan' => 'Bima Saputra',    'jam' => '08.15', 'layanan' => 'Fotokopi A4 · 50 lbr',            'catatan' => 'Bahan cukup',                      'nada' => 'ok',  'berkas' => 'soal-latihan.docx',     'total' => 15000, 'status' => 'pending',  'blokir' => false, 'habis' => '', 'butuh' => ''],
    ['kode' => '#RI-20261008-008', 'pelanggan' => 'Rama Aditya',     'jam' => '07 Okt','layanan' => 'Jilid spiral + soft cover',       'catatan' => '',                                  'nada' => '',    'berkas' => 'skripsi-bab1-5.pdf',    'total' => 13000, 'status' => 'selesai',  'blokir' => false, 'habis' => '', 'butuh' => ''],
  ],
];