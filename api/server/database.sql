-- ============================================================
-- SiMoSoBa v2 — Database Schema
-- Import file ini via phpMyAdmin: Import > pilih file ini > Go
-- ============================================================

CREATE DATABASE IF NOT EXISTS simosoba2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simosoba2;

-- ============================================================
-- TABEL: users
-- ============================================================
-- role ENUM berisi 4 pilihan:
--   'super_admin'  → kelola semua user & lihat semua data
--   'admin_staff'  → lihat laporan + tanggal kadaluarsa divisinya
--   'staff'        → hanya input & output stok divisinya
--   'user'         → akses terbatas, hanya lihat dashboard read-only
-- is_active: 1=aktif boleh login, 0=diblokir tidak bisa login
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100)  NOT NULL,
    username    VARCHAR(50)   NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('super_admin','admin_staff','staff','user') NOT NULL DEFAULT 'user',
    divisi      VARCHAR(100)  DEFAULT NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    last_login  DATETIME     DEFAULT NULL,        -- dicatat otomatis setiap user berhasil login
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABEL: obat
-- ============================================================
-- stok_min   : batas minimum stok, jika stok < stok_min → status "Menipis"
-- exp_date   : tanggal kadaluarsa, trigger notif jika <= 30 hari lagi
-- divisi     : obat milik divisi mana (NULL = milik semua/super_admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS obat (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(150)  NOT NULL,
    kategori    VARCHAR(100)  NOT NULL,
    satuan      VARCHAR(50)   NOT NULL DEFAULT 'Tablet',
    stok        INT           NOT NULL DEFAULT 0,
    stok_min    INT           NOT NULL DEFAULT 10,
    exp_date    DATE          NOT NULL,
    divisi      VARCHAR(100)  DEFAULT NULL,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABEL: transaksi
-- ============================================================
-- tipe: 'masuk' = tambah stok, 'keluar' = kurangi stok
-- stok_sesudah: snapshot sisa stok setelah transaksi ini terjadi
-- ============================================================
CREATE TABLE IF NOT EXISTS transaksi (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    obat_id         INT          NOT NULL,
    user_id         INT          NOT NULL,
    tipe            ENUM('masuk','keluar') NOT NULL,
    jumlah          INT          NOT NULL,
    stok_sesudah    INT          NOT NULL,
    keterangan      TEXT         DEFAULT NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (obat_id) REFERENCES obat(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- DATA AWAL: 4 Role Demo
-- Semua password: "password123"
-- Hash dibuat dengan: password_hash('password123', PASSWORD_DEFAULT)
-- ============================================================
INSERT INTO users (nama, username, password, role, divisi) VALUES
('Super Administrator', 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NULL),
('Admin Staff Apotek A', 'adminstaff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin_staff', 'Apotek A'),
('Staff Apotek A',       'staff1',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff',       'Apotek A'),
('User Biasa',           'user1',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user',        'Apotek A');

-- ============================================================
-- DATA CONTOH: Obat
-- ============================================================
INSERT INTO obat (nama, kategori, satuan, stok, stok_min, exp_date, divisi) VALUES
('Paracetamol 500mg',  'Analgesik',      'Tablet', 150, 30, '2026-12-01', 'Apotek A'),
('Amoxicillin 500mg',  'Antibiotik',     'Kapsul',   5, 20, '2025-06-01', 'Apotek A'),
('Antasida Tablet',    'Antasida',       'Tablet',   0, 15, '2026-08-15', 'Apotek A'),
('Vitamin C 1000mg',   'Vitamin',        'Tablet', 200, 50, '2027-01-01', 'Apotek A'),
('Amlodipine 5mg',     'Antihipertensi', 'Tablet',  12, 25, DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'Apotek A'),
('Cetirizine 10mg',    'Antihistamin',   'Tablet',  80, 20, DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'Apotek A'),
('Omeprazole 20mg',    'Antasida',       'Kapsul',  35, 15, DATE_ADD(CURDATE(), INTERVAL 8 DAY),  'Apotek A');
