<?php
// ============================================================
// dashboard.php — Dashboard per Role
// ── super_admin : tabel semua user + role + tanggal login
// ── admin_staff : laporan stok, kadaluarsa, transaksi
// ── staff/user  : form input & output stok langsung
// ============================================================
require_once 'server/session_handler.php';
session_start();
include 'server/koneksi.php';
include 'server/auth.php';
requireLogin();

$user = me();
$fDiv = getDivisiFilter();

// ── Notif popup expired (semua role) ─────────────────────
$expNotif = [];
$eR = mysqli_query($koneksi,
    "SELECT *, DATEDIFF(exp_date, CURDATE()) AS sisa_hari FROM obat
     WHERE exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND $fDiv
     ORDER BY exp_date ASC LIMIT 5"
);
while ($e = mysqli_fetch_assoc($eR)) $expNotif[] = $e;

// ═══════════════════════════════════════════════════════════
// DATA SUPER ADMIN: daftar semua user + role + last_login
// ═══════════════════════════════════════════════════════════
if (isSuperAdmin()) {
    $userList    = mysqli_query($koneksi,
        "SELECT id, nama, username, role, divisi, is_active, last_login, created_at
         FROM users ORDER BY last_login DESC, nama ASC"
    );
    $totalUser   = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM users"))['n'];
    $totalAktif  = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM users WHERE is_active=1"))['n'];
    $totalOnline = (int)mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT COUNT(*) n FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"))['n'];
    $totalDivisi = (int)mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT COUNT(DISTINCT divisi) n FROM users WHERE divisi IS NOT NULL"))['n'];
}

