<?php
// ============================================================
// session_handler.php — Database Session Handler
// Wajib: solusi untuk Vercel serverless yang tidak menyimpan
// file session di /tmp antar-request.
// Semua session disimpan di tabel `php_sessions` di TiDB Cloud.
// Include file ini SEBELUM session_start() di semua halaman.
// ============================================================

require_once __DIR__ . '/koneksi.php';

// Auto-create php_sessions table if it doesn't exist to prevent login loops
// Ini sangat penting di Vercel agar tidak gagal save session secara diam-diam.
mysqli_query($koneksi, "
    CREATE TABLE IF NOT EXISTS php_sessions (
        session_id varchar(128) NOT NULL,
        data text NOT NULL,
        last_access int(11) unsigned NOT NULL,
        PRIMARY KEY (session_id)
    )
");

// ── Handler Functions ──────────────────────────────────────

function _sess_open($path, $name)    { return true; }
function _sess_close()               { return true; }

function _sess_read($id) {
    global $koneksi;
    $id = mysqli_real_escape_string($koneksi, $id);
    $r  = mysqli_query($koneksi,
        "SELECT data FROM php_sessions WHERE session_id='$id' AND last_access >= " . (time() - 7200)
    );
    if ($r && mysqli_num_rows($r) > 0) {
        return mysqli_fetch_assoc($r)['data'];
    }
    return '';
}

function _sess_write($id, $data) {
    global $koneksi;
    $id   = mysqli_real_escape_string($koneksi, $id);
    $data = mysqli_real_escape_string($koneksi, $data);
    $time = time();
    mysqli_query($koneksi,
        "REPLACE INTO php_sessions (session_id, data, last_access)
         VALUES ('$id', '$data', $time)"
    );
    return true;
}

function _sess_destroy($id) {
    global $koneksi;
    $id = mysqli_real_escape_string($koneksi, $id);
    mysqli_query($koneksi, "DELETE FROM php_sessions WHERE session_id='$id'");
    return true;
}

function _sess_gc($maxlife) {
    global $koneksi;
    $old = time() - $maxlife;
    mysqli_query($koneksi, "DELETE FROM php_sessions WHERE last_access < $old");
    return true;
}

// Daftarkan handler ke PHP
session_set_save_handler(
    '_sess_open',
    '_sess_close',
    '_sess_read',
    '_sess_write',
    '_sess_destroy',
    '_sess_gc'
);

// Wajib: pastikan session ditulis sebelum script selesai
register_shutdown_function('session_write_close');
