-- =========================================================
-- 1. TABEL USERS
-- Role: pelanggan, admin, owner
-- =========================================================

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'pelanggan'
        CHECK (role IN ('pelanggan', 'admin', 'owner')),
    no_telepon VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- 2. TABEL LAYANAN
-- =========================================================

CREATE TABLE IF NOT EXISTS layanan (
    id SERIAL PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    harga NUMERIC(10,2) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    deskripsi VARCHAR(255) NOT NULL
);


-- =========================================================
-- 3. TABEL BAHAN_BAKU
-- Dilengkapi Reorder Point
-- =========================================================

CREATE TABLE IF NOT EXISTS bahan_baku (
    id SERIAL PRIMARY KEY,
    nama_bahan VARCHAR(100) NOT NULL,
    stok NUMERIC(10,2) NOT NULL DEFAULT 0,
    satuan VARCHAR(20) NOT NULL,
    reorder_point NUMERIC(10,2) NOT NULL DEFAULT 10,
    status VARCHAR(20) NOT NULL DEFAULT 'Aman',
    harga_satuan NUMERIC(10,2) NOT NULL DEFAULT 0
);


-- =========================================================
-- 4. TABEL BAHAN_LAYANAN
-- Bill of Materials (BOM)
-- =========================================================

CREATE TABLE IF NOT EXISTS bahan_layanan (
    id SERIAL PRIMARY KEY,
    layanan_id INTEGER NOT NULL,
    bahan_baku_id INTEGER NOT NULL,
    jumlah_kebutuhan NUMERIC(10,4) NOT NULL DEFAULT 1,

    CONSTRAINT uq_bahan_layanan
        UNIQUE (layanan_id, bahan_baku_id),

    CONSTRAINT fk_bahan_layanan_layanan
        FOREIGN KEY (layanan_id)
        REFERENCES layanan(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_bahan_layanan_bahan_baku
        FOREIGN KEY (bahan_baku_id)
        REFERENCES bahan_baku(id)
        ON DELETE RESTRICT
);


-- =========================================================
-- 5. TABEL PESANAN
-- =========================================================

CREATE TABLE IF NOT EXISTS pesanan (
    id SERIAL PRIMARY KEY,
    kode_pesanan VARCHAR(50) UNIQUE NOT NULL,
    user_id INTEGER NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'Pending',
    total_harga NUMERIC(12,2) NOT NULL DEFAULT 0,
    catatan VARCHAR(255) NOT NULL,
    stok_dipotong BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pesanan_users
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
);

-- =========================================================
-- 6. TABEL DETAIL_PESANAN
-- File dokumen dan subtotal berada di tabel ini
-- =========================================================

CREATE TABLE IF NOT EXISTS detail_pesanan (
    id SERIAL PRIMARY KEY,
    pesanan_id INTEGER NOT NULL,
    layanan_id INTEGER NOT NULL,
    jumlah INTEGER NOT NULL DEFAULT 1,
    subtotal NUMERIC(12,2) NOT NULL DEFAULT 0,
    file_dokumen VARCHAR(255),

    CONSTRAINT fk_detail_pesanan_pesanan
        FOREIGN KEY (pesanan_id)
        REFERENCES pesanan(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_detail_pesanan_layanan
        FOREIGN KEY (layanan_id)
        REFERENCES layanan(id)
        ON DELETE RESTRICT
);


-- =========================================================
-- 7. TABEL NOTIFIKASI
-- Informasi notifikasi terkait pesanan
-- =========================================================

CREATE TABLE IF NOT EXISTS notifikasi (
    id SERIAL PRIMARY KEY,
    pesanan_id INTEGER,
    judul VARCHAR(100) NOT NULL,
    pesan VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifikasi_pesanan
        FOREIGN KEY (pesanan_id)
        REFERENCES pesanan(id)
        ON DELETE CASCADE
);


-- =========================================================
-- 8. TABEL NOTIFIKASI_USERS
-- Tabel penghubung notifikasi dan pengguna
-- =========================================================

CREATE TABLE IF NOT EXISTS notifikasi_users (
    id SERIAL PRIMARY KEY,
    notifikasi_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    is_read BIT(1) NOT NULL DEFAULT B'0',

    CONSTRAINT fk_notifikasi_users_notifikasi
        FOREIGN KEY (notifikasi_id)
        REFERENCES notifikasi(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notifikasi_users_users
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT uq_notifikasi_users
        UNIQUE (notifikasi_id, user_id)
);


-- =========================================================
-- 9. TABEL AUTH_TOKENS
-- Menyimpan hash token autentikasi pengguna
-- =========================================================

CREATE TABLE IF NOT EXISTS auth_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_auth_tokens_users
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);