// ═══════════════════════════════════════════════════════════
// DATA ADMIN STAFF: laporan stok + kadaluarsa + transaksi
// ═══════════════════════════════════════════════════════════
if (isAdminStaff()) {
    $total   = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE $fDiv"))['n'];
    $aman    = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE stok>=stok_min AND stok>0 AND $fDiv"))['n'];
    $menipis = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE stok>0 AND stok<stok_min AND $fDiv"))['n'];
    $habis   = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE stok=0 AND $fDiv"))['n'];
    $exp30   = (int)mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT COUNT(*) n FROM obat WHERE exp_date<=DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND $fDiv"))['n'];
    $fDivLog = "o.divisi='" . mysqli_real_escape_string($koneksi, $user['divisi']) . "'";
    // Total keluar bulan ini
    $tKeluar = (int)mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT COALESCE(SUM(t.jumlah),0) n FROM transaksi t JOIN obat o ON t.obat_id=o.id
         WHERE t.tipe='keluar' AND $fDivLog AND MONTH(t.created_at)=MONTH(NOW()) AND YEAR(t.created_at)=YEAR(NOW())"))['n'];
    $tMasuk  = (int)mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT COALESCE(SUM(t.jumlah),0) n FROM transaksi t JOIN obat o ON t.obat_id=o.id
         WHERE t.tipe='masuk' AND $fDivLog AND MONTH(t.created_at)=MONTH(NOW()) AND YEAR(t.created_at)=YEAR(NOW())"))['n'];
    // Obat menipis & habis
    $warned  = mysqli_query($koneksi,
        "SELECT * FROM obat WHERE stok<stok_min AND $fDiv ORDER BY stok ASC LIMIT 6");
    // Obat segera kadaluarsa
    $nearExp = mysqli_query($koneksi,
        "SELECT *, DATEDIFF(exp_date, CURDATE()) AS sisa_hari FROM obat
         WHERE exp_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY) AND $fDiv
         ORDER BY exp_date ASC LIMIT 6");
    // Log transaksi terbaru
    $recentLog = mysqli_query($koneksi,
        "SELECT t.*, o.nama AS nm, o.satuan, u.nama AS nu
         FROM transaksi t JOIN obat o ON t.obat_id=o.id JOIN users u ON t.user_id=u.id
         WHERE $fDivLog ORDER BY t.created_at DESC LIMIT 8");
}

// ═══════════════════════════════════════════════════════════
// DATA STAFF / USER: form input & output stok
// ═══════════════════════════════════════════════════════════
if (isStaff() || isUser()) {
    $obatSelect = mysqli_query($koneksi,
        "SELECT id, nama, stok, satuan FROM obat WHERE $fDiv ORDER BY nama ASC");
    $warned     = mysqli_query($koneksi,
        "SELECT * FROM obat WHERE stok<stok_min AND $fDiv ORDER BY stok ASC LIMIT 5");
    // Riwayat transaksi user ini
    $myLog = mysqli_query($koneksi,
        "SELECT t.*, o.nama AS nm, o.satuan FROM transaksi t
         JOIN obat o ON t.obat_id=o.id
         WHERE t.user_id={$user['id']} ORDER BY t.created_at DESC LIMIT 6");
    $total  = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE $fDiv"))['n'];
    $habis  = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE stok=0 AND $fDiv"))['n'];
}

function stBadge($s,$m){
    if($s==0)  return ['Habis','badge-danger'];
    if($s<$m)  return ['Menipis','badge-warn'];
    return            ['Aman','badge-ok'];
}

$pageTitle    = 'Dashboard';
$pageSubtitle = $user['divisi'] !== '-' ? $user['divisi'] : 'Semua Divisi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — SiMoSoBa</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
  <link rel="stylesheet" href="../css/main.css"/>
</head>
<body>

<?php include 'includes/topbar.php'; ?>

<div id="main-content">
<div class="page-body">

  <!-- ── Greeting ────────────────────────────────────── -->
  <div style="margin:1rem 0 1.25rem;">
    <div style="font-size:1.15rem;font-weight:800;color:var(--text-main);">
      <?php
      $jam  = (int)date('H');
      $sapa = $jam<11 ? 'Selamat Pagi' : ($jam<15 ? 'Selamat Siang' : ($jam<18 ? 'Selamat Sore' : 'Selamat Malam'));
      echo $sapa.', '.htmlspecialchars(explode(' ',$user['nama'])[0]).' 👋';
      ?>
    </div>
    <div style="font-size:.78rem;color:var(--text-sub);margin-top:2px;">
      <?= roleLabel($user['role']) ?> · <?= date('d F Y') ?>
    </div>
  </div>

<?php /* ════════════════════════════════════════════════════
       SUPER ADMIN DASHBOARD
       Tampil: stat user + tabel lengkap semua user + role + last login + BPS API
       ════════════════════════════════════════════════════ */
if (isSuperAdmin()): ?>

  <!-- Stat kartu user -->
  <div class="stat-grid" style="margin-bottom:1.25rem;">
    <div class="stat-card">
      <div class="stat-icon">👥</div>
      <div class="stat-value" style="color:var(--info);"><?= $totalUser ?></div>
      <div class="stat-label">Total User</div>
    </div>
    <div class="stat-card" data-color="ok">
      <div class="stat-icon">✅</div>
      <div class="stat-value" style="color:var(--ok);"><?= $totalAktif ?></div>
      <div class="stat-label">Akun Aktif</div>
    </div>
    <div class="stat-card" data-color="warn">
      <div class="stat-icon">🟢</div>
      <div class="stat-value" style="color:var(--primary);"><?= $totalOnline ?></div>
      <div class="stat-label">Login 1 Jam ini</div>
    </div>
    <div class="stat-card" data-color="info">
      <div class="stat-icon">🏥</div>
      <div class="stat-value" style="color:var(--info);"><?= $totalDivisi ?></div>
      <div class="stat-label">Divisi</div>
    </div>
  </div>

  <!-- Tabel User + Role + Tanggal Login -->
  <div class="content-card" style="margin-bottom:1.25rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.875rem;flex-wrap:wrap;gap:.5rem;">
      <div class="card-title">👥 Daftar Pengguna & Aktivitas Login</div>
      <a href="users.php" class="btn-primary" style="padding:.4rem .875rem;font-size:.78rem;">
        <i class="bi bi-person-plus"></i> Kelola User
      </a>
    </div>
    <div class="overflow-x">
      <table class="table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Username</th>
            <th>Role</th>
            <th>Divisi</th>
            <th>Status</th>
            <th>Login Terakhir</th>
            <th>Terdaftar</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $rlColors = ['super_admin'=>'badge-danger','admin_staff'=>'badge-info','staff'=>'badge-lime','user'=>'badge-gray'];
        mysqli_data_seek($userList, 0);
        while ($u = mysqli_fetch_assoc($userList)):
          $ini = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$u['nama']),0,2))));
          // Hitung jarak waktu login terakhir
          $loginStr = '—';
          $loginColor = 'var(--text-muted)';
          if ($u['last_login']) {
              $diffMin = (time() - strtotime($u['last_login'])) / 60;
              if ($diffMin < 60)      { $loginStr = (int)$diffMin . ' menit lalu'; $loginColor='var(--ok)'; }
              elseif ($diffMin < 1440){ $loginStr = round($diffMin/60) . ' jam lalu'; $loginColor='var(--info)'; }
              else                     { $loginStr = date('d/m/Y H:i', strtotime($u['last_login'])); }
          }
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:30px;height:30px;border-radius:50%;
                          background:var(--primary-light);
                          display:flex;align-items:center;justify-content:center;
                          font-size:.65rem;font-weight:800;color:var(--primary-dark);flex-shrink:0;">
                <?= $ini ?>
              </div>
              <strong style="font-size:.84rem;"><?= htmlspecialchars($u['nama']) ?></strong>
            </div>
          </td>
          <td class="mono" style="font-size:.8rem;color:var(--text-sub);">@<?= htmlspecialchars($u['username']) ?></td>
          <td><span class="badge <?= $rlColors[$u['role']]??'badge-gray' ?>"><?= roleLabel($u['role']) ?></span></td>
          <td style="font-size:.8rem;color:var(--text-sub);"><?= htmlspecialchars($u['divisi']??'—') ?></td>
          <td>
            <span class="badge <?= $u['is_active']?'badge-ok':'badge-gray' ?>">
              <?= $u['is_active']?'Aktif':'Nonaktif' ?>
            </span>
          </td>
          <td>
            <span class="mono" style="font-size:.78rem;color:<?= $loginColor ?>;">
              <?= $loginStr ?>
            </span>
          </td>
          <td style="font-size:.75rem;color:var(--text-muted);">
            <?= date('d/m/Y', strtotime($u['created_at'])) ?>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- BPS API Widget: Statistik Fasilitas Kesehatan -->
  <div class="content-card" style="margin-bottom:1.5rem; border-left: 4px solid var(--primary);">
    <div style="margin-bottom:1.25rem;">
      <div class="card-title" style="font-size:1rem; display:flex; align-items:center; gap:8px;">
        <i class="bi bi-graph-up-arrow" style="color:var(--primary);"></i>
        Data BPS — Sebaran Fasilitas Kesehatan Nasional
      </div>
      <p style="font-size:.78rem; color:var(--text-sub); margin-top:4px; line-height:1.5;">
        Data ini disajikan sebagai referensi strategis untuk memantau persebaran sarana kesehatan di Indonesia. Informasi ini membantu manajemen dalam memproyeksikan distribusi obat ke berbagai wilayah berdasarkan ketersediaan fasilitas medis resmi dari BPS.
      </p>
    </div>

    <!-- Tabel hasil data BPS -->
    <div id="bps-loading" style="text-align:center;padding:2.5rem;color:var(--text-muted);">
      <div class="spinner" style="width:24px; height:24px; border:3px solid var(--border); border-top-color:var(--primary); border-radius:50%; animation:spin 0.8s linear infinite; display:inline-block;"></div>
      <div style="font-size:.8rem;margin-top:.75rem; font-weight:500;">Sinkronisasi data BPS...</div>
    </div>
    
    <div id="bps-error"  style="display:none; color:var(--danger); font-size:.82rem; text-align:center; padding:1.5rem; background:#fff5f5; border-radius:12px; margin-top:1rem;"></div>
    
    <div id="bps-table" class="overflow-x"></div>

    <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
      <span style="font-size:.72rem; color:var(--text-muted);">Sumber: <b>webapi.bps.go.id</b></span>
      <button onclick="loadBPS()" class="btn-primary" style="padding:.35rem .75rem; font-size:.7rem; background:transparent; color:var(--text-sub); border:1px solid var(--border); box-shadow:none;">
        <i class="bi bi-arrow-clockwise"></i> Perbarui Data
      </button>
    </div>
  </div>

