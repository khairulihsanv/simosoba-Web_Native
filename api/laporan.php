<?php
/** @var mysqli $koneksi */ //
// laporan.php — Hanya admin_staff & super_admin
require_once 'server/session_handler.php'; session_start(); include 'server/koneksi.php'; include 'server/auth.php';
requireRole(['super_admin','admin_staff']);
$user=me(); $fDiv=getDivisiFilter();
$bulan=$_GET['bulan']??date('Y-m'); $dari=$bulan.'-01'; $sampai=date('Y-m-t',strtotime($dari));
$fDivLog=getDivisiFilter('o');
$tKeluar=(int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COALESCE(SUM(t.jumlah),0) n FROM transaksi t JOIN obat o ON t.obat_id=o.id WHERE t.tipe='keluar' AND $fDivLog AND t.created_at>='$dari' AND t.created_at<='$sampai 23:59:59'"))['n'];
$tMasuk=(int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COALESCE(SUM(t.jumlah),0) n FROM transaksi t JOIN obat o ON t.obat_id=o.id WHERE t.tipe='masuk' AND $fDivLog AND t.created_at>='$dari' AND t.created_at<='$sampai 23:59:59'"))['n'];
$tObat=(int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE $fDiv"))['n'];
$topQ=mysqli_query($koneksi,"SELECT o.nama,SUM(t.jumlah) AS total FROM transaksi t JOIN obat o ON t.obat_id=o.id WHERE t.tipe='keluar' AND $fDivLog AND t.created_at>='$dari' AND t.created_at<='$sampai 23:59:59' GROUP BY t.obat_id ORDER BY total DESC LIMIT 5");
$logQ=mysqli_query($koneksi,"SELECT t.*,o.nama AS nm,o.satuan,u.nama AS nu FROM transaksi t JOIN obat o ON t.obat_id=o.id JOIN users u ON t.user_id=u.id WHERE $fDivLog AND t.created_at>='$dari' AND t.created_at<='$sampai 23:59:59' ORDER BY t.created_at DESC LIMIT 30");
$expNotif=[];
$pageTitle='Laporan'; $pageSubtitle='Periode '.$bulan;
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Laporan — SiMoSoBa</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../css/main.css"/>
</head><body>
<?php include 'includes/topbar.php'; ?>
<div id="main-content"><div class="page-body">

<div class="content-card" style="margin:1rem 0;">
  <form method="GET" style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
    <label class="form-label" style="margin:0;white-space:nowrap;">📅 Periode:</label>
    <input type="month" name="bulan" class="form-ctrl" style="max-width:180px;" value="<?= $bulan ?>" max="<?= date('Y-m') ?>"/>
    <button type="submit" class="btn-primary" style="padding:.55rem 1rem;">Tampilkan</button>
    <span style="font-size:.78rem;color:var(--text-muted);"><?= date('d M Y',strtotime($dari)) ?> — <?= date('d M Y',strtotime($sampai)) ?></span>
  </form>
</div>

<div class="stat-grid" style="margin-bottom:1.25rem;">
  <div class="stat-card"><div class="stat-icon">📥</div><div class="stat-value" style="color:var(--ok);"><?= number_format($tMasuk) ?></div><div class="stat-label">Unit Masuk</div></div>
  <div class="stat-card" data-color="info"><div class="stat-icon">📤</div><div class="stat-value" style="color:var(--info);"><?= number_format($tKeluar) ?></div><div class="stat-label">Unit Keluar</div></div>
  <div class="stat-card" data-color="gray"><div class="stat-icon">💊</div><div class="stat-value"><?= $tObat ?></div><div class="stat-label">Jenis Obat</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:1rem;margin-bottom:1.5rem;">
  <div class="content-card">
    <div class="card-title">🏆 Top 5 Terbanyak Keluar</div>
    <?php $rows=[]; $maxV=1;
    while($r=mysqli_fetch_assoc($topQ)){$rows[]=$r;if($r['total']>$maxV)$maxV=$r['total'];}
    if(empty($rows)): ?><p style="text-align:center;color:var(--text-muted);padding:1.5rem;font-size:.84rem;">Belum ada data.</p>
    <?php else: ?>
    <?php $colors=['var(--ok)','var(--info)','var(--warn)','#9333ea','#64748b'];
    foreach($rows as $i=>$r): $pct=round($r['total']/$maxV*100); ?>
    <div style="margin-bottom:.625rem;">
      <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px;">
        <span style="font-weight:600;"><?= htmlspecialchars($r['nama']) ?></span>
        <span class="mono" style="color:var(--text-muted);"><?= $r['total'] ?></span>
      </div>
      <div class="prog-bg"><div class="prog-fill" style="width:<?= $pct ?>%;background:<?= $colors[$i]??'#94a3b8' ?>;"></div></div>
    </div>
    <?php endforeach; endif; ?>
  </div>
  <div class="content-card">
    <div class="card-title">🗒️ Log Transaksi</div>
    <div style="max-height:360px;overflow-y:auto;display:flex;flex-direction:column;gap:.375rem;">
    <?php $hasL=false; while($l=mysqli_fetch_assoc($logQ)): $hasL=true; $m=$l['tipe']==='masuk'; ?>
      <div class="log-item">
        <div><div style="font-size:.82rem;font-weight:700;"><?= htmlspecialchars($l['nm']) ?></div>
          <div style="font-size:.7rem;color:var(--text-muted);"><?= htmlspecialchars($l['keterangan']?:'—') ?> · <?= htmlspecialchars($l['nu']) ?></div></div>
        <div style="text-align:right;">
          <span class="badge <?= $m?'badge-ok':'badge-danger' ?>"><?= $m?'+':'-' ?><?= $l['jumlah'] ?> <?= $l['satuan'] ?></span>
          <div class="mono" style="font-size:.68rem;color:var(--text-muted);margin-top:2px;"><?= date('d/m H:i',strtotime($l['created_at'])) ?></div>
        </div>
      </div>
    <?php endwhile; if(!$hasL): ?><p style="text-align:center;color:var(--text-muted);padding:1.5rem;font-size:.84rem;">Belum ada transaksi.</p><?php endif; ?>
    </div>
  </div>
</div>
</div></div>
<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/scripts.php'; ?>
</body></html>
