<?php
// ============================================================
// api/dashboard_stats.php — API Statistik Dashboard (JSON)
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/server/auth.php'; // Proteksi: Wajib Login
require_once __DIR__ . '/server/koneksi.php';

// Cek apakah user sudah login
if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$role   = $_SESSION['role'];
$divisi = $_SESSION['divisi'];

// Filter divisi jika bukan super_admin
$filter = getDivisiFilter();

// 1. Total Jenis Obat
$q1 = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM obat WHERE $filter");
$total_obat = mysqli_fetch_assoc($q1)['total'];

// 2. Stok Menipis (stok < stok_min)
$q2 = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM obat WHERE $filter AND stok < stok_min AND stok > 0");
$stok_menipis = mysqli_fetch_assoc($q2)['total'];

// 3. Stok Habis (stok = 0)
$q3 = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM obat WHERE $filter AND stok = 0");
$stok_habis = mysqli_fetch_assoc($q3)['total'];

// 4. Hampir Kadaluwarsa (<= 30 hari)
$q4 = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM obat WHERE $filter AND exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND exp_date >= CURDATE()");
$hampir_expired = mysqli_fetch_assoc($q4)['total'];

// 5. Sudah Kadaluwarsa
$q5 = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM obat WHERE $filter AND exp_date < CURDATE()");
$sudah_expired = mysqli_fetch_assoc($q5)['total'];

// Response JSON
echo json_encode([
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'user' => [
        'nama' => $_SESSION['nama'],
        'role' => $role,
        'divisi' => $divisi
    ],
    'stats' => [
        'total_jenis_obat' => (int)$total_obat,
        'stok_menipis'     => (int)$stok_menipis,
        'stok_habis'       => (int)$stok_habis,
        'hampir_expired'   => (int)$hampir_expired,
        'sudah_expired'    => (int)$sudah_expired
    ]
]);