<?php /* ════════════════════════════════════════════════════
       ADMIN STAFF DASHBOARD
       Tampil: stat stok + peringatan + kadaluarsa + log transaksi
       ════════════════════════════════════════════════════ */
elseif (isAdminStaff()): ?>

  <!-- Stat stok divisi -->
  <div class="stat-grid" style="margin-bottom:1.25rem;">
    <div class="stat-card">
      <div class="stat-icon">💊</div>
      <div class="stat-value"><?= $total ?></div>
      <div class="stat-label">Total Jenis Obat</div>
    </div>
    <div class="stat-card" data-color="ok">
      <div class="stat-icon">✅</div>
      <div class="stat-value" style="color:var(--ok);"><?= $aman ?></div>
      <div class="stat-label">Stok Aman</div>
    </div>
    <div class="stat-card" data-color="warn">
      <div class="stat-icon">⚠️</div>
      <div class="stat-value" style="color:var(--warn);"><?= $menipis ?></div>
      <div class="stat-label">Menipis</div>
    </div>
    <div class="stat-card" data-color="danger">
      <div class="stat-icon">🚨</div>
      <div class="stat-value" style="color:var(--danger);"><?= $habis ?></div>
      <div class="stat-label">Habis</div>
    </div>
    <div class="stat-card" data-color="danger">
      <div class="stat-icon">⏰</div>
      <div class="stat-value" style="color:var(--danger);"><?= $exp30 ?></div>
      <div class="stat-label">Exp. < 30 Hari</div>
    </div>
    <div class="stat-card" data-color="info">
      <div class="stat-icon">📤</div>
      <div class="stat-value" style="color:var(--info);"><?= number_format($tKeluar) ?></div>
      <div class="stat-label">Keluar Bulan Ini</div>
    </div>
    <div class="stat-card" data-color="ok">
      <div class="stat-icon">📥</div>
      <div class="stat-value" style="color:var(--ok);"><?= number_format($tMasuk) ?></div>
      <div class="stat-label">Masuk Bulan Ini</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">

    <!-- Obat Perlu Perhatian -->
    <div class="content-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.875rem;">
        <div class="card-title">🚨 Perlu Perhatian</div>
        <a href="stok.php" style="font-size:.75rem;color:var(--primary-dark);font-weight:700;text-decoration:none;">Kelola →</a>
      </div>
      <?php
      $hasW = false;
      while ($o = mysqli_fetch_assoc($warned)):
        $hasW = true;
        [$lbl,$cls] = stBadge($o['stok'],$o['stok_min']);
        $bg = $cls==='badge-danger'?'#fee2e2':'#fef3c7';
      ?>
      <div style="display:flex;justify-content:space-between;align-items:center;
                  padding:.55rem .75rem;border-radius:9px;background:<?= $bg ?>;margin-bottom:.375rem;">
        <div>
          <div style="font-size:.82rem;font-weight:700;"><?= htmlspecialchars($o['nama']) ?></div>
          <div style="font-size:.7rem;color:var(--text-muted);">Stok: <b><?= $o['stok'] ?></b> / Min: <?= $o['stok_min'] ?></div>
        </div>
        <span class="badge <?= $cls ?>"><?= $lbl ?></span>
      </div>
      <?php endwhile; ?>
      <?php if (!$hasW): ?>
        <p style="text-align:center;color:var(--text-muted);padding:.75rem 0;font-size:.84rem;">✅ Semua stok aman</p>
      <?php endif; ?>
    </div>

    <!-- Obat Segera Kadaluarsa -->
    <div class="content-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.875rem;">
        <div class="card-title">⏰ Segera Kadaluarsa</div>
        <a href="expired.php" style="font-size:.75rem;color:var(--primary-dark);font-weight:700;text-decoration:none;">Semua →</a>
      </div>
      <?php
      $hasE = false;
      while ($o = mysqli_fetch_assoc($nearExp)):
        $hasE = true;
        $h = (int)$o['sisa_hari'];
        $col = $h < 0 ? 'var(--danger)' : ($h<=30 ? 'var(--warn)' : 'var(--info)');
      ?>
      <div style="display:flex;justify-content:space-between;align-items:center;
                  padding:.5rem 0;border-bottom:1px solid var(--border);">
        <div>
          <div style="font-size:.82rem;font-weight:700;"><?= htmlspecialchars($o['nama']) ?></div>
          <div style="font-size:.7rem;color:var(--text-muted);"><?= date('d/m/Y',strtotime($o['exp_date'])) ?></div>
        </div>
        <span class="mono" style="font-size:.78rem;font-weight:700;color:<?= $col ?>;">
          <?= $h<0 ? abs($h).'h lalu' : $h.'h lagi' ?>
        </span>
      </div>
      <?php endwhile; ?>
      <?php if (!$hasE): ?>
        <p style="text-align:center;color:var(--text-muted);padding:.75rem 0;font-size:.84rem;">✅ Tidak ada obat mendekati expired</p>
      <?php endif; ?>
    </div>

  </div>

  <!-- Log Transaksi Terbaru -->
  <div class="content-card" style="margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.875rem;">
      <div class="card-title">📋 Transaksi Terbaru</div>
      <a href="laporan.php" style="font-size:.75rem;color:var(--primary-dark);font-weight:700;text-decoration:none;">Laporan Lengkap →</a>
    </div>
    <div style="display:flex;flex-direction:column;gap:.375rem;">
    <?php $hasL=false; while($l=mysqli_fetch_assoc($recentLog)): $hasL=true; $m=$l['tipe']==='masuk'; ?>
      <div class="log-item">
        <div>
          <div style="font-size:.84rem;font-weight:700;"><?= htmlspecialchars($l['nm']) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted);"><?= htmlspecialchars($l['nu']) ?> · <?= date('d/m H:i',strtotime($l['created_at'])) ?></div>
        </div>
        <span class="badge <?= $m?'badge-ok':'badge-danger' ?>"><?= $m?'+':'-' ?><?= $l['jumlah'] ?> <?= $l['satuan'] ?></span>
      </div>
    <?php endwhile; if(!$hasL): ?>
      <p style="text-align:center;color:var(--text-muted);padding:1rem;font-size:.84rem;">Belum ada transaksi.</p>
    <?php endif; ?>
    </div>
  </div>

