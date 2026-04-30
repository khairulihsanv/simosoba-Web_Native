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
    $fDivLog = getDivisiFilter('o');
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
            <?= $u['created_at'] ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- BPS API Widget: Market & Health Insights -->
  <div class="content-card" style="margin-bottom:1.5rem; border-left: 4px solid var(--primary); position: relative; overflow: hidden;">
    <!-- Dekorasi Background -->
    <div style="position:absolute; top:-20px; right:-20px; font-size:5rem; opacity:0.03; color:var(--primary); transform:rotate(15deg); pointer-events:none;">
      <i class="bi bi-graph-up"></i>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.25rem; flex-wrap:wrap; gap:1rem;">
      <div>
        <div class="card-title" style="font-size:1rem; display:flex; align-items:center; gap:8px;">
          <i class="bi bi-database-fill-check" style="color:var(--primary);"></i>
          Wawasan Strategis BPS
        </div>
        <p style="font-size:.78rem; color:var(--text-sub); margin-top:4px; max-width:500px; line-height:1.5;">
          Data statistik nasional terintegrasi untuk membantu perencanaan stok dan analisis pasar obat.
        </p>
      </div>
      
      <div style="display:flex; gap:8px;">
        <select id="bps-category" class="form-ctrl" style="font-size:.75rem; padding:.35rem .75rem; width:auto; height:32px;" onchange="changeBPSCategory()">
          <option value="apotek_jaktim">💊 Apotek (Jak-Tim)</option>
          <option value="apotek_jakut">💊 Apotek (Jak-Ut)</option>
          <option value="populasi">👥 Demografi Penduduk</option>
        </select>
        <button onclick="loadBPS()" class="btn-primary" style="padding:.35rem .75rem; font-size:.75rem; height:32px; background:var(--primary-light); color:var(--primary-dark); border:none; box-shadow:none;">
          <i class="bi bi-arrow-clockwise"></i>
        </button>
      </div>
    </div>

    <!-- Container Data -->
    <div id="bps-container" style="min-height:200px;">
        <div id="bps-loading" style="text-align:center;padding:3rem 0;color:var(--text-muted);">
          <div class="spinner" style="width:28px; height:28px; border:3px solid var(--border); border-top-color:var(--primary); border-radius:50%; animation:spin 0.8s linear infinite; display:inline-block;"></div>
          <div style="font-size:.8rem;margin-top:1rem; font-weight:600; letter-spacing:0.5px;">SINKRONISASI DATA BPS...</div>
        </div>
        
        <div id="bps-error"  style="display:none; color:var(--danger); font-size:.82rem; text-align:center; padding:1.5rem; background:#fff5f5; border:1px solid #fee2e2; border-radius:12px;"></div>
        
        <div id="bps-content"></div>
    </div>

    <div style="margin-top:1.25rem; padding-top:1rem; border-top:1px dashed var(--border); display:flex; justify-content:space-between; align-items:center;">
      <span style="font-size:.72rem; color:var(--text-muted);">Sumber: <b style="color:var(--text-main);">webapi.bps.go.id</b></span>
      <span id="bps-last-update" style="font-size:.7rem; color:var(--text-muted); font-style:italic;"></span>
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
          <div style="font-size:.7rem;color:var(--text-muted);"><?= $o['exp_date'] ? date('d/m/Y',strtotime($o['exp_date'])) : '—' ?></div>
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
          <div style="font-size:.72rem;color:var(--text-muted);"><?= htmlspecialchars($l['nu']) ?> · <?= $l['created_at'] ? date('d/m H:i',strtotime($l['created_at'])) : '—' ?></div>
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
  <div class="responsive-grid" style="margin-bottom:1.25rem;">

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
            <?= htmlspecialchars($l['keterangan']?:'—') ?> · <?= $l['created_at'] ? date('d/m H:i',strtotime($l['created_at'])) : '—' ?>
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
// ── BPS API Integration (Advanced Dashboard Edition) ──────────────
const BPS_KEY = 'b928ea9a43f487ccb994b6bf2f308278';

