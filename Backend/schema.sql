-- =====================================================
-- 1. TABEL USERS
-- Role: pelanggan, admin, owner
-- =====================================================

CREATE TABLE IF NOT EXISTS users (
    id_user SERIAL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'pelanggan'
        CHECK (role IN ('pelanggan', 'admin', 'owner')),
    no_telepon VARCHAR(20)
);


-- =====================================================
-- 2. TABEL LAYANAN CETAK
-- =====================================================

CREATE TABLE IF NOT EXISTS layanan (
    id_layanan SERIAL PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    harga_dasar DECIMAL(12,2) NOT NULL,
    satuan VARCHAR(30) NOT NULL
);


-- =====================================================
-- 3. TABEL BAHAN BAKU
-- Dilengkapi Reorder Point
-- =====================================================

CREATE TABLE IF NOT EXISTS bahan_baku (
    id_bahan SERIAL PRIMARY KEY,
    nama_bahan VARCHAR(100) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    satuan VARCHAR(30) NOT NULL,
    reorder_point DECIMAL(10,2) NOT NULL DEFAULT 10,
    harga_bahan_baku DECIMAL(12,2) NOT NULL DEFAULT 0,
    status VARCHAR(30) NOT NULL DEFAULT 'Aman'
);


-- =====================================================
-- 4. TABEL PENGHUBUNG BAHAN_LAYANAN
-- Bill of Materials (BOM)
-- =====================================================

CREATE TABLE IF NOT EXISTS bahan_layanan (
    id_layanan INT NOT NULL,
    id_bahan INT NOT NULL,
    jumlah_kebutuhan INT NOT NULL DEFAULT 1,

    PRIMARY KEY (id_layanan, id_bahan),

    FOREIGN KEY (id_layanan)
        REFERENCES layanan(id_layanan)
        ON DELETE CASCADE,

    FOREIGN KEY (id_bahan)
        REFERENCES bahan_baku(id_bahan)
        ON DELETE RESTRICT
);


-- =====================================================
-- 5. TABEL PESANAN
-- Tracking status pesanan
-- =====================================================

CREATE TABLE IF NOT EXISTS pesanan (
    id_pesanan SERIAL PRIMARY KEY,
    kode_pesanan VARCHAR(30) UNIQUE NOT NULL,
    id_user INT NOT NULL,
    tgl_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_pesanan VARCHAR(50) NOT NULL DEFAULT 'Pending',
    total_harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    file_dokumen VARCHAR(255),
    catatan_pelanggan VARCHAR(255),

    FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE RESTRICT
);


-- =====================================================
-- 6. TABEL DETAIL PESANAN
-- =====================================================

CREATE TABLE IF NOT EXISTS detail_pesanan (
    id_detail SERIAL PRIMARY KEY,
    id_pesanan INT NOT NULL,
    id_layanan INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,

    FOREIGN KEY (id_pesanan)
        REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE,

    FOREIGN KEY (id_layanan)
        REFERENCES layanan(id_layanan)
        ON DELETE RESTRICT
);


-- =====================================================
-- 7. TABEL NOTIFIKASI
-- Update status pesanan dan peringatan stok kritis
-- =====================================================

CREATE TABLE IF NOT EXISTS notifikasi (
    id_notifikasi SERIAL PRIMARY KEY,
    id_user INT,
    id_pesanan INT,
    id_bahan INT,
    jenis_notifikasi VARCHAR(30) NOT NULL,
    judul VARCHAR(100) NOT NULL,
    pesan VARCHAR(255) NOT NULL,
    tgl_notifikasi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_dibaca VARCHAR(20) NOT NULL DEFAULT 'belum',

    FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE CASCADE,

    FOREIGN KEY (id_pesanan)
        REFERENCES pesanan(id_pesanan)
        ON DELETE CASCADE,

    FOREIGN KEY (id_bahan)
        REFERENCES bahan_baku(id_bahan)
        ON DELETE SET NULL
);