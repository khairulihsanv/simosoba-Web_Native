<?php
require_once __DIR__ . '/api/server/koneksi.php';

function fixTable($koneksi, $table, $createSql) {
    echo "<b>Fixing table $table...</b><br>";
    $res = mysqli_query($koneksi, "RENAME TABLE $table TO {$table}_old");
    if (!$res) {
        // Jika rename gagal, mungkin karena tidak ada tabel lama
        echo "Note: Could not rename $table to {$table}_old. Continuing.<br>";
    }
    
    if (mysqli_query($koneksi, $createSql)) {
        echo "Table $table recreated with AUTO_INCREMENT.<br>";
        if ($res) {
            $copy = mysqli_query($koneksi, "INSERT INTO $table SELECT * FROM {$table}_old");
            if ($copy) {
                mysqli_query($koneksi, "DROP TABLE {$table}_old");
                echo "Data copied and old table dropped.<br>";
            } else {
                echo "<span style='color:red;'>Failed to copy data: " . mysqli_error($koneksi) . "</span><br>";
            }
        }
    } else {
        echo "<span style='color:red;'>Failed to create $table: " . mysqli_error($koneksi) . "</span><br>";
    }
    echo "<br>";
}

$createUsers = "CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100)  NOT NULL,
    username    VARCHAR(50)   NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('super_admin','admin_staff','staff','user') NOT NULL DEFAULT 'user',
    divisi      VARCHAR(100)  DEFAULT NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    last_login  DATETIME     DEFAULT NULL,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
)";
fixTable($koneksi, 'users', $createUsers);

$createObat = "CREATE TABLE obat (
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
)";
fixTable($koneksi, 'obat', $createObat);

$createTransaksi = "CREATE TABLE transaksi (
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
)";
fixTable($koneksi, 'transaksi', $createTransaksi);

echo "<h2>Fix Done. Silakan akses halaman login.</h2>";
