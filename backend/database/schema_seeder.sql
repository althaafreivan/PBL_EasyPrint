-- 1. Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telepon VARCHAR(20),
    role ENUM('pelanggan', 'admin', 'owner') DEFAULT 'pelanggan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel layanan
CREATE TABLE IF NOT EXISTS layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    deskripsi TEXT
);

-- 3. Tabel bahan_baku
CREATE TABLE IF NOT EXISTS bahan_baku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_bahan VARCHAR(100) NOT NULL,
    stok DECIMAL(10,2) NOT NULL DEFAULT 0,
    satuan VARCHAR(20) NOT NULL,
    reorder_point DECIMAL(10,2) DEFAULT 5,
    status ENUM('tersedia', 'kritis', 'habis') DEFAULT 'tersedia',
    harga_satuan DECIMAL(10,2) DEFAULT 0
);

-- 4. Tabel bahan_layanan
CREATE TABLE IF NOT EXISTS bahan_layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    layanan_id INT NOT NULL,
    bahan_baku_id INT NOT NULL,
    jumlah_kebutuhan DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (layanan_id) REFERENCES layanan(id) ON DELETE CASCADE,
    FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id) ON DELETE CASCADE
);

-- 5. Tabel pesanan
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Diverifikasi', 'Diproses', 'Tertunda', 'Selesai', 'Diambil') DEFAULT 'Pending',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Tabel detail_pesanan
CREATE TABLE IF NOT EXISTS detail_pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    layanan_id INT NOT NULL,
    jumlah INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    file_dokumen VARCHAR(255),
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (layanan_id) REFERENCES layanan(id) ON DELETE CASCADE
);

-- 7. Tabel notifikasi
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT,
    judul VARCHAR(100) NOT NULL,
    pesan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE SET NULL
);

-- 8. Tabel notifikasi_users
CREATE TABLE IF NOT EXISTS notifikasi_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notifikasi_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    FOREIGN KEY (notifikasi_id) REFERENCES notifikasi(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- INSERT DATA DUMMY (Sesuai Kontrak API Apidog)
INSERT INTO users (nama, email, password, no_telepon, role) VALUES
('Ahmad Fauzi', 'ahmad@example.com', 'hashed_password_123', '081234567890', 'pelanggan'),
('Operator Restu Ibu', 'operator@easyprint.com', 'hashed_password_admin', '089876543210', 'admin'),
('Pemilik Restu Ibu', 'owner@easyprint.com', 'hashed_password_owner', '081122334455', 'owner');

INSERT INTO layanan (nama_layanan, harga, satuan, deskripsi) VALUES
('Print HVS A4 Hitam Putih', 500.00, 'lembar', 'Cetak hitam putih kertas HVS A4 70gr'),
('Jilid Lakban', 5000.00, 'buku', 'Jilid lakban hitam dengan mika bening'),
('Cetak Brosur Glossy A4', 2500.00, 'lembar', 'Cetak full color kertas Art Paper 150gr');

INSERT INTO bahan_baku (nama_bahan, stok, satuan, reorder_point, status, harga_satuan) VALUES
('Kertas HVS A4 70gr', 15.00, 'rim', 5.00, 'tersedia', 48000.00),
('Tinta Hitam Epson 003', 2.00, 'botol', 3.00, 'kritis', 85000.00),
('Mika Bening A4', 50.00, 'lembar', 10.00, 'tersedia', 500.00);

INSERT INTO bahan_layanan (layanan_id, bahan_baku_id, jumlah_kebutuhan) VALUES
(1, 1, 0.002),
(2, 3, 2.000);

INSERT INTO pesanan (kode_pesanan, user_id, total_harga, status, catatan) VALUES
('ORD-20261002-001', 1, 15000.00, 'Pending', 'Tolong jilid lakban hitam, bagian depan mika bening');

INSERT INTO detail_pesanan (pesanan_id, layanan_id, jumlah, subtotal, file_dokumen) VALUES
(1, 1, 20, 10000.00, 'dokumen_skripsi.pdf'),
(1, 2, 1, 5000.00, NULL);

INSERT INTO notifikasi (pesanan_id, judul, pesan) VALUES
(1, 'Pesanan Dibuat', 'Pesanan ORD-20261002-001 berhasil dibuat dan menunggu verifikasi.');

INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read) VALUES
(1, 1, 0);