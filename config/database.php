<?php
// config/database.php

/**
 * Konfigurasi Database Terpusat - SiMoSoBa
 * Mendukung MySQLi (Legacy) dan PDO (Modern)
 * Dioptimalkan untuk TiDB Cloud & Vercel
 */

$host = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$port = 4000;
$user = '23q29KSmPmgSA4v.root';
$pass = 'rTy66yom37kCjduu';
$db   = 'simosoba2';

try {
    // 1. MySQLi Connection dengan SSL (Wajib untuk TiDB Cloud di beberapa environment)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $koneksi = mysqli_init();
    
    // Konfigurasi SSL untuk MySQLi
    // Pada Vercel/Serverless, kita gunakan flag SSL_STARTUP
    if (!$koneksi->real_connect($host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL)) {
        throw new Exception("MySQLi connection failed");
    }
    $koneksi->set_charset("utf8mb4");

    // 2. PDO Connection dengan SSL
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Set false jika CA tidak tersedia secara lokal
    ];
    
    // TiDB Cloud memerlukan SSL
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Alias untuk kompatibilitas
    $conn = $pdo;

} catch (Exception $e) {
    // Log detail error ke server log (tidak tampil di browser demi keamanan)
    error_log("CRITICAL DATABASE ERROR: " . $e->getMessage());
    
    // Tampilan error yang rapi untuk user
    die("
    <div style='display:flex; justify-content:center; align-items:center; height:100vh; background:#f8fafc; font-family:sans-serif;'>
        <div style='background:white; padding:40px; border-radius:24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); border:1px solid #e2e8f0; max-width:480px; text-align:center;'>
            <div style='width:64px; height:64px; background:#fee2e2; color:#ef4444; border-radius:16px; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;'>
                <svg xmlns='http://www.w3.org/2000/svg' width='32' height='32' fill='currentColor' viewBox='0 0 16 16'><path d='M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM7 6.5a.5.5 0 0 1 1 0v3a.5.5 0 0 1-1 0v-3zM8 11a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1z'/></svg>
            </div>
            <h2 style='color:#1e293b; margin:0 0 12px; font-size:22px; font-weight:700;'>Koneksi Terputus</h2>
            <p style='color:#64748b; font-size:15px; line-height:1.6; margin:0 0 20px;'>Maaf, kami sedang mengalami gangguan koneksi ke database.</p>
            
            <div style='background:#f1f5f9; padding:15px; border-radius:12px; text-align:left; font-size:13px; color:#475569;'>
                <strong>Tips Perbaikan:</strong>
                <ul style='margin:10px 0 0 20px; padding:0;'>
                    <li>Pastikan IP Vercel Anda sudah di-whitelist di TiDB Cloud (set ke <code>0.0.0.0/0</code>).</li>
                    <li>Periksa kembali status cluster TiDB Anda.</li>
                    <li>Pastikan kuota database masih tersedia.</li>
                </ul>
            </div>
        </div>
    </div>
    ");
}
?>
