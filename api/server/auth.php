<?php
// ============================================================
// server/auth.php — Sistem Autentikasi & Role
// Include file ini di setiap halaman yang butuh proteksi
// ============================================================
// Session handler berbasis DB wajib untuk Vercel serverless
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/session_handler.php';
    session_start();
}

// ── Fungsi: Cek login ─────────────────────────────────────
// Panggil di awal halaman: requireLogin();
// Jika belum login → redirect ke login.php
function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php'); exit();
    }
}

// ── Fungsi: Cek role ──────────────────────────────────────
// $roles bisa string atau array
// Contoh: requireRole('super_admin')
//         requireRole(['super_admin','admin_staff'])
function requireRole($roles) {
    requireLogin();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($_SESSION['role'] ?? '', $allowed)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;text-align:center;padding:5rem;">
            <h2 style="color:#ef4444">⛔ Akses Ditolak</h2>
            <p>Kamu tidak punya izin ke halaman ini.</p>
            <a href="dashboard.php">← Kembali</a>
        </div>');
    }
}

// ── Shortcut cek role ─────────────────────────────────────
// Gunakan di template: if (isSuperAdmin()) { ... }
function isSuperAdmin()  { return ($_SESSION['role'] ?? '') === 'super_admin'; }
function isAdminStaff()  { return ($_SESSION['role'] ?? '') === 'admin_staff'; }
function isStaff()       { return ($_SESSION['role'] ?? '') === 'staff'; }
function isUser()        { return ($_SESSION['role'] ?? '') === 'user'; }

// ── Apakah bisa input/output stok? ────────────────────────
// super_admin, admin_staff, staff → bisa. user → tidak.
function canInputStok()  { return in_array($_SESSION['role'] ?? '', ['super_admin','admin_staff','staff']); }

// ── Apakah bisa tambah/hapus obat? ────────────────────────
function canManageObat() { return in_array($_SESSION['role'] ?? '', ['super_admin','admin_staff']); }

// ── Apakah bisa lihat laporan? ────────────────────────────
function canLihatLaporan() { return in_array($_SESSION['role'] ?? '', ['super_admin','admin_staff']); }

// ── Filter divisi untuk query SQL ─────────────────────────
// super_admin → lihat semua (WHERE 1=1)
// lainnya     → hanya divisi sendiri
// $alias: alias tabel di SQL, contoh 'o' untuk 'o.divisi'
function getDivisiFilter($alias = '') {
    if (isSuperAdmin()) return "1=1";
    $divisi = mysqli_real_escape_string($GLOBALS['koneksi'], $_SESSION['divisi'] ?? '');
    $col    = $alias ? "$alias.divisi" : "divisi";
    return "$col = '$divisi'";
}

// ── Ambil data session user aktif ─────────────────────────
function me() {
    return [
        'id'     => $_SESSION['user_id'] ?? 0,
        'nama'   => $_SESSION['nama']    ?? 'User',
        'role'   => $_SESSION['role']    ?? '',
        'divisi' => $_SESSION['divisi']  ?? '-',
    ];
}

// ── Label ramah untuk role ────────────────────────────────
// Gunakan: roleLabel($_SESSION['role'])
function roleLabel($role) {
    return [
        'super_admin' => '⚡ Super Admin',
        'admin_staff' => '🏥 Admin Staff',
        'staff'       => '💊 Staff',
        'user'        => '👤 User',
    ][$role] ?? $role;
}
