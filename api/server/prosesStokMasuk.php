<?php
// ============================================================
// server/prosesStokMasuk.php — Proses Input / Tambah Stok
// Dipakai oleh staff & user untuk mencatat pemasukan stok
// obat yang sudah ada di database (bukan tambah obat baru)
// ============================================================
require_once __DIR__ . '/session_handler.php';
session_start();
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/auth.php';
requireLogin(); // semua role yang sudah login bisa input stok

$obat_id    = intval($_POST['obat_id']    ?? 0);
$jumlah     = intval($_POST['jumlah']     ?? 0);
$keterangan = trim($_POST['keterangan']   ?? '');
$uid        = $_SESSION['user_id'];

// Validasi input
if ($obat_id <= 0 || $jumlah <= 0) {
    header('Location: ../dashboard.php?error=invalid'); exit();
}

// Ambil stok saat ini
$obat = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT stok FROM obat WHERE id = $obat_id LIMIT 1")
);
if (!$obat) {
    header('Location: ../dashboard.php?error=invalid'); exit();
}

$stok_baru = $obat['stok'] + $jumlah;

// Update stok di tabel obat
mysqli_query($koneksi, "UPDATE obat SET stok = $stok_baru WHERE id = $obat_id");

// Catat ke log transaksi sebagai tipe 'masuk'
$stmt = mysqli_prepare($koneksi,
    "INSERT INTO transaksi (obat_id, user_id, tipe, jumlah, stok_sesudah, keterangan)
     VALUES (?, ?, 'masuk', ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'iiiis', $obat_id, $uid, $jumlah, $stok_baru, $keterangan);
mysqli_stmt_execute($stmt);

header('Location: ../dashboard.php?success=masuk'); exit();
