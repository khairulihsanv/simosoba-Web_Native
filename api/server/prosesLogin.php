<?php
// ============================================================
// server/prosesLogin.php — Proses Form Login
// Dipanggil dari form action="server/prosesLogin.php"
// ============================================================
require_once __DIR__ . '/session_handler.php';
session_start();
require_once __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php'); exit();
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
    header('Location: ../login.php?tab=login&error=empty'); exit();
}

// Prepared statement mencegah SQL Injection
$stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// password_verify() membandingkan plain text dengan hash bcrypt
if (!$user || !password_verify($password, $user['password'])) {
    header('Location: ../login.php?tab=login&error=invalid'); exit();
}

// Catat waktu login terakhir ke database
// Kolom last_login dipakai super admin untuk pantau aktivitas user
$uid = $user['id'];
mysqli_query($koneksi, "UPDATE users SET last_login = NOW() WHERE id = $uid");

// Simpan ke session
$_SESSION['user_id']    = $user['id'];
$_SESSION['nama']       = $user['nama'];
$_SESSION['role']       = $user['role'];
$_SESSION['divisi']     = $user['divisi'] ?? '-';
$_SESSION['last_login'] = date('d M Y, H:i');

session_write_close(); // Pastikan session tersimpan sebelum redirect
header('Location: ../dashboard.php'); exit();
