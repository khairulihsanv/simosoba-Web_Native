<?php
// config/database.php

/**
 * Konfigurasi Database Terpusat - SiMoSoBa
 * Menggunakan PDO untuk TiDB Cloud dengan SSL
 */

// Load environment variables with fallback (for development)
$host = getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
$port = getenv('DB_PORT') ?: 4000;
$user = getenv('DB_USER') ?: '23q29KSmPmgSA4v.root';
$pass = getenv('DB_PASS') ?: 'rTy66yom37kCjduu';
$db   = getenv('DB_NAME') ?: 'simosoba2';

try {
    // PDO Connection dengan SSL (Wajib untuk TiDB Cloud Serverless)
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_SSL_CA       => true, // Menggunakan system CA untuk verifikasi SSL
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Abaikan verifikasi sertifikat spesifik untuk Vercel
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

    // Alias untuk kompatibilitas dengan init.php
    $conn = $pdo;

} catch (Exception $e) {
    // Tangkap detail error keamanan
    $detailError = $e->getMessage();

    die("
    <div style='display:flex; justify-content:center; align-items:center; height:100vh; background:#f8fafc; font-family:sans-serif;'>
        <div style='background:white; padding:40px; border-radius:24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); border:1px solid #ef4444; max-width:600px; text-align:center;'>
            <div style='width:64px; height:64px; background:#fee2e2; color:#ef4444; border-radius:16px; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;'>
                <svg xmlns='http://www.w3.org/2000/svg' width='32' height='32' fill='currentColor' viewBox='0 0 16 16'><path d='M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM7 6.5a.5.5 0 0 1 1 0v3a.5.5 0 0 1-1 0v-3zM8 11a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1z'/></svg>
            </div>
            <h2 style='color:#1e293b; margin:0 0 12px; font-size:20px; font-weight:700;'>Error Keamanan Database</h2>
            <p style='color:#64748b; font-size:14px; line-height:1.6; margin:0 0 20px;'>TiDB Cloud memerlukan koneksi aman (SSL), namun sistem mendeteksi percobaan akses yang tidak terenkripsi:</p>

            <div style='background:#fef2f2; padding:15px; border-radius:12px; text-align:left; font-family:monospace; font-size:12px; color:#b91c1c; border:1px solid #fecaca; margin-bottom:20px;'>
                $detailError
            </div>

            <div style='text-align:left; font-size:13px; color:#475569; background:#f1f5f9; padding:15px; border-radius:12px;'>
                <strong>Saran AI:</strong>
                <ul style='margin:5px 0 0 20px; padding:0;'>
                    <li>Pastikan Anda sudah melakukan <b>git push</b> versi terbaru file ini ke Vercel.</li>
                    <li>IP Whitelist wajib di-set ke <code>0.0.0.0/0</code> di panel TiDB Cloud.</li>
                </ul>
            </div>
        </div>
    </div>
    ");
}
?>