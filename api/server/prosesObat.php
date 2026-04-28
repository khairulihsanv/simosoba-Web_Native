<?php
// ============================================================
// server/prosesObat.php — CRUD Obat & Transaksi
// aksi: tambah | output | hapus
// ============================================================
require_once __DIR__ . '/session_handler.php';
session_start();
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/auth.php';
requireLogin();

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

function redirStok($t, $k) { header("Location: ../stok.php?$t=$k"); exit(); }

// ── Tambah Obat ───────────────────────────────────────────
if ($aksi === 'tambah') {
    requireRole(['super_admin','admin_staff']);
    $nama     = trim($_POST['nama']     ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $satuan   = trim($_POST['satuan']   ?? 'Tablet');
    $stok     = intval($_POST['stok']   ?? 0);
    $stok_min = intval($_POST['stok_min'] ?? 10);
    $exp_date = trim($_POST['exp_date'] ?? '');
    $divisi   = isSuperAdmin() ? trim($_POST['divisi'] ?? '') : ($_SESSION['divisi'] ?? '');

    if (!$nama || !$kategori || !$exp_date) { redirStok('error','invalid'); }

    // --- LOGIKA MANUAL ID OBAT ---
    $resO = mysqli_query($koneksi, "SELECT MAX(id) as max_id FROM obat");
    $rowO = mysqli_fetch_assoc($resO);
    $nextO = (int)($rowO['max_id'] ?? 0) + 1;

    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO obat (id,nama,kategori,satuan,stok,stok_min,exp_date,divisi) VALUES (?,?,?,?,?,?,?,?)"
    );
    mysqli_stmt_bind_param($stmt,'isssiiis',$nextO,$nama,$kategori,$satuan,$stok,$stok_min,$exp_date,$divisi);

    if (mysqli_stmt_execute($stmt)) {
        // Catat ke log jika ada stok awal
        if ($stok > 0) {
            $oid = $nextO;
            $uid = $_SESSION['user_id'];
            
            // --- LOGIKA MANUAL ID TRANSAKSI ---
            $resT = mysqli_query($koneksi, "SELECT MAX(id) as max_id FROM transaksi");
            $rowT = mysqli_fetch_assoc($resT);
            $nextT = (int)($rowT['max_id'] ?? 0) + 1;

            $log = mysqli_prepare($koneksi,
                "INSERT INTO transaksi (id,obat_id,user_id,tipe,jumlah,stok_sesudah,keterangan) VALUES (?,?,?,'masuk',?,?,'Stok awal')"
            );
            mysqli_stmt_bind_param($log,'iiiii',$nextT,$oid,$uid,$stok,$stok);
            mysqli_stmt_execute($log);
        }
        redirStok('success','added');
    } else { redirStok('error','db'); }
}

// ── Output Stok ───────────────────────────────────────────
if ($aksi === 'output') {
    $obat_id    = intval($_POST['obat_id'] ?? 0);
    $jumlah     = intval($_POST['jumlah']  ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $uid        = $_SESSION['user_id'];

    if ($obat_id <= 0 || $jumlah <= 0) { redirStok('error','invalid'); }

    $obat = mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT stok FROM obat WHERE id=$obat_id LIMIT 1"));
    if (!$obat) { redirStok('error','invalid'); }
    if ($jumlah > $obat['stok']) { redirStok('error','stok_kurang'); }

    $baru = $obat['stok'] - $jumlah;
    mysqli_query($koneksi,"UPDATE obat SET stok=$baru WHERE id=$obat_id");

    // --- LOGIKA MANUAL ID TRANSAKSI ---
    $resT = mysqli_query($koneksi, "SELECT MAX(id) as max_id FROM transaksi");
    $rowT = mysqli_fetch_assoc($resT);
    $nextT = (int)($rowT['max_id'] ?? 0) + 1;

    $log = mysqli_prepare($koneksi,
        "INSERT INTO transaksi (id,obat_id,user_id,tipe,jumlah,stok_sesudah,keterangan) VALUES (?,?,?,'keluar',?,?,?)"
    );
    mysqli_stmt_bind_param($log,'iiiiis',$nextT,$obat_id,$uid,$jumlah,$baru,$keterangan);
    mysqli_stmt_execute($log);
    redirStok('success','output');
}

// ── Hapus Obat ────────────────────────────────────────────
if ($aksi === 'hapus') {
    requireRole(['super_admin','admin_staff']);
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) mysqli_query($koneksi,"DELETE FROM obat WHERE id=$id");
    redirStok('success','deleted');
}

header('Location: ../stok.php'); exit();
