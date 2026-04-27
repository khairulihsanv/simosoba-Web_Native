<?php
// ============================================================
// server/bps_proxy.php — Proxy untuk BPS WebAPI
// Dipanggil oleh JS fetch() di dashboard super admin
// Tujuan: menghindari error CORS saat fetch dari browser
// ============================================================

// Hanya izinkan dari halaman internal (bukan akses langsung)
require_once __DIR__ . '/session_handler.php'; // Wajib untuk Vercel agar session dikenali
session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit();
}

// Ambil URL dari parameter GET
$url = $_GET['url'] ?? '';

// Validasi: hanya boleh akses domain BPS
if (!$url || !str_starts_with($url, 'https://webapi.bps.go.id/')) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'URL tidak valid']);
    exit();
}

// Fetch data dari BPS menggunakan cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false, // nonaktifkan untuk localhost
    CURLOPT_USERAGENT      => 'SiMoSoBa/1.0 PHP-Proxy',
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

// Set header response JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache');

if ($error) {
    // cURL error (misal: timeout, tidak bisa koneksi)
    echo json_encode([
        'status'  => 'error',
        'message' => 'cURL error: ' . $error . '. Pastikan server bisa akses internet.',
    ]);
    exit();
}

if ($httpCode !== 200) {
    echo json_encode([
        'status'  => 'error',
        'message' => "HTTP $httpCode dari BPS API.",
    ]);
    exit();
}

// Teruskan response JSON dari BPS langsung ke browser
echo $response;