<?php /* ════════════════════════════════════════════════════
       STAFF / USER DASHBOARD
       Tampil: form input + output stok langsung + riwayat mereka
       ════════════════════════════════════════════════════ */
else: ?>

  <!-- Mini stat -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem;">
    <div class="stat-card">
      <div class="stat-icon">💊</div>
      <div class="stat-value"><?= $total ?></div>
      <div class="stat-label">Total Jenis Obat</div>
    </div>
    <div class="stat-card" data-color="danger">
      <div class="stat-icon">🚨</div>
      <div class="stat-value" style="color:var(--danger);"><?= $habis ?></div>
      <div class="stat-label">Stok Habis</div>
    </div>
  </div>

  <!-- FORM INPUT & OUTPUT dalam satu card grid -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">

    <!-- ── INPUT STOK (Tambah stok yang sudah ada / update) -->
    <!-- Staff hanya bisa output; input penambahan obat baru via stok.php -->
    <!-- Form ini mencatat pemasukan stok untuk obat yang sudah ada -->
    <div class="content-card">
      <div class="card-title">📥 Input / Tambah Stok</div>
      <form action="server/prosesStokMasuk.php" method="POST">
        <input type="hidden" name="aksi" value="masuk"/>
        <div style="margin-bottom:.75rem;">
          <label class="form-label">Pilih Obat <span class="req">*</span></label>
          <select name="obat_id" class="form-ctrl" onchange="previewStok(this,'prev-in','val-in')" required>
            <option value="">-- Pilih Obat --</option>
            <?php
            mysqli_data_seek($obatSelect, 0);
            while ($o = mysqli_fetch_assoc($obatSelect)):
            ?>
              <option value="<?= $o['id'] ?>" data-stok="<?= $o['stok'] ?>" data-sat="<?= htmlspecialchars($o['satuan']) ?>">
                <?= htmlspecialchars($o['nama']) ?> (<?= $o['stok'] ?> <?= $o['satuan'] ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div id="prev-in" style="display:none;background:var(--primary-light);border-radius:9px;
             padding:.5rem .875rem;margin-bottom:.75rem;font-size:.8rem;color:var(--primary-dark);font-weight:600;">
          Stok saat ini: <span class="mono" id="val-in">-</span>
        </div>
        <div style="margin-bottom:.75rem;">
          <label class="form-label">Jumlah Masuk <span class="req">*</span></label>
          <input type="number" name="jumlah" class="form-ctrl" placeholder="0" min="1" required/>
        </div>
        <div style="margin-bottom:.875rem;">
          <label class="form-label">Keterangan</label>
          <input type="text" name="keterangan" class="form-ctrl" placeholder="Contoh: Pengiriman dari supplier"/>
        </div>
        <button type="submit" class="btn-primary btn-full">
          📥 Tambah Stok
        </button>
      </form>
    </div>

    <!-- ── OUTPUT STOK (Kurangi stok) -->
    <div class="content-card">
      <div class="card-title">📤 Output / Kurangi Stok</div>
      <form action="server/prosesObat.php" method="POST">
        <input type="hidden" name="aksi" value="output"/>
        <div style="margin-bottom:.75rem;">
          <label class="form-label">Pilih Obat <span class="req">*</span></label>
          <select name="obat_id" class="form-ctrl" onchange="previewStok(this,'prev-out','val-out')" required>
            <option value="">-- Pilih Obat --</option>
            <?php
            mysqli_data_seek($obatSelect, 0);
            while ($o = mysqli_fetch_assoc($obatSelect)):
            ?>
              <option value="<?= $o['id'] ?>" data-stok="<?= $o['stok'] ?>" data-sat="<?= htmlspecialchars($o['satuan']) ?>">
                <?= htmlspecialchars($o['nama']) ?> (<?= $o['stok'] ?> <?= $o['satuan'] ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div id="prev-out" style="display:none;background:#fef3c7;border-radius:9px;
             padding:.5rem .875rem;margin-bottom:.75rem;font-size:.8rem;color:#92400e;font-weight:600;">
          Stok tersedia: <span class="mono" id="val-out">-</span>
        </div>
        <div style="margin-bottom:.75rem;">
          <label class="form-label">Jumlah Keluar <span class="req">*</span></label>
          <input type="number" name="jumlah" class="form-ctrl" placeholder="0" min="1" required/>
        </div>
        <div style="margin-bottom:.875rem;">
          <label class="form-label">Keterangan</label>
          <input type="text" name="keterangan" class="form-ctrl" placeholder="Contoh: Resep dr. Budi"/>
        </div>
        <button type="submit" class="btn-primary btn-full" style="background:var(--info);">
          📤 Kurangi Stok
        </button>
      </form>
    </div>

  </div>

  <!-- Obat perlu perhatian -->
  <?php
  $hasW = false;
  $warnedRows = [];
  while ($o = mysqli_fetch_assoc($warned)) { $warnedRows[] = $o; $hasW = true; }
  if ($hasW):
  ?>
  <div class="content-card" style="margin-bottom:1.25rem;">
    <div class="card-title" style="margin-bottom:.875rem;">⚠️ Obat Perlu Perhatian</div>
    <?php foreach ($warnedRows as $o):
      [$lbl,$cls] = stBadge($o['stok'],$o['stok_min']);
      $bg = $cls==='badge-danger'?'#fee2e2':'#fef3c7';
    ?>
    <div style="display:flex;justify-content:space-between;align-items:center;
                padding:.55rem .75rem;border-radius:9px;background:<?= $bg ?>;margin-bottom:.375rem;">
      <div>
        <div style="font-size:.84rem;font-weight:700;"><?= htmlspecialchars($o['nama']) ?></div>
        <div style="font-size:.7rem;color:var(--text-muted);">Stok: <b><?= $o['stok'] ?></b> / Min: <?= $o['stok_min'] ?></div>
      </div>
      <span class="badge <?= $cls ?>"><?= $lbl ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Riwayat transaksi saya -->
  <div class="content-card" style="margin-bottom:1.5rem;">
    <div class="card-title" style="margin-bottom:.875rem;">🗒️ Riwayat Transaksi Saya</div>
    <div style="display:flex;flex-direction:column;gap:.375rem;">
    <?php $hasMyLog=false; while($l=mysqli_fetch_assoc($myLog)): $hasMyLog=true; $m=$l['tipe']==='masuk'; ?>
      <div class="log-item">
        <div>
          <div style="font-size:.84rem;font-weight:700;"><?= htmlspecialchars($l['nm']) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted);">
            <?= htmlspecialchars($l['keterangan']?:'—') ?> · <?= date('d/m H:i',strtotime($l['created_at'])) ?>
          </div>
        </div>
        <span class="badge <?= $m?'badge-ok':'badge-danger' ?>"><?= $m?'+':'-' ?><?= $l['jumlah'] ?> <?= $l['satuan'] ?></span>
      </div>
    <?php endwhile; if(!$hasMyLog): ?>
      <p style="text-align:center;color:var(--text-muted);padding:1rem;font-size:.84rem;">Belum ada transaksi.</p>
    <?php endif; ?>
    </div>
  </div>

<?php endif; ?>

</div><!-- /page-body -->
</div><!-- /main-content -->

<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/scripts.php'; ?>

<script>
// ── previewStok(): Tampilkan stok saat dropdown dipilih ───
// prevId = ID div preview, valId = ID span nilai
function previewStok(sel, prevId, valId) {
  const opt  = sel.options[sel.selectedIndex];
  const box  = document.getElementById(prevId);
  const val  = document.getElementById(valId);
  if (sel.value && box) {
    val.textContent = (opt.dataset.stok || '?') + ' ' + (opt.dataset.sat || '');
    box.style.display = 'block';
  } else if (box) {
    box.style.display = 'none';
  }
}

<?php if (isSuperAdmin()): ?>
// ── BPS API Integration (Professional Edition) ──────────────
const BPS_KEY = 'b928ea9a43f487ccb994b6bf2f308278';
const BPS_URL = `https://webapi.bps.go.id/v1/api/list/model/statictable/domain/0000/lang/ind/key/${BPS_KEY}`;

async function loadBPS() {
  const loading = document.getElementById('bps-loading');
  const errEl   = document.getElementById('bps-error');
  const tableEl = document.getElementById('bps-table');

  if (!loading) return;
  loading.style.display = 'block';
  errEl.style.display   = 'none';
  tableEl.innerHTML     = '';

  try {
    const res  = await fetch('server/bps_proxy.php?url=' + encodeURIComponent(BPS_URL));
    const json = await res.json();

    loading.style.display = 'none';

    if (!json || json.status !== 'OK') {
      throw new Error(json?.message || 'Gagal sinkronisasi dengan server BPS.');
    }

    const dataArr = Array.isArray(json.data) ? json.data : [];
    const rows    = dataArr.length > 1 && Array.isArray(dataArr[1]) ? dataArr[1] : dataArr;

    if (!rows || rows.length === 0) {
      tableEl.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.84rem;">Informasi belum tersedia saat ini.</div>';
      return;
    }

    // Bangun tabel HTML dari data BPS
    const keys = Object.keys(rows[0]).slice(0, 5); // ambil max 5 kolom
    let html = `<p style="font-size:.72rem;color:var(--text-muted);margin-bottom:.5rem;">${bpsLabels[type]} — ${rows.length} entri</p>`;
    html += '<table class="table"><thead><tr>';
    keys.forEach(k => { html += `<th>${k}</th>`; });
    html += '</tr></thead><tbody>';
    rows.slice(0, 10).forEach(r => { // tampilkan max 10 baris
      html += '<tr>';
      keys.forEach(k => { html += `<td style="font-size:.78rem;">${r[k] ?? '—'}</td>`; });
      html += '</tr>';
    });
    html += '</tbody></table>';
    if (rows.length > 10) {
      html += `<p style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:.5rem;">Menampilkan 10 dari ${rows.length} data. Gunakan Postman untuk data lengkap.</p>`;
    }
    tableEl.innerHTML = html;

  } catch (e) {
    loading.style.display = 'none';
    errEl.style.display   = 'block';
    errEl.textContent     = '⚠️ Gagal ambil data BPS: ' + e.message +
                            '. Coba buka endpoint langsung di browser atau Postman.';
  }
}

// Auto-load saat halaman pertama kali dibuka
document.addEventListener('DOMContentLoaded', () => loadBPS('sarkes'));
<?php endif; ?>
</script>

</body>
</html>
