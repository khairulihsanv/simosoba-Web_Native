<?php
require_once __DIR__ . '/api/server/koneksi.php';

echo "<h1>Database Schema Fixer</h1>";

// 1. Fix AUTO_INCREMENT for users
$res = mysqli_query($koneksi, "ALTER TABLE users MODIFY id INT AUTO_INCREMENT;");
echo "Fixing table <b>users</b>: " . ($res ? "<span style='color:green;'>SUCCESS (AUTO_INCREMENT added)</span>" : "<span style='color:red;'>FAILED - " . mysqli_error($koneksi) . "</span>") . "<br>";

// 2. Fix AUTO_INCREMENT for obat
$res = mysqli_query($koneksi, "ALTER TABLE obat MODIFY id INT AUTO_INCREMENT;");
echo "Fixing table <b>obat</b>: " . ($res ? "<span style='color:green;'>SUCCESS (AUTO_INCREMENT added)</span>" : "<span style='color:red;'>FAILED - " . mysqli_error($koneksi) . "</span>") . "<br>";

// 3. Fix AUTO_INCREMENT for transaksi
$res = mysqli_query($koneksi, "ALTER TABLE transaksi MODIFY id INT AUTO_INCREMENT;");
echo "Fixing table <b>transaksi</b>: " . ($res ? "<span style='color:green;'>SUCCESS (AUTO_INCREMENT added)</span>" : "<span style='color:red;'>FAILED - " . mysqli_error($koneksi) . "</span>") . "<br>";

// 4. Create php_sessions table
$createSession = "CREATE TABLE IF NOT EXISTS php_sessions (
    session_id varchar(128) NOT NULL,
    data text NOT NULL,
    last_access int(11) unsigned NOT NULL,
    PRIMARY KEY (session_id)
)";
$res = mysqli_query($koneksi, $createSession);
echo "Creating table <b>php_sessions</b>: " . ($res ? "<span style='color:green;'>SUCCESS (Table ready)</span>" : "<span style='color:red;'>FAILED - " . mysqli_error($koneksi) . "</span>") . "<br>";

echo "<h2>Fix Done. Silakan kembali ke <a href='api/login.php'>Halaman Login / Register</a>.</h2>";
