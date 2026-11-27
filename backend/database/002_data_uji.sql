-- =====================================================================
-- EasyPrint - Data uji realistis (dihasilkan oleh generate_seed.py)
-- Jalankan SETELAH 001_integrasi_fe_be.sql:
--   psql -U postgres -d easyprint_db -f database/seed/002_data_uji.sql
-- Semua akun baru memakai password yang sama dengan akun tim: EasyPrintTest2026!
-- Catatan: stok bahan baku di-set ulang mengikuti simulasi sirkulasi stok.
-- =====================================================================
BEGIN;
SET search_path = public;

ALTER TABLE pesanan DISABLE TRIGGER trg_log_status_pesanan;
ALTER TABLE pesanan DISABLE TRIGGER trg_notifikasi_status_pesanan;
ALTER TABLE pesanan DISABLE TRIGGER trg_pesanan_updated_at;
ALTER TABLE bahan_baku DISABLE TRIGGER trg_log_mutasi_stok;
ALTER TABLE bahan_baku DISABLE TRIGGER trg_peringatan_stok_kritis;

-- Pelanggan
INSERT INTO users (id, nama, email, password, no_telepon, role, created_at) VALUES
  (6, 'Sinta Maharani', 'sinta.maharani@example.com', '$2y$12$Kq69GPS6KG4rw/agt0tG2Oasoocc.JyovblFO//pI/TNUN43MjwGG', '081233450011', 'pelanggan', '2026-09-16 10:00:00'),
  (7, 'Rizky Pratama', 'rizky.pratama@example.com', '$2y$12$Kq69GPS6KG4rw/agt0tG2Oasoocc.JyovblFO//pI/TNUN43MjwGG', '085746120982', 'pelanggan', '2026-09-17 10:00:00'),
  (8, 'Nadia Putri', 'nadia.putri@example.com', '$2y$12$Kq69GPS6KG4rw/agt0tG2Oasoocc.JyovblFO//pI/TNUN43MjwGG', '081335667120', 'pelanggan', '2026-09-18 10:00:00'),
  (9, 'Bima Saputra', 'bima.saputra@example.com', '$2y$12$Kq69GPS6KG4rw/agt0tG2Oasoocc.JyovblFO//pI/TNUN43MjwGG', '089677542210', 'pelanggan', '2026-09-10 10:00:00'),
  (10, 'Rani Anggraini', 'rani.anggraini@example.com', '$2y$12$Kq69GPS6KG4rw/agt0tG2Oasoocc.JyovblFO//pI/TNUN43MjwGG', '082142553009', 'pelanggan', '2026-09-11 10:00:00'),
  (11, 'Fikri Hidayat', 'fikri.hidayat@example.com', '$2y$12$Kq69GPS6KG4rw/agt0tG2Oasoocc.JyovblFO//pI/TNUN43MjwGG', '081931224576', 'pelanggan', '2026-09-12 10:00:00'),
  (12, 'Dewi Lestari', 'dewi.lestari@example.com', '$2y$12$Kq69GPS6KG4rw/agt0tG2Oasoocc.JyovblFO//pI/TNUN43MjwGG', '087853119024', 'pelanggan', '2026-09-13 10:00:00'),
  (13, 'CV Mitra Karya', 'admin@mitrakarya.example.com', '$2y$12$Kq69GPS6KG4rw/agt0tG2Oasoocc.JyovblFO//pI/TNUN43MjwGG', '0341491234', 'pelanggan', '2026-09-14 10:00:00');

-- Layanan
UPDATE layanan SET kategori = 'cetak' WHERE id IN (1, 3);
UPDATE layanan SET kategori = 'finishing' WHERE id = 2;
INSERT INTO layanan (id, nama_layanan, harga, satuan, deskripsi, kategori) VALUES
  (4, 'Print A4 Warna', 1500, 'lembar', 'Cetak full color kertas HVS A4 80gr', 'cetak'),
  (5, 'Print F4 Hitam Putih', 600, 'lembar', 'Cetak hitam putih kertas HVS F4 70gr', 'cetak'),
  (6, 'Fotokopi A4 Hitam Putih', 300, 'lembar', 'Fotokopi hitam putih kertas A4 70gr', 'fotokopi'),
  (7, 'Fotokopi F4 Hitam Putih', 350, 'lembar', 'Fotokopi hitam putih kertas F4 70gr', 'fotokopi'),
  (8, 'Jilid Spiral', 7000, 'buku', 'Jilid ring spiral dengan cover mika bening', 'finishing'),
  (9, 'Jilid Hard Cover', 25000, 'buku', 'Jilid hard cover untuk skripsi/laporan', 'finishing');

-- Bahan baku (stok final di-set di bagian akhir)
INSERT INTO bahan_baku (id, nama_bahan, stok, satuan, reorder_point, harga_satuan) VALUES
  (4, 'Kertas HVS F4 70gr', 0, 'rim', 3, 52000),
  (5, 'Tinta Warna Epson 003 (CMY)', 0, 'botol', 3, 85000),
  (6, 'Kertas Art Paper A4 150gr', 0, 'pak', 2, 65000),
  (7, 'Ring Spiral', 0, 'pcs', 20, 1500),
  (8, 'Hard Cover A4', 0, 'pcs', 5, 9000),
  (9, 'Lakban Hitam', 0, 'roll', 2, 12000);

-- Bill of Materials
INSERT INTO bahan_layanan (layanan_id, bahan_baku_id, jumlah_kebutuhan) VALUES
  (1, 2, 0.0003),
  (2, 9, 0.0500),
  (3, 6, 0.0100),
  (3, 5, 0.0020),
  (4, 1, 0.0020),
  (4, 5, 0.0010),
  (5, 4, 0.0020),
  (5, 2, 0.0003),
  (6, 1, 0.0020),
  (7, 4, 0.0020),
  (8, 7, 1.0000),
  (8, 3, 1.0000),
  (9, 8, 1.0000);

