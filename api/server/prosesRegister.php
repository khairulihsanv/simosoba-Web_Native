<?php
// ============================================================
// server/prosesRegister.php — Proses Form Registrasi
// Role baru dari register selalu 'user' (hak akses terbatas)
// Super Admin bisa upgrade role via halaman users.php
// ============================================================
require_once __DIR__ . '/session_handler.php';
session_start();
require_once __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php'); exit();
}

$nama     = trim($_POST['nama']      ?? '');
$username = trim($_POST['username']  ?? '');
$password = trim($_POST['password']  ?? '');
$konfirm  = trim($_POST['konfirmasi']?? '');
$divisi   = trim($_POST['divisi']    ?? '');

// Validasi semua field diisi
if (!$nama || !$username || !$password || !$konfirm) {
    header('Location: ../login.php?tab=register&error=empty'); exit();
}

// Validasi password minimal 6 karakter
if (strlen($password) < 6) {
    header('Location: ../login.php?tab=register&error=short'); exit();
}

// Validasi konfirmasi password cocok
if ($password !== $konfirm) {
    header('Location: ../login.php?tab=register&error=mismatch'); exit();
}

// Cek apakah username sudah dipakai
$cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? LIMIT 1");
mysqli_stmt_bind_param($cek, 's', $username);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);
if (mysqli_stmt_num_rows($cek) > 0) {
    header('Location: ../login.php?tab=register&error=duplicate'); exit();
}

// Hash password sebelum disimpan — JANGAN simpan plain text
$hash = password_hash($password, PASSWORD_DEFAULT);

// Role default untuk registrasi mandiri adalah 'staff'
$role = 'staff';

// --- LOGIKA MANUAL ID (Solusi jika AUTO_INCREMENT di DB bermasalah) ---
$resId = mysqli_query($koneksi, "SELECT MAX(id) as max_id FROM users");
$rowId = mysqli_fetch_assoc($resId);
$nextId = (int)($rowId['max_id'] ?? 0) + 1;

$stmt = mysqli_prepare($koneksi,
    "INSERT INTO users (id, nama, username, password, role, divisi, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)"
);
mysqli_stmt_bind_param($stmt, 'isssss', $nextId, $nama, $username, $hash, $role, $divisi);

session_write_close(); // Lepas session sebelum redirect
if (mysqli_stmt_execute($stmt)) {
    header('Location: ../login.php?tab=login&success=registered'); exit();
} else {
    // Debug: Tampilkan error jika gagal insert
    die("Gagal menyimpan data ke database. Error: " . mysqli_error($koneksi));
}