// Daftar Indikator yang relevan
const BPS_CONFIG = {
  apotek_jaktim: {
    name: 'Jumlah Apotek (Jakarta Timur)',
    // Menggunakan domain 3175 (Jakarta Timur), Var 679 (Apotek)
    url: `https://webapi.bps.go.id/v1/api/list/model/data/domain/3175/var/679/th/120/key/${BPS_KEY}`,
    icon: 'bi-capsule',
    color: 'var(--ok)'
  },
  apotek_jakut: {
    name: 'Jumlah Apotek (Jakarta Utara)',
    // Menggunakan domain 3172 (Jakarta Utara), Var 679 (Apotek)
    url: `https://webapi.bps.go.id/v1/api/list/model/data/domain/3172/var/679/th/120/key/${BPS_KEY}`,
    icon: 'bi-capsule',
    color: 'var(--primary)'
  },
  populasi: {
    name: 'Demografi Penduduk (Kelompok Umur)',
    url: `https://webapi.bps.go.id/v1/api/list/model/data/domain/0000/var/2135/th/2023/key/${BPS_KEY}`,
    icon: 'bi-people',
    color: 'var(--info)'
  }
};

function changeBPSCategory() {
  loadBPS();
}

async function loadBPS() {
  const type    = document.getElementById('bps-category')?.value || 'obat';
  const loading = document.getElementById('bps-loading');
  const errEl   = document.getElementById('bps-error');
  const content = document.getElementById('bps-content');
  const updateEl = document.getElementById('bps-last-update');
  const config  = BPS_CONFIG[type];

  if (!loading || !config) return;
  
  loading.style.display = 'block';
  errEl.style.display   = 'none';
  content.innerHTML     = '';

  try {
    const res  = await fetch('server/bps_proxy.php?url=' + encodeURIComponent(config.url));
    const json = await res.json();

    loading.style.display = 'none';

    if (!json || json.status !== 'OK') {
      throw new Error(json?.message || 'Gagal sinkronisasi dengan server BPS.');
    }

    // Parsing struktur data dinamis BPS
    const variables = json.var || [];
    const vervar    = json.vervar || []; 
    const turvar    = json.turvar || []; 
    const data      = json.data || {};

    if (!variables.length || !vervar.length) {
      content.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.84rem;">Informasi data indikator ini belum tersedia di API BPS saat ini.</div>';
      return;
    }

    let html = `
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background:var(--primary-light); padding:1rem; border-radius:12px; border-left:4px solid ${config.color};">
          <div style="font-size:.7rem; color:var(--text-sub); text-transform:uppercase; font-weight:700; margin-bottom:4px;">Indikator Terpilih</div>
          <div style="font-size:.9rem; font-weight:800; color:var(--text-main); line-height:1.2;">${config.name}</div>
        </div>
      </div>
      
      <div class="overflow-x">
        <table class="table" style="font-size:.78rem;">
          <thead>
            <tr>
              <th style="background:var(--bg-sub); position:sticky; left:0; z-index:1;">Wilayah / Dimensi</th>
              ${turvar.slice(0, 3).map(t => `<th>${t.label}</th>`).join('')}
            </tr>
          </thead>
          <tbody>
    `;

    vervar.slice(0, 11).forEach(v => {
      html += `<tr>
        <td style="font-weight:600; background:white; position:sticky; left:0; z-index:1; border-right:1px solid var(--border);">${v.label}</td>
      `;
      turvar.slice(0, 3).forEach(t => {
        const valKey = Object.keys(data).find(k => k.includes(v.val) && k.includes(t.val)) || '';
        const val = data[valKey] || '—';
        html += `<td class="mono" style="color:var(--text-main); font-weight:500;">${val}</td>`;
      });
      html += '</tr>';
    });

    html += '</tbody></table></div>';
    content.innerHTML = html;
    
    if (updateEl) {
      updateEl.textContent = 'Terakhir diperbarui: ' + new Date().toLocaleTimeString('id-ID');
    }

  } catch (e) {
    loading.style.display = 'none';
    errEl.style.display   = 'block';
    errEl.textContent     = '⚠️ Masalah Koneksi BPS: ' + e.message;
    console.error('BPS Error:', e);
  }
}

// Auto-load saat halaman pertama kali dibuka
document.addEventListener('DOMContentLoaded', () => loadBPS());
<?php endif; ?>
</script>

</body>
</html>
