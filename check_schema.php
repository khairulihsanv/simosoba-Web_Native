<?php
require_once __DIR__ . '/api/server/koneksi.php';

echo "<h1>Database Schema Fixer v2 (TiDB Optimized)</h1>";

function runFix($koneksi, $sql, $label) {
    $res = mysqli_query($koneksi, $sql);
    if ($res) {
        echo "Fixing <b>$label</b>: <span style='color:green;'>SUCCESS ✅</span><br>";
    } else {
        echo "Fixing <b>$label</b>: <span style='color:red;'>FAILED ❌</span><br>";
        echo "<blockquote>Error: " . mysqli_error($koneksi) . "</blockquote>";
    }
}

// Matikan pengecekan foreign key sementara agar bisa modifikasi kolom ID
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 0");

// 1. Fix AUTO_INCREMENT for users
runFix($koneksi, "ALTER TABLE users MODIFY id INT NOT NULL AUTO_INCREMENT", "Table Users");

// 2. Fix AUTO_INCREMENT for obat
runFix($koneksi, "ALTER TABLE obat MODIFY id INT NOT NULL AUTO_INCREMENT", "Table Obat");

// 3. Fix AUTO_INCREMENT for transaksi
runFix($koneksi, "ALTER TABLE transaksi MODIFY id INT NOT NULL AUTO_INCREMENT", "Table Transaksi");

// Aktifkan kembali pengecekan foreign key
mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 1");

// 4. Create php_sessions table
$createSession = "CREATE TABLE IF NOT EXISTS php_sessions (
    session_id varchar(128) NOT NULL,
    data text NOT NULL,
    last_access int(11) unsigned NOT NULL,
    PRIMARY KEY (session_id)
)";
runFix($koneksi, $createSession, "Table PHP Sessions");

echo "<h2>Penting:</h2>";
echo "<p>Jika semua bertanda <b>SUCCESS</b>, silakan coba Register lagi.</p>";
echo "<p>Jika ada yang <b>FAILED</b>, mohon copy pesan error di atas dan kirimkan ke saya.</p>";
echo "<h3><a href='api/login.php'>Kembali ke Login</a></h3>";
