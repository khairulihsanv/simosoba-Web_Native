<?php
session_start(); include 'server/koneksi.php'; include 'server/auth.php'; requireLogin();
$user=me(); $fDiv=getDivisiFilter();
$sudah=(int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE exp_date<CURDATE() AND $fDiv"))['n'];
$d30=(int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE exp_date>=CURDATE() AND exp_date<=DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND $fDiv"))['n'];
$d90=(int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE exp_date>DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND exp_date<=DATE_ADD(CURDATE(),INTERVAL 90 DAY) AND $fDiv"))['n'];
$aman=(int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) n FROM obat WHERE exp_date>DATE_ADD(CURDATE(),INTERVAL 90 DAY) AND $fDiv"))['n'];
$fExp=$_GET['filter']??'semua'; $cari=trim($_GET['cari']??'');
$where="WHERE $fDiv";
if($cari){$kw=mysqli_real_escape_string($koneksi,$cari);$where.=" AND nama LIKE '%$kw%'";}
if($fExp==='sudah') $where.=" AND exp_date<CURDATE()";
if($fExp==='30')    $where.=" AND exp_date>=CURDATE() AND exp_date<=DATE_ADD(CURDATE(),INTERVAL 30 DAY)";
if($fExp==='90')    $where.=" AND exp_date>DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND exp_date<=DATE_ADD(CURDATE(),INTERVAL 90 DAY)";
if($fExp==='aman')  $where.=" AND exp_date>DATE_ADD(CURDATE(),INTERVAL 90 DAY)";
$obatList=mysqli_query($koneksi,"SELECT *,DATEDIFF(exp_date,CURDATE()) AS sisa_hari FROM obat $where ORDER BY exp_date ASC");
$expNotif=[];
$eR=mysqli_query($koneksi,"SELECT *,DATEDIFF(exp_date,CURDATE()) AS sisa_hari FROM obat WHERE exp_date<=DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND $fDiv ORDER BY exp_date ASC LIMIT 5");
while($e=mysqli_fetch_assoc($eR)) $expNotif[]=$e;
function expBadge($h){ if($h<0) return ['Kadaluarsa','badge-danger']; if($h<=30) return ['<30 hari','badge-warn']; if($h<=90) return ['<90 hari','badge-info']; return ['Aman','badge-ok']; }
function stBadge2($s,$m){ if($s==0) return ['Habis','badge-danger']; if($s<$m) return ['Menipis','badge-warn']; return ['Aman','badge-ok']; }
$pageTitle='Kadaluarsa'; $pageSubtitle='Monitor Tanggal Expired';
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Kadaluarsa — SiMoSoBa</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../css/main.css"/>
</head><body>
<?php include 'includes/topbar.php'; ?>
<div id="main-content"><div class="page-body">

<!-- Stat Cards — klik untuk filter -->
<div class="stat-grid" style="margin:1rem 0 1.25rem;">
  <?php
  $filters=[['sudah',$sudah,'🔴','Kadaluarsa','var(--danger)'],['30',$d30,'🟡','< 30 Hari','var(--warn)'],
            ['90',$d90,'🔵','< 90 Hari','var(--info)'],['aman',$aman,'🟢','Aman',  'var(--ok)']];
  foreach($filters as [$fk,$fv,$fi,$fl,$fc]): ?>
  <a href="?filter=<?= $fk ?>" style="text-decoration:none;">
    <div class="stat-card" style="<?= $fExp===$fk?'box-shadow:0 0 0 2px '.$fc.';':'' ?>cursor:pointer;">
      <div class="stat-icon"><?= $fi ?></div>
      <div class="stat-value" style="color:<?= $fc ?>;"><?= $fv ?></div>
      <div class="stat-label"><?= $fl ?></div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<!-- Tabel -->
<div class="content-card" style="margin-bottom:1.5rem;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.875rem;flex-wrap:wrap;gap:.5rem;">
    <div class="card-title">📅 Tabel Kadaluarsa</div>
    <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($fExp) ?>"/>
      <input type="text" name="cari" class="form-ctrl" style="max-width:180px;" placeholder="🔍 Cari…" value="<?= htmlspecialchars($cari) ?>"/>
      <button type="submit" class="btn-primary" style="padding:.55rem .875rem;">Cari</button>
      <?php if($cari||$fExp!=='semua'): ?><a href="expired.php" class="btn-outline" style="padding:.55rem .875rem;">Reset</a><?php endif; ?>
    </form>
  </div>
  <div class="overflow-x">
    <table class="table">
      <thead><tr><th>No</th><th>Nama</th><th>Stok</th><th>Exp. Date</th><th>Sisa Hari</th><th>Status Exp.</th><th>Status Stok</th></tr></thead>
      <tbody>
      <?php $no=1; while($o=mysqli_fetch_assoc($obatList)):
        $h=(int)$o['sisa_hari']; [$el,$ec]=expBadge($h); [$sl,$sc]=stBadge2($o['stok'],$o['stok_min']);
        $col=$h<0?'var(--danger)':($h<=7?'var(--warn)':'var(--text-muted)'); ?>
      <tr>
        <td class="mono text-muted" style="font-size:.72rem;"><?= $no++ ?></td>
        <td><strong><?= htmlspecialchars($o['nama']) ?></strong><br><span class="badge badge-gray" style="font-size:.62rem;"><?= htmlspecialchars($o['kategori']) ?></span></td>
        <td class="mono"><?= $o['stok'] ?> <span style="color:var(--text-muted);font-size:.7rem;"><?= $o['satuan'] ?></span></td>
        <td class="mono" style="font-size:.82rem;"><?= date('d/m/Y',strtotime($o['exp_date'])) ?></td>
        <td><span class="mono fw-bold" style="color:<?= $col ?>;"><?= $h<0?abs($h).' hari lalu':$h.' hari lagi' ?></span></td>
        <td><span class="badge <?= $ec ?>"><?= $el ?></span></td>
        <td><span class="badge <?= $sc ?>"><?= $sl ?></span></td>
      </tr>
      <?php endwhile; ?>
      <?php if(mysqli_num_rows($obatList)===0): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">Tidak ada data.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div></div>
<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/scripts.php'; ?>
</body></html>