-- Pesanan
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (100, 'RI-20260914-505', 9, 30000, 'Diambil', NULL, '2026-09-14 08:31:18', true, NULL, 'QRIS', '2026-09-15 10:44:18', '2026-09-15 10:44:18');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (101, 'RI-20260915-260', 7, 42600, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-09-15 10:23:27', true, NULL, 'Tunai', '2026-09-16 04:51:27', '2026-09-16 04:51:27');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (102, 'RI-20260915-082', 8, 5000, 'Selesai', 'Fotokopi 2 rangkap', '2026-09-15 13:22:40', true, NULL, NULL, NULL, '2026-09-15 15:08:40');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (103, 'RI-20260916-701', 6, 90000, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-09-16 08:24:16', true, NULL, 'Tunai', '2026-09-16 22:16:16', '2026-09-16 22:16:16');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (104, 'RI-20260917-120', 1, 27400, 'Ditolak', NULL, '2026-09-17 15:50:58', false, 'Berkas tidak terbaca / buram. Mohon scan ulang dengan resolusi lebih tinggi.', NULL, NULL, '2026-09-17 16:07:58');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (105, 'RI-20260917-672', 1, 30000, 'Diambil', 'Fotokopi 2 rangkap', '2026-09-17 09:44:12', true, NULL, 'QRIS', '2026-09-18 02:26:12', '2026-09-18 02:26:12');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (106, 'RI-20260917-684', 9, 17000, 'Diambil', 'Ambil sore hari', '2026-09-17 12:32:13', true, NULL, 'Tunai', '2026-09-18 18:20:13', '2026-09-18 18:20:13');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (107, 'RI-20260918-894', 7, 150000, 'Diambil', 'Mohon dijilid rapi', '2026-09-18 11:41:53', true, NULL, 'Tunai', '2026-09-19 08:09:53', '2026-09-19 08:09:53');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (108, 'RI-20260918-542', 13, 57000, 'Diambil', 'Fotokopi 2 rangkap', '2026-09-18 14:30:18', true, NULL, 'Tunai', '2026-09-19 21:53:18', '2026-09-19 21:53:18');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (109, 'RI-20260919-249', 6, 925000, 'Diambil', 'Cover warna biru', '2026-09-19 14:35:22', true, NULL, 'Tunai', '2026-09-20 06:38:22', '2026-09-20 06:38:22');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (110, 'RI-20260919-328', 12, 14400, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-09-19 15:37:48', true, NULL, 'Tunai', '2026-09-20 06:31:48', '2026-09-20 06:31:48');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (111, 'RI-20260919-967', 10, 9600, 'Diambil', NULL, '2026-09-19 15:40:58', true, NULL, 'Tunai', '2026-09-20 08:50:58', '2026-09-20 08:50:58');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (112, 'RI-20260920-448', 7, 2000, 'Diambil', NULL, '2026-09-20 14:02:43', true, NULL, 'Tunai', '2026-09-21 04:24:43', '2026-09-21 04:24:43');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (113, 'RI-20260921-295', 12, 13000, 'Diambil', 'Ambil sore hari', '2026-09-21 10:52:56', true, NULL, 'Tunai', '2026-09-22 14:26:56', '2026-09-22 14:26:56');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (114, 'RI-20260921-918', 8, 30000, 'Diambil', 'Mohon dijilid rapi', '2026-09-21 08:46:50', true, NULL, 'Tunai', '2026-09-22 01:38:50', '2026-09-22 01:38:50');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (115, 'RI-20260922-268', 8, 24000, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-09-22 11:08:11', true, NULL, 'Tunai', '2026-09-23 18:06:11', '2026-09-23 18:06:11');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (116, 'RI-20260922-625', 6, 5000, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-09-22 11:58:01', true, NULL, 'Tunai', '2026-09-22 23:01:01', '2026-09-22 23:01:01');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (117, 'RI-20260923-973', 10, 8400, 'Diambil', NULL, '2026-09-23 14:24:55', true, NULL, 'QRIS', '2026-09-24 00:30:55', '2026-09-24 00:30:55');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (118, 'RI-20260923-663', 13, 6200, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-09-23 14:36:39', true, NULL, 'QRIS', '2026-09-24 11:22:39', '2026-09-24 11:22:39');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (119, 'RI-20260924-924', 7, 10600, 'Diambil', NULL, '2026-09-24 12:51:41', true, NULL, 'Tunai', '2026-09-24 22:32:41', '2026-09-24 22:32:41');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (120, 'RI-20260925-863', 6, 18000, 'Ditolak', NULL, '2026-09-25 08:06:44', false, 'Jumlah halaman berkas tidak sesuai dengan jumlah lembar yang dipesan.', NULL, NULL, '2026-09-25 08:51:44');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (121, 'RI-20260925-215', 1, 17000, 'Diambil', 'Mohon dijilid rapi', '2026-09-25 14:34:20', true, NULL, 'Tunai', '2026-09-26 04:13:20', '2026-09-26 04:13:20');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (122, 'RI-20260926-605', 1, 50000, 'Ditolak', 'Halaman 1-3 berwarna', '2026-09-26 17:14:43', false, 'Berkas terkunci password sehingga tidak dapat dicetak.', NULL, NULL, '2026-09-26 17:50:43');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (123, 'RI-20260926-652', 11, 27000, 'Diambil', NULL, '2026-09-26 10:13:48', true, NULL, 'Tunai', '2026-09-27 16:38:48', '2026-09-27 16:38:48');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (124, 'RI-20260926-601', 8, 60000, 'Diambil', 'Margin kiri lebih lebar', '2026-09-26 13:37:50', true, NULL, 'QRIS', '2026-09-27 01:32:50', '2026-09-27 01:32:50');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (125, 'RI-20260927-214', 13, 100000, 'Diambil', 'Fotokopi 2 rangkap', '2026-09-27 14:54:34', true, NULL, 'Tunai', '2026-09-28 18:52:34', '2026-09-28 18:52:34');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (126, 'RI-20260928-405', 1, 18000, 'Diambil', 'Cetak bolak-balik', '2026-09-28 09:24:09', true, NULL, 'QRIS', '2026-09-29 06:53:09', '2026-09-29 06:53:09');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (127, 'RI-20260928-617', 8, 10000, 'Diambil', 'Cetak bolak-balik', '2026-09-28 18:46:45', true, NULL, 'Tunai', '2026-09-30 01:43:45', '2026-09-30 01:43:45');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (128, 'RI-20260929-744', 7, 150000, 'Diambil', NULL, '2026-09-29 16:01:08', true, NULL, 'Tunai', '2026-10-01 01:24:08', '2026-10-01 01:24:08');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (129, 'RI-20260930-549', 11, 21000, 'Diambil', 'Fotokopi 2 rangkap', '2026-09-30 13:28:03', true, NULL, 'Tunai', '2026-10-01 02:21:03', '2026-10-01 02:21:03');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (130, 'RI-20260930-634', 11, 300000, 'Diambil', 'Cover warna biru', '2026-09-30 16:17:17', true, NULL, 'Tunai', '2026-10-01 11:21:17', '2026-10-01 11:21:17');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (131, 'RI-20261001-174', 11, 12000, 'Diambil', NULL, '2026-10-01 15:57:34', true, NULL, 'QRIS', '2026-10-01 22:31:34', '2026-10-01 22:31:34');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (132, 'RI-20261002-271', 6, 24000, 'Diambil', 'Cover warna biru', '2026-10-02 10:28:59', true, NULL, 'Tunai', '2026-10-03 20:10:59', '2026-10-03 20:10:59');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (133, 'RI-20261003-345', 6, 24000, 'Diambil', 'Halaman 1-3 berwarna', '2026-10-03 08:09:31', true, NULL, 'Tunai', '2026-10-03 10:45:31', '2026-10-03 10:45:31');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (134, 'RI-20261003-344', 8, 625000, 'Diambil', 'Fotokopi 2 rangkap', '2026-10-03 12:47:30', true, NULL, 'QRIS', '2026-10-04 13:21:30', '2026-10-04 13:21:30');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (135, 'RI-20261003-001', 4, 25000, 'Selesai', NULL, '2026-10-03 16:59:41', true, NULL, NULL, NULL, '2026-10-03 19:05:41');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (136, 'RI-20261005-090', 11, 2800, 'Diambil', NULL, '2026-10-05 17:06:32', true, NULL, 'Tunai', '2026-10-06 09:48:32', '2026-10-06 09:48:32');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (137, 'RI-20261005-890', 13, 34000, 'Diambil', 'Halaman 1-3 berwarna', '2026-10-05 12:49:49', true, NULL, 'QRIS', '2026-10-05 21:15:49', '2026-10-05 21:15:49');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (138, 'RI-20261006-295', 10, 12000, 'Diambil', NULL, '2026-10-06 09:23:00', true, NULL, 'Tunai', '2026-10-07 02:11:00', '2026-10-07 02:11:00');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (139, 'RI-20261006-929', 6, 22000, 'Selesai', 'Untuk tugas kuliah, mohon cepat', '2026-10-06 15:12:50', true, NULL, NULL, NULL, '2026-10-06 17:09:50');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (140, 'RI-20261006-174', 8, 254000, 'Diambil', 'Halaman 1-3 berwarna', '2026-10-06 17:54:44', true, NULL, 'Tunai', '2026-10-07 12:36:44', '2026-10-07 12:36:44');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (141, 'RI-20261007-103', 13, 32200, 'Ditolak', NULL, '2026-10-07 17:49:16', false, 'Berkas terkunci password sehingga tidak dapat dicetak.', NULL, NULL, '2026-10-07 18:26:16');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (142, 'RI-20261007-832', 12, 21000, 'Diambil', NULL, '2026-10-07 18:11:08', true, NULL, 'Tunai', '2026-10-08 20:37:08', '2026-10-08 20:37:08');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (143, 'RI-20261008-221', 6, 60000, 'Diambil', NULL, '2026-10-08 18:26:17', true, NULL, 'QRIS', '2026-10-09 02:24:17', '2026-10-09 02:24:17');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (144, 'RI-20261009-574', 11, 55000, 'Diambil', NULL, '2026-10-09 18:56:36', true, NULL, 'Tunai', '2026-10-10 04:08:36', '2026-10-10 04:08:36');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (145, 'RI-20261009-764', 12, 80000, 'Diambil', 'Cover warna biru', '2026-10-09 16:34:10', true, NULL, 'QRIS', '2026-10-09 20:23:10', '2026-10-09 20:23:10');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (146, 'RI-20261010-799', 6, 15000, 'Diambil', 'Cetak bolak-balik', '2026-10-10 14:11:04', true, NULL, 'QRIS', '2026-10-11 20:04:04', '2026-10-11 20:04:04');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (147, 'RI-20261010-051', 6, 34000, 'Ditolak', 'Halaman 1-3 berwarna', '2026-10-10 14:46:28', false, 'Berkas terkunci password sehingga tidak dapat dicetak.', NULL, NULL, '2026-10-10 15:14:28');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (148, 'RI-20261011-509', 4, 250000, 'Diambil', NULL, '2026-10-11 14:55:30', true, NULL, 'Tunai', '2026-10-11 18:36:30', '2026-10-11 18:36:30');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (149, 'RI-20261012-669', 4, 250000, 'Diambil', NULL, '2026-10-12 11:59:04', true, NULL, 'Tunai', '2026-10-12 23:21:04', '2026-10-12 23:21:04');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (150, 'RI-20261012-971', 13, 75000, 'Diambil', 'Cover warna biru', '2026-10-12 08:00:47', true, NULL, 'QRIS', '2026-10-12 17:43:47', '2026-10-12 17:43:47');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (151, 'RI-20261013-583', 10, 19000, 'Diambil', NULL, '2026-10-13 10:03:37', true, NULL, 'Tunai', '2026-10-13 19:11:37', '2026-10-13 19:11:37');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (152, 'RI-20261013-097', 11, 20000, 'Diambil', NULL, '2026-10-13 10:01:39', true, NULL, 'Tunai', '2026-10-14 17:18:39', '2026-10-14 17:18:39');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (153, 'RI-20261013-105', 7, 27600, 'Diambil', 'Ambil sore hari', '2026-10-13 15:33:18', true, NULL, 'Tunai', '2026-10-13 22:33:18', '2026-10-13 22:33:18');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (154, 'RI-20261014-987', 10, 8400, 'Diambil', NULL, '2026-10-14 11:13:02', true, NULL, 'Tunai', '2026-10-15 05:54:02', '2026-10-15 05:54:02');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (155, 'RI-20261014-817', 9, 10600, 'Diambil', 'Margin kiri lebih lebar', '2026-10-14 12:49:57', true, NULL, 'QRIS', '2026-10-14 16:41:57', '2026-10-14 16:41:57');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (156, 'RI-20261014-815', 11, 150000, 'Diambil', 'Mohon dijilid rapi', '2026-10-14 13:10:50', true, NULL, 'Tunai', '2026-10-15 04:32:50', '2026-10-15 04:32:50');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (157, 'RI-20261015-063', 7, 17800, 'Diambil', NULL, '2026-10-15 16:12:34', true, NULL, 'Tunai', '2026-10-16 09:43:34', '2026-10-16 09:43:34');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (158, 'RI-20261015-860', 7, 10000, 'Diambil', 'Fotokopi 2 rangkap', '2026-10-15 16:17:04', true, NULL, 'QRIS', '2026-10-16 23:47:04', '2026-10-16 23:47:04');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (159, 'RI-20261016-753', 6, 60000, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-10-16 12:09:50', true, NULL, 'QRIS', '2026-10-17 11:14:50', '2026-10-17 11:14:50');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (160, 'RI-20261017-456', 8, 9400, 'Diambil', 'Mohon dijilid rapi', '2026-10-17 14:49:05', true, NULL, 'Tunai', '2026-10-17 18:09:05', '2026-10-17 18:09:05');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (161, 'RI-20261017-129', 1, 2000, 'Diambil', 'Mohon dijilid rapi', '2026-10-17 08:04:35', true, NULL, 'QRIS', '2026-10-18 09:41:35', '2026-10-18 09:41:35');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (162, 'RI-20261019-638', 8, 4500, 'Diambil', 'Margin kiri lebih lebar', '2026-10-19 15:16:42', true, NULL, 'Tunai', '2026-10-20 07:39:42', '2026-10-20 07:39:42');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (163, 'RI-20261020-623', 6, 28400, 'Ditolak', 'Fotokopi 2 rangkap', '2026-10-20 09:50:32', false, 'Jumlah halaman berkas tidak sesuai dengan jumlah lembar yang dipesan.', NULL, NULL, '2026-10-20 10:21:32');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (164, 'RI-20261020-888', 6, 25000, 'Diambil', 'Mohon dijilid rapi', '2026-10-20 12:45:54', true, NULL, 'Tunai', '2026-10-20 16:52:54', '2026-10-20 16:52:54');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (165, 'RI-20261021-896', 1, 14000, 'Diambil', 'Mohon dijilid rapi', '2026-10-21 13:31:33', true, NULL, 'Tunai', '2026-10-21 18:50:33', '2026-10-21 18:50:33');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (166, 'RI-20261021-993', 11, 2400, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-10-21 18:03:58', true, NULL, 'QRIS', '2026-10-22 03:45:58', '2026-10-22 03:45:58');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (167, 'RI-20261021-858', 6, 37000, 'Diambil', 'Halaman 1-3 berwarna', '2026-10-21 09:42:04', true, NULL, 'QRIS', '2026-10-21 20:07:04', '2026-10-21 20:07:04');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (168, 'RI-20261022-466', 7, 187500, 'Diambil', NULL, '2026-10-22 18:04:04', true, NULL, 'QRIS', '2026-10-23 14:41:04', '2026-10-23 14:41:04');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (169, 'RI-20261023-219', 13, 19000, 'Diambil', NULL, '2026-10-23 08:50:22', true, NULL, 'QRIS', '2026-10-23 20:54:22', '2026-10-23 20:54:22');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (170, 'RI-20261024-493', 1, 80000, 'Diambil', 'Cetak bolak-balik', '2026-10-24 14:46:13', true, NULL, 'Tunai', '2026-10-25 22:42:13', '2026-10-25 22:42:13');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (171, 'RI-20261024-390', 5, 45000, 'Diambil', NULL, '2026-10-24 10:51:24', true, NULL, 'Tunai', '2026-10-25 02:19:24', '2026-10-25 02:19:24');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (172, 'RI-20261024-813', 9, 46000, 'Ditolak', 'Margin kiri lebih lebar', '2026-10-24 15:46:26', false, 'Berkas terkunci password sehingga tidak dapat dicetak.', NULL, NULL, '2026-10-24 16:10:26');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (173, 'RI-20261025-531', 11, 150000, 'Selesai', NULL, '2026-10-25 12:58:03', true, NULL, NULL, NULL, '2026-10-25 16:36:03');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (174, 'RI-20261026-720', 1, 30000, 'Diambil', NULL, '2026-10-26 12:26:37', true, NULL, 'Tunai', '2026-10-27 10:32:37', '2026-10-27 10:32:37');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (175, 'RI-20261027-014', 11, 36000, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-10-27 18:38:00', true, NULL, 'Tunai', '2026-10-28 20:27:00', '2026-10-28 20:27:00');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (176, 'RI-20261027-010', 4, 47000, 'Diambil', 'Halaman 1-3 berwarna', '2026-10-27 08:17:21', true, NULL, 'Tunai', '2026-10-27 22:34:21', '2026-10-27 22:34:21');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (177, 'RI-20261028-607', 9, 25000, 'Diambil', 'Cover warna biru', '2026-10-28 08:09:08', true, NULL, 'QRIS', '2026-10-29 13:29:08', '2026-10-29 13:29:08');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (178, 'RI-20261029-293', 8, 77000, 'Diambil', 'Cetak bolak-balik', '2026-10-29 18:47:02', true, NULL, 'Tunai', '2026-10-31 01:26:02', '2026-10-31 01:26:02');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (179, 'RI-20261030-693', 13, 58000, 'Selesai', 'Margin kiri lebih lebar', '2026-10-30 18:41:37', true, NULL, NULL, NULL, '2026-10-30 20:18:37');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (180, 'RI-20261030-593', 7, 12000, 'Diambil', 'Halaman 1-3 berwarna', '2026-10-30 16:25:56', true, NULL, 'Tunai', '2026-10-31 23:50:56', '2026-10-31 23:50:56');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (181, 'RI-20261031-856', 6, 18000, 'Ditolak', 'Cover warna biru', '2026-10-31 18:06:49', false, 'Jumlah halaman berkas tidak sesuai dengan jumlah lembar yang dipesan.', NULL, NULL, '2026-10-31 18:30:49');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (182, 'RI-20261031-762', 6, 40000, 'Diambil', 'Untuk tugas kuliah, mohon cepat', '2026-10-31 15:06:21', true, NULL, 'Tunai', '2026-10-31 22:25:21', '2026-10-31 22:25:21');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (183, 'RI-20261101-892', 13, 7500, 'Diambil', NULL, '2026-11-01 15:38:43', true, NULL, 'Tunai', '2026-11-02 00:18:43', '2026-11-02 00:18:43');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (184, 'RI-20261102-750', 4, 30000, 'Diambil', NULL, '2026-11-02 11:55:31', true, NULL, 'Tunai', '2026-11-02 18:18:31', '2026-11-02 18:18:31');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (185, 'RI-20261103-420', 8, 150000, 'Diambil', 'Ambil sore hari', '2026-11-03 15:15:03', true, NULL, 'Tunai', '2026-11-03 21:02:03', '2026-11-03 21:02:03');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (186, 'RI-20261104-632', 7, 30000, 'Diambil', 'Cover warna biru', '2026-11-04 16:00:46', true, NULL, 'Tunai', '2026-11-05 11:32:46', '2026-11-05 11:32:46');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (187, 'RI-20261104-887', 12, 43200, 'Diambil', 'Ambil sore hari', '2026-11-04 09:25:58', true, NULL, 'QRIS', '2026-11-04 23:04:58', '2026-11-04 23:04:58');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (188, 'RI-20261105-077', 7, 21000, 'Diambil', NULL, '2026-11-05 18:04:47', true, NULL, 'Tunai', '2026-11-06 09:59:47', '2026-11-06 09:59:47');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (189, 'RI-20261106-633', 10, 9000, 'Diambil', NULL, '2026-11-06 14:51:54', true, NULL, 'Tunai', '2026-11-07 08:54:54', '2026-11-07 08:54:54');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (190, 'RI-20261107-164', 1, 26600, 'Diambil', NULL, '2026-11-07 09:48:53', true, NULL, 'Tunai', '2026-11-07 23:19:53', '2026-11-07 23:19:53');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (191, 'RI-20261107-162', 6, 40000, 'Diambil', NULL, '2026-11-07 14:34:47', true, NULL, 'Tunai', '2026-11-08 01:29:47', '2026-11-08 01:29:47');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (192, 'RI-20261109-835', 8, 15000, 'Diproses', 'Ambil sore hari', '2026-11-09 13:48:42', true, NULL, NULL, NULL, '2026-11-09 14:25:42');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (193, 'RI-20261109-402', 10, 10800, 'Diverifikasi', 'Ambil sore hari', '2026-11-09 14:53:35', false, NULL, NULL, NULL, '2026-11-09 15:31:35');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (194, 'RI-20261109-206', 10, 30000, 'Diproses', NULL, '2026-11-09 10:15:42', true, NULL, NULL, NULL, '2026-11-09 11:08:42');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (195, 'RI-20261109-428', 4, 60000, 'Pending', 'Cetak bolak-balik', '2026-11-09 10:40:58', false, NULL, NULL, NULL, '2026-11-09 10:40:58');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (196, 'RI-20261109-049', 8, 187500, 'Diverifikasi', 'Untuk tugas kuliah, mohon cepat', '2026-11-09 14:59:55', false, NULL, NULL, NULL, '2026-11-09 15:04:55');
INSERT INTO pesanan (id, kode_pesanan, user_id, total_harga, status, catatan, created_at, stok_dikurangi, alasan_tolak, metode_bayar, dibayar_at, updated_at) VALUES (197, 'RI-20261109-777', 6, 85000, 'Tertunda - Stok Tidak Tersedia', 'Skripsi, jilid hard cover warna biru dongker', '2026-11-09 09:12:05', false, NULL, NULL, NULL, '2026-11-09 09:43:05');

-- Detail pesanan (trigger trg_update_total_pesanan ikut menghitung ulang total)
INSERT INTO detail_pesanan (pesanan_id, layanan_id, jumlah, subtotal, file_dokumen, spesifikasi) VALUES
  (100, 1, 10, 5000, 'contoh_dokumen_seed.pdf', '10 lembar x 1 eks'),
  (100, 9, 1, 25000, NULL, '1 buku'),
  (101, 6, 72, 21600, 'contoh_dokumen_seed.pdf', '24 lembar x 3 eks'),
  (101, 8, 3, 21000, NULL, '3 buku'),
  (102, 1, 10, 5000, NULL, '10 lembar x 1 eks | dua sisi'),
  (103, 1, 180, 90000, 'contoh_dokumen_seed.pdf', '36 lembar x 5 eks'),
  (104, 6, 8, 2400, 'contoh_dokumen_seed.pdf', '8 lembar x 1 eks'),
  (104, 9, 1, 25000, NULL, '1 buku'),
  (105, 5, 50, 30000, 'contoh_dokumen_seed.pdf', '50 lembar x 1 eks'),
  (106, 7, 20, 7000, 'contoh_dokumen_seed.pdf', '10 lembar x 2 eks'),
  (106, 2, 2, 10000, NULL, '2 buku'),
  (107, 4, 100, 150000, 'contoh_dokumen_seed.pdf', '100 lembar x 1 eks | dua sisi'),
  (108, 1, 100, 50000, 'contoh_dokumen_seed.pdf', '100 lembar x 1 eks'),
  (108, 8, 1, 7000, NULL, '1 buku'),
  (109, 4, 600, 900000, 'contoh_dokumen_seed.pdf', '120 lembar x 5 eks | dua sisi'),
  (109, 2, 5, 25000, NULL, '5 buku'),
  (110, 5, 24, 14400, 'contoh_dokumen_seed.pdf', '24 lembar x 1 eks'),
  (111, 5, 16, 9600, 'contoh_dokumen_seed.pdf', '8 lembar x 2 eks'),
  (112, 1, 4, 2000, 'contoh_dokumen_seed.pdf', '4 lembar x 1 eks'),
  (113, 5, 10, 6000, 'contoh_dokumen_seed.pdf', '10 lembar x 1 eks'),
  (113, 8, 1, 7000, NULL, '1 buku'),
  (114, 6, 30, 9000, 'contoh_dokumen_seed.pdf', '10 lembar x 3 eks'),
  (114, 8, 3, 21000, NULL, '3 buku'),
  (115, 6, 80, 24000, 'contoh_dokumen_seed.pdf', '80 lembar x 1 eks'),
  (116, 1, 10, 5000, 'contoh_dokumen_seed.pdf', '10 lembar x 1 eks'),
  (117, 7, 24, 8400, 'contoh_dokumen_seed.pdf', '12 lembar x 2 eks'),
  (118, 6, 4, 1200, 'contoh_dokumen_seed.pdf', '4 lembar x 1 eks'),
  (118, 2, 1, 5000, NULL, '1 buku'),
  (119, 6, 12, 3600, 'contoh_dokumen_seed.pdf', '12 lembar x 1 eks | dua sisi'),
  (119, 8, 1, 7000, NULL, '1 buku'),
  (120, 4, 12, 18000, NULL, '12 lembar x 1 eks'),
  (121, 1, 20, 10000, NULL, '20 lembar x 1 eks'),
  (121, 8, 1, 7000, NULL, '1 buku'),
  (122, 1, 80, 40000, 'contoh_dokumen_seed.pdf', '40 lembar x 2 eks'),
  (122, 2, 2, 10000, NULL, '2 buku'),
  (123, 6, 90, 27000, 'contoh_dokumen_seed.pdf', '30 lembar x 3 eks'),
  (124, 5, 100, 60000, 'contoh_dokumen_seed.pdf', '100 lembar x 1 eks'),
  (125, 1, 200, 100000, 'contoh_dokumen_seed.pdf', '100 lembar x 2 eks'),
  (126, 5, 30, 18000, 'contoh_dokumen_seed.pdf', '30 lembar x 1 eks | dua sisi'),
  (127, 6, 10, 3000, 'contoh_dokumen_seed.pdf', '10 lembar x 1 eks | dua sisi'),
  (127, 8, 1, 7000, NULL, '1 buku'),
  (128, 4, 100, 150000, 'contoh_dokumen_seed.pdf', '50 lembar x 2 eks | dua sisi'),
  (129, 7, 40, 14000, 'contoh_dokumen_seed.pdf', '40 lembar x 1 eks'),
  (129, 8, 1, 7000, NULL, '1 buku'),
  (130, 4, 200, 300000, 'contoh_dokumen_seed.pdf', '40 lembar x 5 eks'),
  (131, 1, 24, 12000, 'contoh_dokumen_seed.pdf', '12 lembar x 2 eks'),
  (132, 1, 48, 24000, 'contoh_dokumen_seed.pdf', '24 lembar x 2 eks'),
  (133, 1, 20, 10000, 'contoh_dokumen_seed.pdf', '10 lembar x 2 eks'),
  (133, 8, 2, 14000, NULL, '2 buku'),
  (134, 3, 250, 625000, 'contoh_dokumen_seed.pdf', '50 lembar x 5 eks'),
  (135, 1, 36, 18000, 'contoh_dokumen_seed.pdf', '36 lembar x 1 eks'),
  (135, 8, 1, 7000, NULL, '1 buku'),
  (136, 7, 8, 2800, 'contoh_dokumen_seed.pdf', '8 lembar x 1 eks | dua sisi'),
  (137, 1, 40, 20000, 'contoh_dokumen_seed.pdf', '20 lembar x 2 eks | dua sisi'),
  (137, 8, 2, 14000, NULL, '2 buku'),
  (138, 6, 40, 12000, 'contoh_dokumen_seed.pdf', '40 lembar x 1 eks'),
  (139, 6, 50, 15000, NULL, '50 lembar x 1 eks'),
  (139, 8, 1, 7000, NULL, '1 buku'),
  (140, 4, 160, 240000, 'contoh_dokumen_seed.pdf', '80 lembar x 2 eks | dua sisi'),
  (140, 8, 2, 14000, NULL, '2 buku'),
  (141, 6, 24, 7200, NULL, '24 lembar x 1 eks | dua sisi'),
  (141, 9, 1, 25000, NULL, '1 buku'),
  (142, 7, 60, 21000, NULL, '60 lembar x 1 eks'),
  (143, 6, 200, 60000, 'contoh_dokumen_seed.pdf', '100 lembar x 2 eks'),
  (144, 1, 100, 50000, 'contoh_dokumen_seed.pdf', '100 lembar x 1 eks'),
  (144, 2, 1, 5000, NULL, '1 buku'),
  (145, 1, 160, 80000, 'contoh_dokumen_seed.pdf', '80 lembar x 2 eks'),
  (146, 1, 30, 15000, 'contoh_dokumen_seed.pdf', '10 lembar x 3 eks'),
  (147, 1, 40, 20000, NULL, '20 lembar x 2 eks'),
  (147, 8, 2, 14000, NULL, '2 buku'),
  (148, 3, 100, 250000, NULL, '100 lembar x 1 eks'),
  (149, 3, 100, 250000, 'contoh_dokumen_seed.pdf', '50 lembar x 2 eks'),
  (150, 1, 100, 50000, 'contoh_dokumen_seed.pdf', '100 lembar x 1 eks | dua sisi'),
  (150, 9, 1, 25000, NULL, '1 buku'),
  (151, 1, 24, 12000, NULL, '24 lembar x 1 eks'),
  (151, 8, 1, 7000, NULL, '1 buku'),
  (152, 1, 40, 20000, 'contoh_dokumen_seed.pdf', '8 lembar x 5 eks'),
  (153, 7, 36, 12600, 'contoh_dokumen_seed.pdf', '12 lembar x 3 eks'),
  (153, 2, 3, 15000, NULL, '3 buku'),
  (154, 7, 24, 8400, NULL, '24 lembar x 1 eks'),
  (155, 6, 12, 3600, 'contoh_dokumen_seed.pdf', '12 lembar x 1 eks | dua sisi'),
  (155, 8, 1, 7000, NULL, '1 buku'),
  (156, 4, 100, 150000, NULL, '100 lembar x 1 eks'),
  (157, 6, 36, 10800, NULL, '36 lembar x 1 eks'),
  (157, 8, 1, 7000, NULL, '1 buku'),
  (158, 1, 20, 10000, 'contoh_dokumen_seed.pdf', '20 lembar x 1 eks'),
  (159, 1, 120, 60000, 'contoh_dokumen_seed.pdf', '120 lembar x 1 eks | dua sisi'),
  (160, 5, 4, 2400, 'contoh_dokumen_seed.pdf', '4 lembar x 1 eks | dua sisi'),
  (160, 8, 1, 7000, NULL, '1 buku'),
  (161, 1, 4, 2000, 'contoh_dokumen_seed.pdf', '4 lembar x 1 eks'),
  (162, 6, 15, 4500, NULL, '15 lembar x 1 eks'),
  (163, 6, 48, 14400, 'contoh_dokumen_seed.pdf', '24 lembar x 2 eks'),
  (163, 8, 2, 14000, NULL, '2 buku'),
  (164, 1, 40, 20000, 'contoh_dokumen_seed.pdf', '40 lembar x 1 eks | dua sisi'),
  (164, 2, 1, 5000, NULL, '1 buku'),
  (165, 6, 30, 9000, 'contoh_dokumen_seed.pdf', '30 lembar x 1 eks'),
  (165, 2, 1, 5000, NULL, '1 buku'),
  (166, 5, 4, 2400, 'contoh_dokumen_seed.pdf', '4 lembar x 1 eks'),
  (167, 6, 100, 30000, 'contoh_dokumen_seed.pdf', '100 lembar x 1 eks | dua sisi'),
  (167, 8, 1, 7000, NULL, '1 buku'),
  (168, 3, 75, 187500, 'contoh_dokumen_seed.pdf', '25 lembar x 3 eks | dua sisi'),
  (169, 1, 24, 12000, 'contoh_dokumen_seed.pdf', '24 lembar x 1 eks | dua sisi'),
  (169, 8, 1, 7000, NULL, '1 buku'),
  (170, 6, 100, 30000, NULL, '50 lembar x 2 eks'),
  (170, 9, 2, 50000, NULL, '2 buku'),
  (171, 1, 90, 45000, 'contoh_dokumen_seed.pdf', '30 lembar x 3 eks'),
  (172, 6, 120, 36000, 'contoh_dokumen_seed.pdf', '60 lembar x 2 eks | dua sisi'),
  (172, 2, 2, 10000, NULL, '2 buku'),
  (173, 4, 100, 150000, 'contoh_dokumen_seed.pdf', '100 lembar x 1 eks'),
  (174, 5, 50, 30000, 'contoh_dokumen_seed.pdf', '10 lembar x 5 eks'),
  (175, 6, 120, 36000, 'contoh_dokumen_seed.pdf', '60 lembar x 2 eks'),
  (176, 1, 80, 40000, NULL, '80 lembar x 1 eks | dua sisi'),
  (176, 8, 1, 7000, NULL, '1 buku'),
  (177, 1, 50, 25000, NULL, '50 lembar x 1 eks'),
  (178, 5, 120, 72000, 'contoh_dokumen_seed.pdf', '120 lembar x 1 eks'),
  (178, 2, 1, 5000, NULL, '1 buku'),
  (179, 6, 160, 48000, 'contoh_dokumen_seed.pdf', '80 lembar x 2 eks'),
  (179, 2, 2, 10000, NULL, '2 buku'),
  (180, 6, 40, 12000, NULL, '40 lembar x 1 eks'),
  (181, 6, 60, 18000, 'contoh_dokumen_seed.pdf', '60 lembar x 1 eks'),
  (182, 1, 30, 15000, NULL, '30 lembar x 1 eks'),
  (182, 9, 1, 25000, NULL, '1 buku'),
  (183, 1, 15, 7500, NULL, '15 lembar x 1 eks | dua sisi'),
  (184, 4, 20, 30000, 'contoh_dokumen_seed.pdf', '20 lembar x 1 eks'),
  (185, 4, 100, 150000, 'contoh_dokumen_seed.pdf', '100 lembar x 1 eks | dua sisi'),
  (186, 1, 60, 30000, 'contoh_dokumen_seed.pdf', '60 lembar x 1 eks'),
  (187, 5, 72, 43200, 'contoh_dokumen_seed.pdf', '36 lembar x 2 eks'),
  (188, 7, 60, 21000, 'contoh_dokumen_seed.pdf', '60 lembar x 1 eks'),
  (189, 1, 4, 2000, 'contoh_dokumen_seed.pdf', '4 lembar x 1 eks'),
  (189, 8, 1, 7000, NULL, '1 buku'),
  (190, 5, 36, 21600, 'contoh_dokumen_seed.pdf', '36 lembar x 1 eks'),
  (190, 2, 1, 5000, NULL, '1 buku'),
  (191, 1, 80, 40000, 'contoh_dokumen_seed.pdf', '40 lembar x 2 eks'),
  (192, 4, 10, 15000, 'contoh_dokumen_seed.pdf', '10 lembar x 1 eks'),
  (193, 6, 36, 10800, 'contoh_dokumen_seed.pdf', '36 lembar x 1 eks | dua sisi'),
  (194, 6, 100, 30000, 'contoh_dokumen_seed.pdf', '50 lembar x 2 eks'),
  (195, 1, 90, 45000, NULL, '30 lembar x 3 eks'),
  (195, 2, 3, 15000, NULL, '3 buku'),
  (196, 3, 75, 187500, 'contoh_dokumen_seed.pdf', '25 lembar x 3 eks | dua sisi'),
  (197, 1, 120, 60000, 'contoh_dokumen_seed.pdf', '120 lembar x 1 eks'),
  (197, 9, 1, 25000, NULL, '1 buku');

-- Riwayat status
INSERT INTO riwayat_status (pesanan_id, status_lama, status_baru, keterangan, diubah_oleh, created_at) VALUES
  (100, NULL, 'Pending', 'Pesanan dibuat', 9, '2026-09-14 08:31:18'),
  (100, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-14 08:52:18'),
  (100, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-14 08:56:18'),
  (100, 'Diproses', 'Selesai', NULL, 2, '2026-09-14 11:33:18'),
  (100, 'Selesai', 'Diambil', NULL, 2, '2026-09-15 10:44:18'),
  (101, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-09-15 10:23:27'),
  (101, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-15 10:30:27'),
  (101, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-15 10:39:27'),
  (101, 'Diproses', 'Selesai', NULL, 2, '2026-09-15 13:52:27'),
  (101, 'Selesai', 'Diambil', NULL, 2, '2026-09-16 04:51:27'),
  (102, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-09-15 13:22:40'),
  (102, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-15 13:54:40'),
  (102, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-15 14:20:40'),
  (102, 'Diproses', 'Selesai', NULL, 2, '2026-09-15 15:08:40'),
  (103, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-09-16 08:24:16'),
  (103, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-16 08:31:16'),
  (103, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-16 09:01:16'),
  (103, 'Diproses', 'Selesai', NULL, 2, '2026-09-16 12:19:16'),
  (103, 'Selesai', 'Diambil', NULL, 2, '2026-09-16 22:16:16'),
  (104, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-09-17 15:50:58'),
  (104, 'Pending', 'Ditolak', 'Berkas tidak terbaca / buram. Mohon scan ulang dengan resolusi lebih tinggi.', 2, '2026-09-17 16:07:58'),
  (105, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-09-17 09:44:12'),
  (105, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-17 10:23:12'),
  (105, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-17 10:53:12'),
  (105, 'Diproses', 'Selesai', NULL, 2, '2026-09-17 12:41:12'),
  (105, 'Selesai', 'Diambil', NULL, 2, '2026-09-18 02:26:12'),
  (106, NULL, 'Pending', 'Pesanan dibuat', 9, '2026-09-17 12:32:13'),
  (106, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-17 12:47:13'),
  (106, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-17 13:05:13'),
  (106, 'Diproses', 'Selesai', NULL, 2, '2026-09-17 15:11:13'),
  (106, 'Selesai', 'Diambil', NULL, 2, '2026-09-18 18:20:13'),
  (107, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-09-18 11:41:53'),
  (107, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-18 11:59:53'),
  (107, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-18 12:26:53'),
  (107, 'Diproses', 'Selesai', NULL, 2, '2026-09-18 13:24:53'),
  (107, 'Selesai', 'Diambil', NULL, 2, '2026-09-19 08:09:53'),
  (108, NULL, 'Pending', 'Pesanan dibuat', 13, '2026-09-18 14:30:18'),
  (108, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-18 14:46:18'),
  (108, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-18 15:15:18'),
  (108, 'Diproses', 'Selesai', NULL, 2, '2026-09-18 17:51:18'),
  (108, 'Selesai', 'Diambil', NULL, 2, '2026-09-19 21:53:18'),
  (109, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-09-19 14:35:22'),
  (109, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-19 14:59:22'),
  (109, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-19 15:22:22'),
  (109, 'Diproses', 'Selesai', NULL, 2, '2026-09-19 17:43:22'),
  (109, 'Selesai', 'Diambil', NULL, 2, '2026-09-20 06:38:22'),
  (110, NULL, 'Pending', 'Pesanan dibuat', 12, '2026-09-19 15:37:48'),
  (110, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-19 15:55:48'),
  (110, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-19 15:58:48'),
  (110, 'Diproses', 'Selesai', NULL, 2, '2026-09-19 17:12:48'),
  (110, 'Selesai', 'Diambil', NULL, 2, '2026-09-20 06:31:48'),
  (111, NULL, 'Pending', 'Pesanan dibuat', 10, '2026-09-19 15:40:58'),
  (111, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-19 16:05:58'),
  (111, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-19 16:34:58'),
  (111, 'Diproses', 'Selesai', NULL, 2, '2026-09-19 18:40:58'),
  (111, 'Selesai', 'Diambil', NULL, 2, '2026-09-20 08:50:58'),
  (112, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-09-20 14:02:43'),
  (112, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-20 14:08:43'),
  (112, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-20 14:36:43'),
  (112, 'Diproses', 'Selesai', NULL, 2, '2026-09-20 16:33:43'),
  (112, 'Selesai', 'Diambil', NULL, 2, '2026-09-21 04:24:43'),
  (113, NULL, 'Pending', 'Pesanan dibuat', 12, '2026-09-21 10:52:56'),
  (113, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-21 11:06:56'),
  (113, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-21 11:30:56'),
  (113, 'Diproses', 'Selesai', NULL, 2, '2026-09-21 14:17:56'),
  (113, 'Selesai', 'Diambil', NULL, 2, '2026-09-22 14:26:56'),
  (114, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-09-21 08:46:50'),
  (114, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-21 09:15:50'),
  (114, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-21 09:44:50'),
  (114, 'Diproses', 'Selesai', NULL, 2, '2026-09-21 10:18:50'),
  (114, 'Selesai', 'Diambil', NULL, 2, '2026-09-22 01:38:50'),
  (115, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-09-22 11:08:11'),
  (115, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-22 11:39:11'),
  (115, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-22 11:56:11'),
  (115, 'Diproses', 'Selesai', NULL, 2, '2026-09-22 15:08:11'),
  (115, 'Selesai', 'Diambil', NULL, 2, '2026-09-23 18:06:11'),
  (116, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-09-22 11:58:01'),
  (116, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-22 12:18:01'),
  (116, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-22 12:43:01'),
  (116, 'Diproses', 'Selesai', NULL, 2, '2026-09-22 13:53:01'),
  (116, 'Selesai', 'Diambil', NULL, 2, '2026-09-22 23:01:01'),
  (117, NULL, 'Pending', 'Pesanan dibuat', 10, '2026-09-23 14:24:55'),
  (117, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-23 14:33:55'),
  (117, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-23 15:01:55'),
  (117, 'Diproses', 'Selesai', NULL, 2, '2026-09-23 17:16:55'),
  (117, 'Selesai', 'Diambil', NULL, 2, '2026-09-24 00:30:55'),
  (118, NULL, 'Pending', 'Pesanan dibuat', 13, '2026-09-23 14:36:39'),
  (118, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-23 15:09:39'),
  (118, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-23 15:22:39'),
  (118, 'Diproses', 'Selesai', NULL, 2, '2026-09-23 16:59:39'),
  (118, 'Selesai', 'Diambil', NULL, 2, '2026-09-24 11:22:39'),
  (119, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-09-24 12:51:41'),
  (119, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-24 13:02:41'),
  (119, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-24 13:17:41'),
  (119, 'Diproses', 'Selesai', NULL, 2, '2026-09-24 14:15:41'),
  (119, 'Selesai', 'Diambil', NULL, 2, '2026-09-24 22:32:41'),
  (120, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-09-25 08:06:44'),
  (120, 'Pending', 'Ditolak', 'Jumlah halaman berkas tidak sesuai dengan jumlah lembar yang dipesan.', 2, '2026-09-25 08:51:44'),
  (121, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-09-25 14:34:20'),
  (121, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-25 14:48:20'),
  (121, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-25 15:07:20'),
  (121, 'Diproses', 'Selesai', NULL, 2, '2026-09-25 16:49:20'),
  (121, 'Selesai', 'Diambil', NULL, 2, '2026-09-26 04:13:20'),
  (122, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-09-26 17:14:43'),
  (122, 'Pending', 'Ditolak', 'Berkas terkunci password sehingga tidak dapat dicetak.', 2, '2026-09-26 17:50:43'),
  (123, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-09-26 10:13:48'),
  (123, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-26 10:37:48'),
  (123, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-26 11:02:48'),
  (123, 'Diproses', 'Selesai', NULL, 2, '2026-09-26 13:29:48'),
  (123, 'Selesai', 'Diambil', NULL, 2, '2026-09-27 16:38:48'),
  (124, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-09-26 13:37:50'),
  (124, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-26 14:14:50'),
  (124, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-26 14:39:50'),
  (124, 'Diproses', 'Selesai', NULL, 2, '2026-09-26 15:33:50'),
  (124, 'Selesai', 'Diambil', NULL, 2, '2026-09-27 01:32:50'),
  (125, NULL, 'Pending', 'Pesanan dibuat', 13, '2026-09-27 14:54:34'),
  (125, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-27 15:33:34'),
  (125, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-27 15:51:34'),
  (125, 'Diproses', 'Selesai', NULL, 2, '2026-09-27 17:35:34'),
  (125, 'Selesai', 'Diambil', NULL, 2, '2026-09-28 18:52:34'),
  (126, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-09-28 09:24:09'),
  (126, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-28 09:32:09'),
  (126, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-28 09:40:09'),
  (126, 'Diproses', 'Selesai', NULL, 2, '2026-09-28 11:54:09'),
  (126, 'Selesai', 'Diambil', NULL, 2, '2026-09-29 06:53:09'),
  (127, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-09-28 18:46:45'),
  (127, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-28 19:07:45'),
  (127, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-28 19:10:45'),
  (127, 'Diproses', 'Selesai', NULL, 2, '2026-09-28 21:02:45'),
  (127, 'Selesai', 'Diambil', NULL, 2, '2026-09-30 01:43:45'),
  (128, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-09-29 16:01:08'),
  (128, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-29 16:23:08'),
  (128, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-29 16:52:08'),
  (128, 'Diproses', 'Selesai', NULL, 2, '2026-09-29 19:36:08'),
  (128, 'Selesai', 'Diambil', NULL, 2, '2026-10-01 01:24:08'),
  (129, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-09-30 13:28:03'),
  (129, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-30 13:38:03'),
  (129, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-30 13:55:03'),
  (129, 'Diproses', 'Selesai', NULL, 2, '2026-09-30 16:48:03'),
  (129, 'Selesai', 'Diambil', NULL, 2, '2026-10-01 02:21:03'),
  (130, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-09-30 16:17:17'),
  (130, 'Pending', 'Diverifikasi', NULL, 2, '2026-09-30 16:30:17'),
  (130, 'Diverifikasi', 'Diproses', NULL, 2, '2026-09-30 16:36:17'),
  (130, 'Diproses', 'Selesai', NULL, 2, '2026-09-30 19:16:17'),
  (130, 'Selesai', 'Diambil', NULL, 2, '2026-10-01 11:21:17'),
  (131, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-10-01 15:57:34'),
  (131, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-01 16:20:34'),
  (131, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-01 16:26:34'),
  (131, 'Diproses', 'Selesai', NULL, 2, '2026-10-01 19:40:34'),
  (131, 'Selesai', 'Diambil', NULL, 2, '2026-10-01 22:31:34'),
  (132, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-02 10:28:59'),
  (132, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-02 11:07:59'),
  (132, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-02 11:17:59'),
  (132, 'Diproses', 'Selesai', NULL, 2, '2026-10-02 14:14:59'),
  (132, 'Selesai', 'Diambil', NULL, 2, '2026-10-03 20:10:59'),
  (133, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-03 08:09:31'),
  (133, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-03 08:27:31'),
  (133, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-03 08:30:31'),
  (133, 'Diproses', 'Selesai', NULL, 2, '2026-10-03 08:58:31'),
  (133, 'Selesai', 'Diambil', NULL, 2, '2026-10-03 10:45:31'),
  (134, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-10-03 12:47:30'),
  (134, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-03 13:22:30'),
  (134, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-03 13:48:30'),
  (134, 'Diproses', 'Selesai', NULL, 2, '2026-10-03 16:04:30'),
  (134, 'Selesai', 'Diambil', NULL, 2, '2026-10-04 13:21:30'),
  (135, NULL, 'Pending', 'Pesanan dibuat', 4, '2026-10-03 16:59:41'),
  (135, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-03 17:31:41'),
  (135, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-03 17:54:41'),
  (135, 'Diproses', 'Selesai', NULL, 2, '2026-10-03 19:05:41'),
  (136, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-10-05 17:06:32'),
  (136, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-05 17:24:32'),
  (136, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-05 17:41:32'),
  (136, 'Diproses', 'Selesai', NULL, 2, '2026-10-05 20:37:32'),
  (136, 'Selesai', 'Diambil', NULL, 2, '2026-10-06 09:48:32'),
  (137, NULL, 'Pending', 'Pesanan dibuat', 13, '2026-10-05 12:49:49'),
  (137, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-05 13:21:49'),
  (137, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-05 13:32:49'),
  (137, 'Diproses', 'Selesai', NULL, 2, '2026-10-05 16:17:49'),
  (137, 'Selesai', 'Diambil', NULL, 2, '2026-10-05 21:15:49'),
  (138, NULL, 'Pending', 'Pesanan dibuat', 10, '2026-10-06 09:23:00'),
  (138, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-06 09:51:00'),
  (138, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-06 10:20:00'),
  (138, 'Diproses', 'Selesai', NULL, 2, '2026-10-06 11:32:00'),
  (138, 'Selesai', 'Diambil', NULL, 2, '2026-10-07 02:11:00'),
  (139, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-06 15:12:50'),
  (139, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-06 15:31:50'),
  (139, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-06 15:47:50'),
  (139, 'Diproses', 'Selesai', NULL, 2, '2026-10-06 17:09:50'),
  (140, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-10-06 17:54:44'),
  (140, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-06 18:27:44'),
  (140, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-06 18:55:44'),
  (140, 'Diproses', 'Selesai', NULL, 2, '2026-10-06 22:11:44'),
  (140, 'Selesai', 'Diambil', NULL, 2, '2026-10-07 12:36:44'),
  (141, NULL, 'Pending', 'Pesanan dibuat', 13, '2026-10-07 17:49:16'),
  (141, 'Pending', 'Ditolak', 'Berkas terkunci password sehingga tidak dapat dicetak.', 2, '2026-10-07 18:26:16'),
  (142, NULL, 'Pending', 'Pesanan dibuat', 12, '2026-10-07 18:11:08'),
  (142, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-07 18:24:08'),
  (142, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-07 18:45:08'),
  (142, 'Diproses', 'Selesai', NULL, 2, '2026-10-07 19:51:08'),
  (142, 'Selesai', 'Diambil', NULL, 2, '2026-10-08 20:37:08'),
  (143, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-08 18:26:17'),
  (143, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-08 18:51:17'),
  (143, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-08 19:21:17'),
  (143, 'Diproses', 'Selesai', NULL, 2, '2026-10-08 20:09:17'),
  (143, 'Selesai', 'Diambil', NULL, 2, '2026-10-09 02:24:17'),
  (144, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-10-09 18:56:36'),
  (144, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-09 19:16:36'),
  (144, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-09 19:39:36'),
  (144, 'Diproses', 'Selesai', NULL, 2, '2026-10-09 22:59:36'),
  (144, 'Selesai', 'Diambil', NULL, 2, '2026-10-10 04:08:36'),
  (145, NULL, 'Pending', 'Pesanan dibuat', 12, '2026-10-09 16:34:10'),
  (145, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-09 17:03:10'),
  (145, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-09 17:32:10'),
  (145, 'Diproses', 'Selesai', NULL, 2, '2026-10-09 18:45:10'),
  (145, 'Selesai', 'Diambil', NULL, 2, '2026-10-09 20:23:10'),
  (146, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-10 14:11:04'),
  (146, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-10 14:42:04'),
  (146, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-10 14:55:04'),
  (146, 'Diproses', 'Selesai', NULL, 2, '2026-10-10 16:48:04'),
  (146, 'Selesai', 'Diambil', NULL, 2, '2026-10-11 20:04:04'),
  (147, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-10 14:46:28'),
  (147, 'Pending', 'Ditolak', 'Berkas terkunci password sehingga tidak dapat dicetak.', 2, '2026-10-10 15:14:28'),
  (148, NULL, 'Pending', 'Pesanan dibuat', 4, '2026-10-11 14:55:30'),
  (148, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-11 15:12:30'),
  (148, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-11 15:30:30'),
  (148, 'Diproses', 'Selesai', NULL, 2, '2026-10-11 16:55:30'),
  (148, 'Selesai', 'Diambil', NULL, 2, '2026-10-11 18:36:30'),
  (149, NULL, 'Pending', 'Pesanan dibuat', 4, '2026-10-12 11:59:04'),
  (149, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-12 12:30:04'),
  (149, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-12 12:33:04'),
  (149, 'Diproses', 'Selesai', NULL, 2, '2026-10-12 15:20:04'),
  (149, 'Selesai', 'Diambil', NULL, 2, '2026-10-12 23:21:04'),
  (150, NULL, 'Pending', 'Pesanan dibuat', 13, '2026-10-12 08:00:47'),
  (150, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-12 08:23:47'),
  (150, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-12 08:51:47'),
  (150, 'Diproses', 'Selesai', NULL, 2, '2026-10-12 11:53:47'),
  (150, 'Selesai', 'Diambil', NULL, 2, '2026-10-12 17:43:47'),
  (151, NULL, 'Pending', 'Pesanan dibuat', 10, '2026-10-13 10:03:37'),
  (151, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-13 10:18:37'),
  (151, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-13 10:24:37'),
  (151, 'Diproses', 'Selesai', NULL, 2, '2026-10-13 12:16:37'),
  (151, 'Selesai', 'Diambil', NULL, 2, '2026-10-13 19:11:37'),
  (152, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-10-13 10:01:39'),
  (152, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-13 10:24:39'),
  (152, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-13 10:41:39'),
  (152, 'Diproses', 'Selesai', NULL, 2, '2026-10-13 12:44:39'),
  (152, 'Selesai', 'Diambil', NULL, 2, '2026-10-14 17:18:39'),
  (153, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-10-13 15:33:18'),
  (153, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-13 15:50:18'),
  (153, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-13 16:17:18'),
  (153, 'Diproses', 'Selesai', NULL, 2, '2026-10-13 18:58:18'),
  (153, 'Selesai', 'Diambil', NULL, 2, '2026-10-13 22:33:18'),
  (154, NULL, 'Pending', 'Pesanan dibuat', 10, '2026-10-14 11:13:02'),
  (154, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-14 11:51:02'),
  (154, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-14 12:14:02'),
  (154, 'Diproses', 'Selesai', NULL, 2, '2026-10-14 13:54:02'),
  (154, 'Selesai', 'Diambil', NULL, 2, '2026-10-15 05:54:02'),
  (155, NULL, 'Pending', 'Pesanan dibuat', 9, '2026-10-14 12:49:57'),
  (155, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-14 13:05:57'),
  (155, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-14 13:19:57'),
  (155, 'Diproses', 'Selesai', NULL, 2, '2026-10-14 15:12:57'),
  (155, 'Selesai', 'Diambil', NULL, 2, '2026-10-14 16:41:57'),
  (156, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-10-14 13:10:50'),
  (156, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-14 13:38:50'),
  (156, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-14 14:07:50'),
  (156, 'Diproses', 'Selesai', NULL, 2, '2026-10-14 14:51:50'),
  (156, 'Selesai', 'Diambil', NULL, 2, '2026-10-15 04:32:50'),
  (157, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-10-15 16:12:34'),
  (157, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-15 16:44:34'),
  (157, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-15 16:54:34'),
  (157, 'Diproses', 'Selesai', NULL, 2, '2026-10-15 19:49:34'),
  (157, 'Selesai', 'Diambil', NULL, 2, '2026-10-16 09:43:34'),
  (158, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-10-15 16:17:04'),
  (158, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-15 16:56:04'),
  (158, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-15 17:26:04'),
  (158, 'Diproses', 'Selesai', NULL, 2, '2026-10-15 20:24:04'),
  (158, 'Selesai', 'Diambil', NULL, 2, '2026-10-16 23:47:04'),
  (159, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-16 12:09:50'),
  (159, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-16 12:34:50'),
  (159, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-16 12:51:50'),
  (159, 'Diproses', 'Selesai', NULL, 2, '2026-10-16 13:35:50'),
  (159, 'Selesai', 'Diambil', NULL, 2, '2026-10-17 11:14:50'),
  (160, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-10-17 14:49:05'),
  (160, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-17 15:15:05'),
  (160, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-17 15:20:05'),
  (160, 'Diproses', 'Selesai', NULL, 2, '2026-10-17 17:05:05'),
  (160, 'Selesai', 'Diambil', NULL, 2, '2026-10-17 18:09:05'),
  (161, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-10-17 08:04:35'),
  (161, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-17 08:37:35'),
  (161, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-17 09:04:35'),
  (161, 'Diproses', 'Selesai', NULL, 2, '2026-10-17 11:13:35'),
  (161, 'Selesai', 'Diambil', NULL, 2, '2026-10-18 09:41:35'),
  (162, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-10-19 15:16:42'),
  (162, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-19 15:34:42'),
  (162, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-19 16:03:42'),
  (162, 'Diproses', 'Selesai', NULL, 2, '2026-10-19 19:15:42'),
  (162, 'Selesai', 'Diambil', NULL, 2, '2026-10-20 07:39:42'),
  (163, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-20 09:50:32'),
  (163, 'Pending', 'Ditolak', 'Jumlah halaman berkas tidak sesuai dengan jumlah lembar yang dipesan.', 2, '2026-10-20 10:21:32'),
  (164, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-20 12:45:54'),
  (164, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-20 13:12:54'),
  (164, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-20 13:23:54'),
  (164, 'Diproses', 'Selesai', NULL, 2, '2026-10-20 15:02:54'),
  (164, 'Selesai', 'Diambil', NULL, 2, '2026-10-20 16:52:54'),
  (165, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-10-21 13:31:33'),
  (165, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-21 14:00:33'),
  (165, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-21 14:26:33'),
  (165, 'Diproses', 'Selesai', NULL, 2, '2026-10-21 17:35:33'),
  (165, 'Selesai', 'Diambil', NULL, 2, '2026-10-21 18:50:33'),
  (166, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-10-21 18:03:58'),
  (166, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-21 18:14:58'),
  (166, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-21 18:20:58'),
  (166, 'Diproses', 'Selesai', NULL, 2, '2026-10-21 21:23:58'),
  (166, 'Selesai', 'Diambil', NULL, 2, '2026-10-22 03:45:58'),
  (167, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-21 09:42:04'),
  (167, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-21 09:48:04'),
  (167, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-21 10:14:04'),
  (167, 'Diproses', 'Selesai', NULL, 2, '2026-10-21 11:15:04'),
  (167, 'Selesai', 'Diambil', NULL, 2, '2026-10-21 20:07:04'),
  (168, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-10-22 18:04:04'),
  (168, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-22 18:38:04'),
  (168, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-22 18:54:04'),
  (168, 'Diproses', 'Selesai', NULL, 2, '2026-10-22 20:12:04'),
  (168, 'Selesai', 'Diambil', NULL, 2, '2026-10-23 14:41:04'),
  (169, NULL, 'Pending', 'Pesanan dibuat', 13, '2026-10-23 08:50:22'),
  (169, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-23 08:58:22'),
  (169, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-23 09:08:22'),
  (169, 'Diproses', 'Selesai', NULL, 2, '2026-10-23 10:32:22'),
  (169, 'Selesai', 'Diambil', NULL, 2, '2026-10-23 20:54:22'),
  (170, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-10-24 14:46:13'),
  (170, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-24 15:20:13'),
  (170, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-24 15:34:13'),
  (170, 'Diproses', 'Selesai', NULL, 2, '2026-10-24 17:44:13'),
  (170, 'Selesai', 'Diambil', NULL, 2, '2026-10-25 22:42:13'),
  (171, NULL, 'Pending', 'Pesanan dibuat', 5, '2026-10-24 10:51:24'),
  (171, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-24 11:00:24'),
  (171, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-24 11:05:24'),
  (171, 'Diproses', 'Selesai', NULL, 2, '2026-10-24 12:38:24'),
  (171, 'Selesai', 'Diambil', NULL, 2, '2026-10-25 02:19:24'),
  (172, NULL, 'Pending', 'Pesanan dibuat', 9, '2026-10-24 15:46:26'),
  (172, 'Pending', 'Ditolak', 'Berkas terkunci password sehingga tidak dapat dicetak.', 2, '2026-10-24 16:10:26'),
  (173, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-10-25 12:58:03'),
  (173, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-25 13:11:03'),
  (173, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-25 13:38:03'),
  (173, 'Diproses', 'Selesai', NULL, 2, '2026-10-25 16:36:03'),
  (174, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-10-26 12:26:37'),
  (174, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-26 13:05:37'),
  (174, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-26 13:26:37'),
  (174, 'Diproses', 'Selesai', NULL, 2, '2026-10-26 14:42:37'),
  (174, 'Selesai', 'Diambil', NULL, 2, '2026-10-27 10:32:37'),
  (175, NULL, 'Pending', 'Pesanan dibuat', 11, '2026-10-27 18:38:00'),
  (175, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-27 19:07:00'),
  (175, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-27 19:15:00'),
  (175, 'Diproses', 'Selesai', NULL, 2, '2026-10-27 21:33:00'),
  (175, 'Selesai', 'Diambil', NULL, 2, '2026-10-28 20:27:00'),
  (176, NULL, 'Pending', 'Pesanan dibuat', 4, '2026-10-27 08:17:21'),
  (176, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-27 08:43:21'),
  (176, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-27 09:11:21'),
  (176, 'Diproses', 'Selesai', NULL, 2, '2026-10-27 12:24:21'),
  (176, 'Selesai', 'Diambil', NULL, 2, '2026-10-27 22:34:21'),
  (177, NULL, 'Pending', 'Pesanan dibuat', 9, '2026-10-28 08:09:08'),
  (177, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-28 08:37:08'),
  (177, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-28 09:00:08'),
  (177, 'Diproses', 'Selesai', NULL, 2, '2026-10-28 11:06:08'),
  (177, 'Selesai', 'Diambil', NULL, 2, '2026-10-29 13:29:08'),
  (178, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-10-29 18:47:02'),
  (178, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-29 19:22:02'),
  (178, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-29 19:39:02'),
  (178, 'Diproses', 'Selesai', NULL, 2, '2026-10-29 21:45:02'),
  (178, 'Selesai', 'Diambil', NULL, 2, '2026-10-31 01:26:02'),
  (179, NULL, 'Pending', 'Pesanan dibuat', 13, '2026-10-30 18:41:37'),
  (179, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-30 19:05:37'),
  (179, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-30 19:34:37'),
  (179, 'Diproses', 'Selesai', NULL, 2, '2026-10-30 20:18:37'),
  (180, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-10-30 16:25:56'),
  (180, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-30 17:03:56'),
  (180, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-30 17:07:56'),
  (180, 'Diproses', 'Selesai', NULL, 2, '2026-10-30 18:25:56'),
  (180, 'Selesai', 'Diambil', NULL, 2, '2026-10-31 23:50:56'),
  (181, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-31 18:06:49'),
  (181, 'Pending', 'Ditolak', 'Jumlah halaman berkas tidak sesuai dengan jumlah lembar yang dipesan.', 2, '2026-10-31 18:30:49'),
  (182, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-10-31 15:06:21'),
  (182, 'Pending', 'Diverifikasi', NULL, 2, '2026-10-31 15:26:21'),
  (182, 'Diverifikasi', 'Diproses', NULL, 2, '2026-10-31 15:33:21'),
  (182, 'Diproses', 'Selesai', NULL, 2, '2026-10-31 17:41:21'),
  (182, 'Selesai', 'Diambil', NULL, 2, '2026-10-31 22:25:21'),
  (183, NULL, 'Pending', 'Pesanan dibuat', 13, '2026-11-01 15:38:43'),
  (183, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-01 16:12:43'),
  (183, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-01 16:20:43'),
  (183, 'Diproses', 'Selesai', NULL, 2, '2026-11-01 18:59:43'),
  (183, 'Selesai', 'Diambil', NULL, 2, '2026-11-02 00:18:43'),
  (184, NULL, 'Pending', 'Pesanan dibuat', 4, '2026-11-02 11:55:31'),
  (184, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-02 12:00:31'),
  (184, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-02 12:10:31'),
  (184, 'Diproses', 'Selesai', NULL, 2, '2026-11-02 13:08:31'),
  (184, 'Selesai', 'Diambil', NULL, 2, '2026-11-02 18:18:31'),
  (185, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-11-03 15:15:03'),
  (185, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-03 15:44:03'),
  (185, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-03 15:57:03'),
  (185, 'Diproses', 'Selesai', NULL, 2, '2026-11-03 18:33:03'),
  (185, 'Selesai', 'Diambil', NULL, 2, '2026-11-03 21:02:03'),
  (186, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-11-04 16:00:46'),
  (186, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-04 16:29:46'),
  (186, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-04 16:54:46'),
  (186, 'Diproses', 'Selesai', NULL, 2, '2026-11-04 19:20:46'),
  (186, 'Selesai', 'Diambil', NULL, 2, '2026-11-05 11:32:46'),
  (187, NULL, 'Pending', 'Pesanan dibuat', 12, '2026-11-04 09:25:58'),
  (187, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-04 10:00:58'),
  (187, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-04 10:20:58'),
  (187, 'Diproses', 'Selesai', NULL, 2, '2026-11-04 11:33:58'),
  (187, 'Selesai', 'Diambil', NULL, 2, '2026-11-04 23:04:58'),
  (188, NULL, 'Pending', 'Pesanan dibuat', 7, '2026-11-05 18:04:47'),
  (188, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-05 18:43:47'),
  (188, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-05 18:57:47'),
  (188, 'Diproses', 'Selesai', NULL, 2, '2026-11-05 20:24:47'),
  (188, 'Selesai', 'Diambil', NULL, 2, '2026-11-06 09:59:47'),
  (189, NULL, 'Pending', 'Pesanan dibuat', 10, '2026-11-06 14:51:54'),
  (189, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-06 15:24:54'),
  (189, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-06 15:48:54'),
  (189, 'Diproses', 'Selesai', NULL, 2, '2026-11-06 17:08:54'),
  (189, 'Selesai', 'Diambil', NULL, 2, '2026-11-07 08:54:54'),
  (190, NULL, 'Pending', 'Pesanan dibuat', 1, '2026-11-07 09:48:53'),
  (190, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-07 10:04:53'),
  (190, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-07 10:19:53'),
  (190, 'Diproses', 'Selesai', NULL, 2, '2026-11-07 12:19:53'),
  (190, 'Selesai', 'Diambil', NULL, 2, '2026-11-07 23:19:53'),
  (191, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-11-07 14:34:47'),
  (191, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-07 14:48:47'),
  (191, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-07 15:06:47'),
  (191, 'Diproses', 'Selesai', NULL, 2, '2026-11-07 17:29:47'),
  (191, 'Selesai', 'Diambil', NULL, 2, '2026-11-08 01:29:47'),
  (192, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-11-09 13:48:42'),
  (192, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-09 14:21:42'),
  (192, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-09 14:25:42'),
  (193, NULL, 'Pending', 'Pesanan dibuat', 10, '2026-11-09 14:53:35'),
  (193, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-09 15:31:35'),
  (194, NULL, 'Pending', 'Pesanan dibuat', 10, '2026-11-09 10:15:42'),
  (194, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-09 10:50:42'),
  (194, 'Diverifikasi', 'Diproses', NULL, 2, '2026-11-09 11:08:42'),
  (195, NULL, 'Pending', 'Pesanan dibuat', 4, '2026-11-09 10:40:58'),
  (196, NULL, 'Pending', 'Pesanan dibuat', 8, '2026-11-09 14:59:55'),
  (196, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-09 15:04:55'),
  (197, NULL, 'Pending', 'Pesanan dibuat', 6, '2026-11-09 09:12:05'),
  (197, 'Pending', 'Diverifikasi', NULL, 2, '2026-11-09 09:34:05'),
  (197, 'Diverifikasi', 'Tertunda - Stok Tidak Tersedia', 'Stok Hard Cover A4 tidak mencukupi', 2, '2026-11-09 09:43:05');

-- Riwayat stok: ganti stok awal hasil migrasi dengan simulasi lengkap
DELETE FROM riwayat_stok WHERE keterangan = 'Stok awal (migrasi)';
INSERT INTO riwayat_stok (bahan_baku_id, pesanan_id, jenis, jumlah, stok_sebelum, stok_sesudah, keterangan, user_id, created_at) VALUES
  (1, NULL, 'masuk', 25.0000, 0.0000, 25.0000, 'Stok awal', 3, '2026-09-14 07:00:00'),
  (2, NULL, 'masuk', 3.5000, 0.0000, 3.5000, 'Stok awal', 3, '2026-09-14 07:00:00'),
  (3, NULL, 'masuk', 150.0000, 0.0000, 150.0000, 'Stok awal', 3, '2026-09-14 07:00:00'),
  (4, NULL, 'masuk', 4.5000, 0.0000, 4.5000, 'Stok awal', 3, '2026-09-14 07:00:00'),
  (5, NULL, 'masuk', 5.0000, 0.0000, 5.0000, 'Stok awal', 3, '2026-09-14 07:00:00'),
  (6, NULL, 'masuk', 6.0000, 0.0000, 6.0000, 'Stok awal', 3, '2026-09-14 07:00:00'),
  (7, NULL, 'masuk', 120.0000, 0.0000, 120.0000, 'Stok awal', 3, '2026-09-14 07:00:00'),
  (8, NULL, 'masuk', 20.0000, 0.0000, 20.0000, 'Stok awal', 3, '2026-09-14 07:00:00'),
  (9, NULL, 'masuk', 6.0000, 0.0000, 6.0000, 'Stok awal', 3, '2026-09-14 07:00:00'),
  (1, 100, 'keluar', -0.0200, 25.0000, 24.9800, 'Pemakaian pesanan RI-20260914-505', 2, '2026-09-14 08:56:18'),
  (2, 100, 'keluar', -0.0030, 3.5000, 3.4970, 'Pemakaian pesanan RI-20260914-505', 2, '2026-09-14 08:56:18'),
  (8, 100, 'keluar', -1.0000, 20.0000, 19.0000, 'Pemakaian pesanan RI-20260914-505', 2, '2026-09-14 08:56:18'),
  (1, 101, 'keluar', -0.1440, 24.9800, 24.8360, 'Pemakaian pesanan RI-20260915-260', 2, '2026-09-15 10:39:27'),
  (7, 101, 'keluar', -3.0000, 120.0000, 117.0000, 'Pemakaian pesanan RI-20260915-260', 2, '2026-09-15 10:39:27'),
  (3, 101, 'keluar', -3.0000, 150.0000, 147.0000, 'Pemakaian pesanan RI-20260915-260', 2, '2026-09-15 10:39:27'),
  (1, 102, 'keluar', -0.0200, 24.8360, 24.8160, 'Pemakaian pesanan RI-20260915-082', 2, '2026-09-15 14:20:40'),
  (2, 102, 'keluar', -0.0030, 3.4970, 3.4940, 'Pemakaian pesanan RI-20260915-082', 2, '2026-09-15 14:20:40'),
  (1, 103, 'keluar', -0.3600, 24.8160, 24.4560, 'Pemakaian pesanan RI-20260916-701', 2, '2026-09-16 09:01:16'),
  (2, 103, 'keluar', -0.0540, 3.4940, 3.4400, 'Pemakaian pesanan RI-20260916-701', 2, '2026-09-16 09:01:16'),
  (4, 105, 'keluar', -0.1000, 4.5000, 4.4000, 'Pemakaian pesanan RI-20260917-672', 2, '2026-09-17 10:53:12'),
  (2, 105, 'keluar', -0.0150, 3.4400, 3.4250, 'Pemakaian pesanan RI-20260917-672', 2, '2026-09-17 10:53:12'),
  (4, 106, 'keluar', -0.0400, 4.4000, 4.3600, 'Pemakaian pesanan RI-20260917-684', 2, '2026-09-17 13:05:13'),
  (3, 106, 'keluar', -4.0000, 147.0000, 143.0000, 'Pemakaian pesanan RI-20260917-684', 2, '2026-09-17 13:05:13'),
  (9, 106, 'keluar', -0.1000, 6.0000, 5.9000, 'Pemakaian pesanan RI-20260917-684', 2, '2026-09-17 13:05:13'),
  (1, 107, 'keluar', -0.2000, 24.4560, 24.2560, 'Pemakaian pesanan RI-20260918-894', 2, '2026-09-18 12:26:53'),
  (5, 107, 'keluar', -0.1000, 5.0000, 4.9000, 'Pemakaian pesanan RI-20260918-894', 2, '2026-09-18 12:26:53'),
  (1, 108, 'keluar', -0.2000, 24.2560, 24.0560, 'Pemakaian pesanan RI-20260918-542', 2, '2026-09-18 15:15:18'),
  (2, 108, 'keluar', -0.0300, 3.4250, 3.3950, 'Pemakaian pesanan RI-20260918-542', 2, '2026-09-18 15:15:18'),
  (7, 108, 'keluar', -1.0000, 117.0000, 116.0000, 'Pemakaian pesanan RI-20260918-542', 2, '2026-09-18 15:15:18'),
  (3, 108, 'keluar', -1.0000, 143.0000, 142.0000, 'Pemakaian pesanan RI-20260918-542', 2, '2026-09-18 15:15:18'),
  (1, 109, 'keluar', -1.2000, 24.0560, 22.8560, 'Pemakaian pesanan RI-20260919-249', 2, '2026-09-19 15:22:22'),
  (5, 109, 'keluar', -0.6000, 4.9000, 4.3000, 'Pemakaian pesanan RI-20260919-249', 2, '2026-09-19 15:22:22'),
  (3, 109, 'keluar', -10.0000, 142.0000, 132.0000, 'Pemakaian pesanan RI-20260919-249', 2, '2026-09-19 15:22:22'),
  (9, 109, 'keluar', -0.2500, 5.9000, 5.6500, 'Pemakaian pesanan RI-20260919-249', 2, '2026-09-19 15:22:22'),
  (4, 110, 'keluar', -0.0480, 4.3600, 4.3120, 'Pemakaian pesanan RI-20260919-328', 2, '2026-09-19 15:58:48'),
  (2, 110, 'keluar', -0.0072, 3.3950, 3.3878, 'Pemakaian pesanan RI-20260919-328', 2, '2026-09-19 15:58:48'),
  (4, 111, 'keluar', -0.0320, 4.3120, 4.2800, 'Pemakaian pesanan RI-20260919-967', 2, '2026-09-19 16:34:58'),
  (2, 111, 'keluar', -0.0048, 3.3878, 3.3830, 'Pemakaian pesanan RI-20260919-967', 2, '2026-09-19 16:34:58'),
  (1, 112, 'keluar', -0.0080, 22.8560, 22.8480, 'Pemakaian pesanan RI-20260920-448', 2, '2026-09-20 14:36:43'),
  (2, 112, 'keluar', -0.0012, 3.3830, 3.3818, 'Pemakaian pesanan RI-20260920-448', 2, '2026-09-20 14:36:43'),
  (1, 114, 'keluar', -0.0600, 22.8480, 22.7880, 'Pemakaian pesanan RI-20260921-918', 2, '2026-09-21 09:44:50'),
  (7, 114, 'keluar', -3.0000, 116.0000, 113.0000, 'Pemakaian pesanan RI-20260921-918', 2, '2026-09-21 09:44:50'),
  (3, 114, 'keluar', -3.0000, 132.0000, 129.0000, 'Pemakaian pesanan RI-20260921-918', 2, '2026-09-21 09:44:50'),
  (4, 113, 'keluar', -0.0200, 4.2800, 4.2600, 'Pemakaian pesanan RI-20260921-295', 2, '2026-09-21 11:30:56'),
  (2, 113, 'keluar', -0.0030, 3.3818, 3.3788, 'Pemakaian pesanan RI-20260921-295', 2, '2026-09-21 11:30:56'),
  (7, 113, 'keluar', -1.0000, 113.0000, 112.0000, 'Pemakaian pesanan RI-20260921-295', 2, '2026-09-21 11:30:56'),
  (3, 113, 'keluar', -1.0000, 129.0000, 128.0000, 'Pemakaian pesanan RI-20260921-295', 2, '2026-09-21 11:30:56'),
  (1, 115, 'keluar', -0.1600, 22.7880, 22.6280, 'Pemakaian pesanan RI-20260922-268', 2, '2026-09-22 11:56:11'),
  (1, 116, 'keluar', -0.0200, 22.6280, 22.6080, 'Pemakaian pesanan RI-20260922-625', 2, '2026-09-22 12:43:01'),
  (2, 116, 'keluar', -0.0030, 3.3788, 3.3758, 'Pemakaian pesanan RI-20260922-625', 2, '2026-09-22 12:43:01'),
  (4, 117, 'keluar', -0.0480, 4.2600, 4.2120, 'Pemakaian pesanan RI-20260923-973', 2, '2026-09-23 15:01:55'),
  (1, 118, 'keluar', -0.0080, 22.6080, 22.6000, 'Pemakaian pesanan RI-20260923-663', 2, '2026-09-23 15:22:39'),
  (3, 118, 'keluar', -2.0000, 128.0000, 126.0000, 'Pemakaian pesanan RI-20260923-663', 2, '2026-09-23 15:22:39'),
  (9, 118, 'keluar', -0.0500, 5.6500, 5.6000, 'Pemakaian pesanan RI-20260923-663', 2, '2026-09-23 15:22:39'),
  (1, 119, 'keluar', -0.0240, 22.6000, 22.5760, 'Pemakaian pesanan RI-20260924-924', 2, '2026-09-24 13:17:41'),
  (7, 119, 'keluar', -1.0000, 112.0000, 111.0000, 'Pemakaian pesanan RI-20260924-924', 2, '2026-09-24 13:17:41'),
  (3, 119, 'keluar', -1.0000, 126.0000, 125.0000, 'Pemakaian pesanan RI-20260924-924', 2, '2026-09-24 13:17:41'),
  (1, 121, 'keluar', -0.0400, 22.5760, 22.5360, 'Pemakaian pesanan RI-20260925-215', 2, '2026-09-25 15:07:20'),
  (2, 121, 'keluar', -0.0060, 3.3758, 3.3698, 'Pemakaian pesanan RI-20260925-215', 2, '2026-09-25 15:07:20'),
  (7, 121, 'keluar', -1.0000, 111.0000, 110.0000, 'Pemakaian pesanan RI-20260925-215', 2, '2026-09-25 15:07:20'),
  (3, 121, 'keluar', -1.0000, 125.0000, 124.0000, 'Pemakaian pesanan RI-20260925-215', 2, '2026-09-25 15:07:20'),
  (1, 123, 'keluar', -0.1800, 22.5360, 22.3560, 'Pemakaian pesanan RI-20260926-652', 2, '2026-09-26 11:02:48'),
  (4, 124, 'keluar', -0.2000, 4.2120, 4.0120, 'Pemakaian pesanan RI-20260926-601', 2, '2026-09-26 14:39:50'),
  (2, 124, 'keluar', -0.0300, 3.3698, 3.3398, 'Pemakaian pesanan RI-20260926-601', 2, '2026-09-26 14:39:50'),
  (1, 125, 'keluar', -0.4000, 22.3560, 21.9560, 'Pemakaian pesanan RI-20260927-214', 2, '2026-09-27 15:51:34'),
  (2, 125, 'keluar', -0.0600, 3.3398, 3.2798, 'Pemakaian pesanan RI-20260927-214', 2, '2026-09-27 15:51:34'),
  (4, 126, 'keluar', -0.0600, 4.0120, 3.9520, 'Pemakaian pesanan RI-20260928-405', 2, '2026-09-28 09:40:09'),
  (2, 126, 'keluar', -0.0090, 3.2798, 3.2708, 'Pemakaian pesanan RI-20260928-405', 2, '2026-09-28 09:40:09'),
  (1, 127, 'keluar', -0.0200, 21.9560, 21.9360, 'Pemakaian pesanan RI-20260928-617', 2, '2026-09-28 19:10:45'),
  (7, 127, 'keluar', -1.0000, 110.0000, 109.0000, 'Pemakaian pesanan RI-20260928-617', 2, '2026-09-28 19:10:45'),
  (3, 127, 'keluar', -1.0000, 124.0000, 123.0000, 'Pemakaian pesanan RI-20260928-617', 2, '2026-09-28 19:10:45'),
  (1, 128, 'keluar', -0.2000, 21.9360, 21.7360, 'Pemakaian pesanan RI-20260929-744', 2, '2026-09-29 16:52:08'),
  (5, 128, 'keluar', -0.1000, 4.3000, 4.2000, 'Pemakaian pesanan RI-20260929-744', 2, '2026-09-29 16:52:08'),
  (4, 129, 'keluar', -0.0800, 3.9520, 3.8720, 'Pemakaian pesanan RI-20260930-549', 2, '2026-09-30 13:55:03'),
  (7, 129, 'keluar', -1.0000, 109.0000, 108.0000, 'Pemakaian pesanan RI-20260930-549', 2, '2026-09-30 13:55:03'),
  (3, 129, 'keluar', -1.0000, 123.0000, 122.0000, 'Pemakaian pesanan RI-20260930-549', 2, '2026-09-30 13:55:03'),
  (1, 130, 'keluar', -0.4000, 21.7360, 21.3360, 'Pemakaian pesanan RI-20260930-634', 2, '2026-09-30 16:36:17'),
  (5, 130, 'keluar', -0.2000, 4.2000, 4.0000, 'Pemakaian pesanan RI-20260930-634', 2, '2026-09-30 16:36:17'),
  (4, NULL, 'masuk', 5.0000, 3.8720, 8.8720, 'Pembelian dari supplier', 3, '2026-10-01 07:30:00'),
  (1, 131, 'keluar', -0.0480, 21.3360, 21.2880, 'Pemakaian pesanan RI-20261001-174', 2, '2026-10-01 16:26:34'),
  (2, 131, 'keluar', -0.0072, 3.2708, 3.2636, 'Pemakaian pesanan RI-20261001-174', 2, '2026-10-01 16:26:34'),
  (1, 132, 'keluar', -0.0960, 21.2880, 21.1920, 'Pemakaian pesanan RI-20261002-271', 2, '2026-10-02 11:17:59'),
  (2, 132, 'keluar', -0.0144, 3.2636, 3.2492, 'Pemakaian pesanan RI-20261002-271', 2, '2026-10-02 11:17:59'),
  (1, 133, 'keluar', -0.0400, 21.1920, 21.1520, 'Pemakaian pesanan RI-20261003-345', 2, '2026-10-03 08:30:31'),
  (2, 133, 'keluar', -0.0060, 3.2492, 3.2432, 'Pemakaian pesanan RI-20261003-345', 2, '2026-10-03 08:30:31'),
  (7, 133, 'keluar', -2.0000, 108.0000, 106.0000, 'Pemakaian pesanan RI-20261003-345', 2, '2026-10-03 08:30:31'),
  (3, 133, 'keluar', -2.0000, 122.0000, 120.0000, 'Pemakaian pesanan RI-20261003-345', 2, '2026-10-03 08:30:31'),
  (6, 134, 'keluar', -2.5000, 6.0000, 3.5000, 'Pemakaian pesanan RI-20261003-344', 2, '2026-10-03 13:48:30'),
  (5, 134, 'keluar', -0.5000, 4.0000, 3.5000, 'Pemakaian pesanan RI-20261003-344', 2, '2026-10-03 13:48:30'),
  (1, 135, 'keluar', -0.0720, 21.1520, 21.0800, 'Pemakaian pesanan RI-20261003-001', 2, '2026-10-03 17:54:41'),
  (2, 135, 'keluar', -0.0108, 3.2432, 3.2324, 'Pemakaian pesanan RI-20261003-001', 2, '2026-10-03 17:54:41'),
  (7, 135, 'keluar', -1.0000, 106.0000, 105.0000, 'Pemakaian pesanan RI-20261003-001', 2, '2026-10-03 17:54:41'),
  (3, 135, 'keluar', -1.0000, 120.0000, 119.0000, 'Pemakaian pesanan RI-20261003-001', 2, '2026-10-03 17:54:41'),
  (5, NULL, 'masuk', 3.0000, 3.5000, 6.5000, 'Pembelian dari supplier', 3, '2026-10-05 07:30:00'),
  (1, 137, 'keluar', -0.0800, 21.0800, 21.0000, 'Pemakaian pesanan RI-20261005-890', 2, '2026-10-05 13:32:49'),
  (2, 137, 'keluar', -0.0120, 3.2324, 3.2204, 'Pemakaian pesanan RI-20261005-890', 2, '2026-10-05 13:32:49'),
  (7, 137, 'keluar', -2.0000, 105.0000, 103.0000, 'Pemakaian pesanan RI-20261005-890', 2, '2026-10-05 13:32:49'),
  (3, 137, 'keluar', -2.0000, 119.0000, 117.0000, 'Pemakaian pesanan RI-20261005-890', 2, '2026-10-05 13:32:49'),
  (4, 136, 'keluar', -0.0160, 8.8720, 8.8560, 'Pemakaian pesanan RI-20261005-090', 2, '2026-10-05 17:41:32'),
  (1, 138, 'keluar', -0.0800, 21.0000, 20.9200, 'Pemakaian pesanan RI-20261006-295', 2, '2026-10-06 10:20:00'),
  (1, 139, 'keluar', -0.1000, 20.9200, 20.8200, 'Pemakaian pesanan RI-20261006-929', 2, '2026-10-06 15:47:50'),
  (7, 139, 'keluar', -1.0000, 103.0000, 102.0000, 'Pemakaian pesanan RI-20261006-929', 2, '2026-10-06 15:47:50'),
  (3, 139, 'keluar', -1.0000, 117.0000, 116.0000, 'Pemakaian pesanan RI-20261006-929', 2, '2026-10-06 15:47:50'),
  (1, 140, 'keluar', -0.3200, 20.8200, 20.5000, 'Pemakaian pesanan RI-20261006-174', 2, '2026-10-06 18:55:44'),
  (5, 140, 'keluar', -0.1600, 6.5000, 6.3400, 'Pemakaian pesanan RI-20261006-174', 2, '2026-10-06 18:55:44'),
  (7, 140, 'keluar', -2.0000, 102.0000, 100.0000, 'Pemakaian pesanan RI-20261006-174', 2, '2026-10-06 18:55:44'),
  (3, 140, 'keluar', -2.0000, 116.0000, 114.0000, 'Pemakaian pesanan RI-20261006-174', 2, '2026-10-06 18:55:44'),
  (4, 142, 'keluar', -0.1200, 8.8560, 8.7360, 'Pemakaian pesanan RI-20261007-832', 2, '2026-10-07 18:45:08'),
  (1, 143, 'keluar', -0.4000, 20.5000, 20.1000, 'Pemakaian pesanan RI-20261008-221', 2, '2026-10-08 19:21:17'),
  (1, 145, 'keluar', -0.3200, 20.1000, 19.7800, 'Pemakaian pesanan RI-20261009-764', 2, '2026-10-09 17:32:10'),
  (2, 145, 'keluar', -0.0480, 3.2204, 3.1724, 'Pemakaian pesanan RI-20261009-764', 2, '2026-10-09 17:32:10'),
  (1, 144, 'keluar', -0.2000, 19.7800, 19.5800, 'Pemakaian pesanan RI-20261009-574', 2, '2026-10-09 19:39:36'),
  (2, 144, 'keluar', -0.0300, 3.1724, 3.1424, 'Pemakaian pesanan RI-20261009-574', 2, '2026-10-09 19:39:36'),
  (3, 144, 'keluar', -2.0000, 114.0000, 112.0000, 'Pemakaian pesanan RI-20261009-574', 2, '2026-10-09 19:39:36'),
  (9, 144, 'keluar', -0.0500, 5.6000, 5.5500, 'Pemakaian pesanan RI-20261009-574', 2, '2026-10-09 19:39:36'),
  (1, 146, 'keluar', -0.0600, 19.5800, 19.5200, 'Pemakaian pesanan RI-20261010-799', 2, '2026-10-10 14:55:04'),
  (2, 146, 'keluar', -0.0090, 3.1424, 3.1334, 'Pemakaian pesanan RI-20261010-799', 2, '2026-10-10 14:55:04'),
  (6, 148, 'keluar', -1.0000, 3.5000, 2.5000, 'Pemakaian pesanan RI-20261011-509', 2, '2026-10-11 15:30:30'),
  (5, 148, 'keluar', -0.2000, 6.3400, 6.1400, 'Pemakaian pesanan RI-20261011-509', 2, '2026-10-11 15:30:30'),
  (6, NULL, 'masuk', 3.0000, 2.5000, 5.5000, 'Pembelian dari supplier', 3, '2026-10-12 07:30:00'),
  (1, 150, 'keluar', -0.2000, 19.5200, 19.3200, 'Pemakaian pesanan RI-20261012-971', 2, '2026-10-12 08:51:47'),
  (2, 150, 'keluar', -0.0300, 3.1334, 3.1034, 'Pemakaian pesanan RI-20261012-971', 2, '2026-10-12 08:51:47'),
  (8, 150, 'keluar', -1.0000, 19.0000, 18.0000, 'Pemakaian pesanan RI-20261012-971', 2, '2026-10-12 08:51:47'),
  (6, 149, 'keluar', -1.0000, 5.5000, 4.5000, 'Pemakaian pesanan RI-20261012-669', 2, '2026-10-12 12:33:04'),
  (5, 149, 'keluar', -0.2000, 6.1400, 5.9400, 'Pemakaian pesanan RI-20261012-669', 2, '2026-10-12 12:33:04'),
  (1, 151, 'keluar', -0.0480, 19.3200, 19.2720, 'Pemakaian pesanan RI-20261013-583', 2, '2026-10-13 10:24:37'),
  (2, 151, 'keluar', -0.0072, 3.1034, 3.0962, 'Pemakaian pesanan RI-20261013-583', 2, '2026-10-13 10:24:37'),
  (7, 151, 'keluar', -1.0000, 100.0000, 99.0000, 'Pemakaian pesanan RI-20261013-583', 2, '2026-10-13 10:24:37'),
  (3, 151, 'keluar', -1.0000, 112.0000, 111.0000, 'Pemakaian pesanan RI-20261013-583', 2, '2026-10-13 10:24:37'),
  (1, 152, 'keluar', -0.0800, 19.2720, 19.1920, 'Pemakaian pesanan RI-20261013-097', 2, '2026-10-13 10:41:39'),
  (2, 152, 'keluar', -0.0120, 3.0962, 3.0842, 'Pemakaian pesanan RI-20261013-097', 2, '2026-10-13 10:41:39'),
  (4, 153, 'keluar', -0.0720, 8.7360, 8.6640, 'Pemakaian pesanan RI-20261013-105', 2, '2026-10-13 16:17:18'),
  (3, 153, 'keluar', -6.0000, 111.0000, 105.0000, 'Pemakaian pesanan RI-20261013-105', 2, '2026-10-13 16:17:18'),
  (9, 153, 'keluar', -0.1500, 5.5500, 5.4000, 'Pemakaian pesanan RI-20261013-105', 2, '2026-10-13 16:17:18'),
  (4, 154, 'keluar', -0.0480, 8.6640, 8.6160, 'Pemakaian pesanan RI-20261014-987', 2, '2026-10-14 12:14:02'),
  (1, 155, 'keluar', -0.0240, 19.1920, 19.1680, 'Pemakaian pesanan RI-20261014-817', 2, '2026-10-14 13:19:57'),
  (7, 155, 'keluar', -1.0000, 99.0000, 98.0000, 'Pemakaian pesanan RI-20261014-817', 2, '2026-10-14 13:19:57'),
  (3, 155, 'keluar', -1.0000, 105.0000, 104.0000, 'Pemakaian pesanan RI-20261014-817', 2, '2026-10-14 13:19:57'),
  (1, 156, 'keluar', -0.2000, 19.1680, 18.9680, 'Pemakaian pesanan RI-20261014-815', 2, '2026-10-14 14:07:50'),
  (5, 156, 'keluar', -0.1000, 5.9400, 5.8400, 'Pemakaian pesanan RI-20261014-815', 2, '2026-10-14 14:07:50'),
  (1, 157, 'keluar', -0.0720, 18.9680, 18.8960, 'Pemakaian pesanan RI-20261015-063', 2, '2026-10-15 16:54:34'),
  (7, 157, 'keluar', -1.0000, 98.0000, 97.0000, 'Pemakaian pesanan RI-20261015-063', 2, '2026-10-15 16:54:34'),
  (3, 157, 'keluar', -1.0000, 104.0000, 103.0000, 'Pemakaian pesanan RI-20261015-063', 2, '2026-10-15 16:54:34'),
  (1, 158, 'keluar', -0.0400, 18.8960, 18.8560, 'Pemakaian pesanan RI-20261015-860', 2, '2026-10-15 17:26:04'),
  (2, 158, 'keluar', -0.0060, 3.0842, 3.0782, 'Pemakaian pesanan RI-20261015-860', 2, '2026-10-15 17:26:04'),
  (1, 159, 'keluar', -0.2400, 18.8560, 18.6160, 'Pemakaian pesanan RI-20261016-753', 2, '2026-10-16 12:51:50'),
  (2, 159, 'keluar', -0.0360, 3.0782, 3.0422, 'Pemakaian pesanan RI-20261016-753', 2, '2026-10-16 12:51:50'),
  (1, 161, 'keluar', -0.0080, 18.6160, 18.6080, 'Pemakaian pesanan RI-20261017-129', 2, '2026-10-17 09:04:35'),
  (2, 161, 'keluar', -0.0012, 3.0422, 3.0410, 'Pemakaian pesanan RI-20261017-129', 2, '2026-10-17 09:04:35'),
  (4, 160, 'keluar', -0.0080, 8.6160, 8.6080, 'Pemakaian pesanan RI-20261017-456', 2, '2026-10-17 15:20:05'),
  (2, 160, 'keluar', -0.0012, 3.0410, 3.0398, 'Pemakaian pesanan RI-20261017-456', 2, '2026-10-17 15:20:05'),
  (7, 160, 'keluar', -1.0000, 97.0000, 96.0000, 'Pemakaian pesanan RI-20261017-456', 2, '2026-10-17 15:20:05'),
  (3, 160, 'keluar', -1.0000, 103.0000, 102.0000, 'Pemakaian pesanan RI-20261017-456', 2, '2026-10-17 15:20:05'),
  (1, 162, 'keluar', -0.0300, 18.6080, 18.5780, 'Pemakaian pesanan RI-20261019-638', 2, '2026-10-19 16:03:42'),
  (1, 164, 'keluar', -0.0800, 18.5780, 18.4980, 'Pemakaian pesanan RI-20261020-888', 2, '2026-10-20 13:23:54'),
  (2, 164, 'keluar', -0.0120, 3.0398, 3.0278, 'Pemakaian pesanan RI-20261020-888', 2, '2026-10-20 13:23:54'),
  (3, 164, 'keluar', -2.0000, 102.0000, 100.0000, 'Pemakaian pesanan RI-20261020-888', 2, '2026-10-20 13:23:54'),
  (9, 164, 'keluar', -0.0500, 5.4000, 5.3500, 'Pemakaian pesanan RI-20261020-888', 2, '2026-10-20 13:23:54'),
  (1, 167, 'keluar', -0.2000, 18.4980, 18.2980, 'Pemakaian pesanan RI-20261021-858', 2, '2026-10-21 10:14:04'),
  (7, 167, 'keluar', -1.0000, 96.0000, 95.0000, 'Pemakaian pesanan RI-20261021-858', 2, '2026-10-21 10:14:04'),
  (3, 167, 'keluar', -1.0000, 100.0000, 99.0000, 'Pemakaian pesanan RI-20261021-858', 2, '2026-10-21 10:14:04'),
  (1, 165, 'keluar', -0.0600, 18.2980, 18.2380, 'Pemakaian pesanan RI-20261021-896', 2, '2026-10-21 14:26:33'),
  (3, 165, 'keluar', -2.0000, 99.0000, 97.0000, 'Pemakaian pesanan RI-20261021-896', 2, '2026-10-21 14:26:33'),
  (9, 165, 'keluar', -0.0500, 5.3500, 5.3000, 'Pemakaian pesanan RI-20261021-896', 2, '2026-10-21 14:26:33'),
  (4, 166, 'keluar', -0.0080, 8.6080, 8.6000, 'Pemakaian pesanan RI-20261021-993', 2, '2026-10-21 18:20:58'),
  (2, 166, 'keluar', -0.0012, 3.0278, 3.0266, 'Pemakaian pesanan RI-20261021-993', 2, '2026-10-21 18:20:58'),
  (6, 168, 'keluar', -0.7500, 4.5000, 3.7500, 'Pemakaian pesanan RI-20261022-466', 2, '2026-10-22 18:54:04'),
  (5, 168, 'keluar', -0.1500, 5.8400, 5.6900, 'Pemakaian pesanan RI-20261022-466', 2, '2026-10-22 18:54:04'),
  (1, 169, 'keluar', -0.0480, 18.2380, 18.1900, 'Pemakaian pesanan RI-20261023-219', 2, '2026-10-23 09:08:22'),
  (2, 169, 'keluar', -0.0072, 3.0266, 3.0194, 'Pemakaian pesanan RI-20261023-219', 2, '2026-10-23 09:08:22'),
  (7, 169, 'keluar', -1.0000, 95.0000, 94.0000, 'Pemakaian pesanan RI-20261023-219', 2, '2026-10-23 09:08:22'),
  (3, 169, 'keluar', -1.0000, 97.0000, 96.0000, 'Pemakaian pesanan RI-20261023-219', 2, '2026-10-23 09:08:22'),
  (1, 171, 'keluar', -0.1800, 18.1900, 18.0100, 'Pemakaian pesanan RI-20261024-390', 2, '2026-10-24 11:05:24'),
  (2, 171, 'keluar', -0.0270, 3.0194, 2.9924, 'Pemakaian pesanan RI-20261024-390', 2, '2026-10-24 11:05:24'),
  (1, 170, 'keluar', -0.2000, 18.0100, 17.8100, 'Pemakaian pesanan RI-20261024-493', 2, '2026-10-24 15:34:13'),
  (8, 170, 'keluar', -2.0000, 18.0000, 16.0000, 'Pemakaian pesanan RI-20261024-493', 2, '2026-10-24 15:34:13'),
  (1, 173, 'keluar', -0.2000, 17.8100, 17.6100, 'Pemakaian pesanan RI-20261025-531', 2, '2026-10-25 13:38:03'),
  (5, 173, 'keluar', -0.1000, 5.6900, 5.5900, 'Pemakaian pesanan RI-20261025-531', 2, '2026-10-25 13:38:03'),
  (4, 174, 'keluar', -0.1000, 8.6000, 8.5000, 'Pemakaian pesanan RI-20261026-720', 2, '2026-10-26 13:26:37'),
  (2, 174, 'keluar', -0.0150, 2.9924, 2.9774, 'Pemakaian pesanan RI-20261026-720', 2, '2026-10-26 13:26:37'),
  (1, 176, 'keluar', -0.1600, 17.6100, 17.4500, 'Pemakaian pesanan RI-20261027-010', 2, '2026-10-27 09:11:21'),
  (2, 176, 'keluar', -0.0240, 2.9774, 2.9534, 'Pemakaian pesanan RI-20261027-010', 2, '2026-10-27 09:11:21'),
  (7, 176, 'keluar', -1.0000, 94.0000, 93.0000, 'Pemakaian pesanan RI-20261027-010', 2, '2026-10-27 09:11:21'),
  (3, 176, 'keluar', -1.0000, 96.0000, 95.0000, 'Pemakaian pesanan RI-20261027-010', 2, '2026-10-27 09:11:21'),
  (1, 175, 'keluar', -0.2400, 17.4500, 17.2100, 'Pemakaian pesanan RI-20261027-014', 2, '2026-10-27 19:15:00'),
  (1, 177, 'keluar', -0.1000, 17.2100, 17.1100, 'Pemakaian pesanan RI-20261028-607', 2, '2026-10-28 09:00:08'),
  (2, 177, 'keluar', -0.0150, 2.9534, 2.9384, 'Pemakaian pesanan RI-20261028-607', 2, '2026-10-28 09:00:08'),
  (1, 4, 'keluar', -0.0040, 17.1100, 17.1060, 'Pemakaian pesanan data uji tim', 2, '2026-10-29 02:52:36'),
  (2, 4, 'keluar', -0.0006, 2.9384, 2.9378, 'Pemakaian pesanan data uji tim', 2, '2026-10-29 02:52:36'),
  (1, 5, 'keluar', -0.0020, 17.1060, 17.1040, 'Pemakaian pesanan data uji tim', 2, '2026-10-29 04:01:13'),
  (2, 5, 'keluar', -0.0003, 2.9378, 2.9375, 'Pemakaian pesanan data uji tim', 2, '2026-10-29 04:01:13'),
  (4, 178, 'keluar', -0.2400, 8.5000, 8.2600, 'Pemakaian pesanan RI-20261029-293', 2, '2026-10-29 19:39:02'),
  (2, 178, 'keluar', -0.0360, 2.9375, 2.9015, 'Pemakaian pesanan RI-20261029-293', 2, '2026-10-29 19:39:02'),
  (3, 178, 'keluar', -2.0000, 95.0000, 93.0000, 'Pemakaian pesanan RI-20261029-293', 2, '2026-10-29 19:39:02'),
  (9, 178, 'keluar', -0.0500, 5.3000, 5.2500, 'Pemakaian pesanan RI-20261029-293', 2, '2026-10-29 19:39:02'),
  (1, 180, 'keluar', -0.0800, 17.1040, 17.0240, 'Pemakaian pesanan RI-20261030-593', 2, '2026-10-30 17:07:56'),
  (1, 179, 'keluar', -0.3200, 17.0240, 16.7040, 'Pemakaian pesanan RI-20261030-693', 2, '2026-10-30 19:34:37'),
  (3, 179, 'keluar', -4.0000, 93.0000, 89.0000, 'Pemakaian pesanan RI-20261030-693', 2, '2026-10-30 19:34:37'),
  (9, 179, 'keluar', -0.1000, 5.2500, 5.1500, 'Pemakaian pesanan RI-20261030-693', 2, '2026-10-30 19:34:37'),
  (1, 182, 'keluar', -0.0600, 16.7040, 16.6440, 'Pemakaian pesanan RI-20261031-762', 2, '2026-10-31 15:33:21'),
  (2, 182, 'keluar', -0.0090, 2.9015, 2.8925, 'Pemakaian pesanan RI-20261031-762', 2, '2026-10-31 15:33:21'),
  (8, 182, 'keluar', -1.0000, 16.0000, 15.0000, 'Pemakaian pesanan RI-20261031-762', 2, '2026-10-31 15:33:21'),
  (1, 183, 'keluar', -0.0300, 16.6440, 16.6140, 'Pemakaian pesanan RI-20261101-892', 2, '2026-11-01 16:20:43'),
  (2, 183, 'keluar', -0.0045, 2.8925, 2.8880, 'Pemakaian pesanan RI-20261101-892', 2, '2026-11-01 16:20:43'),
  (1, 184, 'keluar', -0.0400, 16.6140, 16.5740, 'Pemakaian pesanan RI-20261102-750', 2, '2026-11-02 12:10:31'),
  (5, 184, 'keluar', -0.0200, 5.5900, 5.5700, 'Pemakaian pesanan RI-20261102-750', 2, '2026-11-02 12:10:31'),
  (1, 185, 'keluar', -0.2000, 16.5740, 16.3740, 'Pemakaian pesanan RI-20261103-420', 2, '2026-11-03 15:57:03'),
  (5, 185, 'keluar', -0.1000, 5.5700, 5.4700, 'Pemakaian pesanan RI-20261103-420', 2, '2026-11-03 15:57:03'),
  (4, 187, 'keluar', -0.1440, 8.2600, 8.1160, 'Pemakaian pesanan RI-20261104-887', 2, '2026-11-04 10:20:58'),
  (2, 187, 'keluar', -0.0216, 2.8880, 2.8664, 'Pemakaian pesanan RI-20261104-887', 2, '2026-11-04 10:20:58'),
  (1, 186, 'keluar', -0.1200, 16.3740, 16.2540, 'Pemakaian pesanan RI-20261104-632', 2, '2026-11-04 16:54:46'),
  (2, 186, 'keluar', -0.0180, 2.8664, 2.8484, 'Pemakaian pesanan RI-20261104-632', 2, '2026-11-04 16:54:46'),
  (4, 188, 'keluar', -0.1200, 8.1160, 7.9960, 'Pemakaian pesanan RI-20261105-077', 2, '2026-11-05 18:57:47'),
  (1, 189, 'keluar', -0.0080, 16.2540, 16.2460, 'Pemakaian pesanan RI-20261106-633', 2, '2026-11-06 15:48:54'),
  (2, 189, 'keluar', -0.0012, 2.8484, 2.8472, 'Pemakaian pesanan RI-20261106-633', 2, '2026-11-06 15:48:54'),
  (7, 189, 'keluar', -1.0000, 93.0000, 92.0000, 'Pemakaian pesanan RI-20261106-633', 2, '2026-11-06 15:48:54'),
  (3, 189, 'keluar', -1.0000, 89.0000, 88.0000, 'Pemakaian pesanan RI-20261106-633', 2, '2026-11-06 15:48:54'),
  (4, 190, 'keluar', -0.0720, 7.9960, 7.9240, 'Pemakaian pesanan RI-20261107-164', 2, '2026-11-07 10:19:53'),
  (2, 190, 'keluar', -0.0108, 2.8472, 2.8364, 'Pemakaian pesanan RI-20261107-164', 2, '2026-11-07 10:19:53'),
  (3, 190, 'keluar', -2.0000, 88.0000, 86.0000, 'Pemakaian pesanan RI-20261107-164', 2, '2026-11-07 10:19:53'),
  (9, 190, 'keluar', -0.0500, 5.1500, 5.1000, 'Pemakaian pesanan RI-20261107-164', 2, '2026-11-07 10:19:53'),
  (1, 191, 'keluar', -0.1600, 16.2460, 16.0860, 'Pemakaian pesanan RI-20261107-162', 2, '2026-11-07 15:06:47'),
  (2, 191, 'keluar', -0.0240, 2.8364, 2.8124, 'Pemakaian pesanan RI-20261107-162', 2, '2026-11-07 15:06:47'),
  (1, 7, 'keluar', -0.0040, 16.0860, 16.0820, 'Pemakaian pesanan data uji tim', 2, '2026-11-08 17:20:25'),
  (2, 7, 'keluar', -0.0006, 2.8124, 2.8118, 'Pemakaian pesanan data uji tim', 2, '2026-11-08 17:20:25'),
  (8, NULL, 'koreksi', -15.0000, 15.0000, 0.0000, 'Stock opname: sisa hard cover rusak/terlipat', 3, '2026-11-08 19:00:00'),
  (1, 194, 'keluar', -0.2000, 16.0820, 15.8820, 'Pemakaian pesanan RI-20261109-206', 2, '2026-11-09 11:08:42'),
  (1, 192, 'keluar', -0.0200, 15.8820, 15.8620, 'Pemakaian pesanan RI-20261109-835', 2, '2026-11-09 14:25:42'),
  (5, 192, 'keluar', -0.0100, 5.4700, 5.4600, 'Pemakaian pesanan RI-20261109-835', 2, '2026-11-09 14:25:42');

-- Stok final (trigger fn_update_status_bahan menghitung status tersedia/kritis/habis)
UPDATE bahan_baku SET stok = 15.8620 WHERE id = 1;
UPDATE bahan_baku SET stok = 2.8118 WHERE id = 2;
UPDATE bahan_baku SET stok = 86.0000 WHERE id = 3;
UPDATE bahan_baku SET stok = 7.9240 WHERE id = 4;
UPDATE bahan_baku SET stok = 5.4600 WHERE id = 5;
UPDATE bahan_baku SET stok = 3.7500 WHERE id = 6;
UPDATE bahan_baku SET stok = 92.0000 WHERE id = 7;
UPDATE bahan_baku SET stok = 0.0000 WHERE id = 8;
UPDATE bahan_baku SET stok = 5.1000 WHERE id = 9;

-- Notifikasi aktivitas 4 hari terakhir
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (188, 'Pesanan Dibuat', 'Pesanan RI-20261105-077 berhasil dibuat dan menunggu verifikasi.', '2026-11-05 18:04:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 7, true FROM n UNION ALL SELECT id, 2, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (188, 'Perubahan Status Pesanan', 'Pesanan RI-20261105-077 berubah status dari Pending menjadi Diverifikasi', '2026-11-05 18:43:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 7, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (188, 'Perubahan Status Pesanan', 'Pesanan RI-20261105-077 berubah status dari Diverifikasi menjadi Diproses', '2026-11-05 18:57:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 7, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (188, 'Perubahan Status Pesanan', 'Pesanan RI-20261105-077 berubah status dari Diproses menjadi Selesai', '2026-11-05 20:24:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 7, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (188, 'Perubahan Status Pesanan', 'Pesanan RI-20261105-077 berubah status dari Selesai menjadi Diambil', '2026-11-06 09:59:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 7, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (189, 'Pesanan Dibuat', 'Pesanan RI-20261106-633 berhasil dibuat dan menunggu verifikasi.', '2026-11-06 14:51:54') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, true FROM n UNION ALL SELECT id, 2, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (189, 'Perubahan Status Pesanan', 'Pesanan RI-20261106-633 berubah status dari Pending menjadi Diverifikasi', '2026-11-06 15:24:54') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (189, 'Perubahan Status Pesanan', 'Pesanan RI-20261106-633 berubah status dari Diverifikasi menjadi Diproses', '2026-11-06 15:48:54') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (189, 'Perubahan Status Pesanan', 'Pesanan RI-20261106-633 berubah status dari Diproses menjadi Selesai', '2026-11-06 17:08:54') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (189, 'Perubahan Status Pesanan', 'Pesanan RI-20261106-633 berubah status dari Selesai menjadi Diambil', '2026-11-07 08:54:54') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (190, 'Pesanan Dibuat', 'Pesanan RI-20261107-164 berhasil dibuat dan menunggu verifikasi.', '2026-11-07 09:48:53') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 1, true FROM n UNION ALL SELECT id, 2, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (190, 'Perubahan Status Pesanan', 'Pesanan RI-20261107-164 berubah status dari Pending menjadi Diverifikasi', '2026-11-07 10:04:53') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 1, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (190, 'Perubahan Status Pesanan', 'Pesanan RI-20261107-164 berubah status dari Diverifikasi menjadi Diproses', '2026-11-07 10:19:53') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 1, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (190, 'Perubahan Status Pesanan', 'Pesanan RI-20261107-164 berubah status dari Diproses menjadi Selesai', '2026-11-07 12:19:53') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 1, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (190, 'Perubahan Status Pesanan', 'Pesanan RI-20261107-164 berubah status dari Selesai menjadi Diambil', '2026-11-07 23:19:53') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 1, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (191, 'Pesanan Dibuat', 'Pesanan RI-20261107-162 berhasil dibuat dan menunggu verifikasi.', '2026-11-07 14:34:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 6, true FROM n UNION ALL SELECT id, 2, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (191, 'Perubahan Status Pesanan', 'Pesanan RI-20261107-162 berubah status dari Pending menjadi Diverifikasi', '2026-11-07 14:48:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 6, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (191, 'Perubahan Status Pesanan', 'Pesanan RI-20261107-162 berubah status dari Diverifikasi menjadi Diproses', '2026-11-07 15:06:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 6, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (191, 'Perubahan Status Pesanan', 'Pesanan RI-20261107-162 berubah status dari Diproses menjadi Selesai', '2026-11-07 17:29:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 6, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (191, 'Perubahan Status Pesanan', 'Pesanan RI-20261107-162 berubah status dari Selesai menjadi Diambil', '2026-11-08 01:29:47') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 6, true FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (192, 'Pesanan Dibuat', 'Pesanan RI-20261109-835 berhasil dibuat dan menunggu verifikasi.', '2026-11-09 13:48:42') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 8, false FROM n UNION ALL SELECT id, 2, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (192, 'Perubahan Status Pesanan', 'Pesanan RI-20261109-835 berubah status dari Pending menjadi Diverifikasi', '2026-11-09 14:21:42') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 8, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (192, 'Perubahan Status Pesanan', 'Pesanan RI-20261109-835 berubah status dari Diverifikasi menjadi Diproses', '2026-11-09 14:25:42') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 8, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (193, 'Pesanan Dibuat', 'Pesanan RI-20261109-402 berhasil dibuat dan menunggu verifikasi.', '2026-11-09 14:53:35') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, false FROM n UNION ALL SELECT id, 2, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (193, 'Perubahan Status Pesanan', 'Pesanan RI-20261109-402 berubah status dari Pending menjadi Diverifikasi', '2026-11-09 15:31:35') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (194, 'Pesanan Dibuat', 'Pesanan RI-20261109-206 berhasil dibuat dan menunggu verifikasi.', '2026-11-09 10:15:42') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, false FROM n UNION ALL SELECT id, 2, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (194, 'Perubahan Status Pesanan', 'Pesanan RI-20261109-206 berubah status dari Pending menjadi Diverifikasi', '2026-11-09 10:50:42') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (194, 'Perubahan Status Pesanan', 'Pesanan RI-20261109-206 berubah status dari Diverifikasi menjadi Diproses', '2026-11-09 11:08:42') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 10, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (195, 'Pesanan Dibuat', 'Pesanan RI-20261109-428 berhasil dibuat dan menunggu verifikasi.', '2026-11-09 10:40:58') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 4, false FROM n UNION ALL SELECT id, 2, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (196, 'Pesanan Dibuat', 'Pesanan RI-20261109-049 berhasil dibuat dan menunggu verifikasi.', '2026-11-09 14:59:55') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 8, false FROM n UNION ALL SELECT id, 2, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (196, 'Perubahan Status Pesanan', 'Pesanan RI-20261109-049 berubah status dari Pending menjadi Diverifikasi', '2026-11-09 15:04:55') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 8, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (197, 'Pesanan Dibuat', 'Pesanan RI-20261109-777 berhasil dibuat dan menunggu verifikasi.', '2026-11-09 09:12:05') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 6, false FROM n UNION ALL SELECT id, 2, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (197, 'Perubahan Status Pesanan', 'Pesanan RI-20261109-777 berubah status dari Pending menjadi Diverifikasi', '2026-11-09 09:34:05') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 6, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (197, 'Perubahan Status Pesanan', 'Pesanan RI-20261109-777 berubah status dari Diverifikasi menjadi Tertunda - Stok Tidak Tersedia. Stok Hard Cover A4 tidak mencukupi', '2026-11-09 09:43:05') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 6, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (NULL, 'Stok Kritis', 'Tinta Hitam Epson 003 tersisa 2.8118 botol (reorder point 3).', '2026-11-09 07:30:00') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 2, false FROM n UNION ALL SELECT id, 3, false FROM n;
WITH n AS (INSERT INTO notifikasi (pesanan_id, judul, pesan, created_at) VALUES (NULL, 'Stok Habis', 'Hard Cover A4 tersisa 0 pcs (reorder point 5).', '2026-11-09 07:30:00') RETURNING id) INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) SELECT id, 2, false FROM n UNION ALL SELECT id, 3, false FROM n;

ALTER TABLE pesanan ENABLE TRIGGER trg_log_status_pesanan;
ALTER TABLE pesanan ENABLE TRIGGER trg_notifikasi_status_pesanan;
ALTER TABLE pesanan ENABLE TRIGGER trg_pesanan_updated_at;
ALTER TABLE bahan_baku ENABLE TRIGGER trg_log_mutasi_stok;
ALTER TABLE bahan_baku ENABLE TRIGGER trg_peringatan_stok_kritis;

-- Sinkronkan sequence identity
SELECT setval(pg_get_serial_sequence('users', 'id'), (SELECT MAX(id) FROM users));
SELECT setval(pg_get_serial_sequence('layanan', 'id'), (SELECT MAX(id) FROM layanan));
SELECT setval(pg_get_serial_sequence('bahan_baku', 'id'), (SELECT MAX(id) FROM bahan_baku));
SELECT setval(pg_get_serial_sequence('bahan_layanan', 'id'), (SELECT MAX(id) FROM bahan_layanan));
SELECT setval(pg_get_serial_sequence('pesanan', 'id'), (SELECT MAX(id) FROM pesanan));
SELECT setval(pg_get_serial_sequence('detail_pesanan', 'id'), (SELECT MAX(id) FROM detail_pesanan));
SELECT setval(pg_get_serial_sequence('notifikasi', 'id'), (SELECT MAX(id) FROM notifikasi));
SELECT setval(pg_get_serial_sequence('notifikasi_users', 'id'), (SELECT MAX(id) FROM notifikasi_users));
SELECT setval(pg_get_serial_sequence('riwayat_status', 'id'), (SELECT MAX(id) FROM riwayat_status));
SELECT setval(pg_get_serial_sequence('riwayat_stok', 'id'), (SELECT MAX(id) FROM riwayat_stok));

COMMIT;
