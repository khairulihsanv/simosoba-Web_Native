<?php
// stok.php — Input / Output Stok Obat
require_once 'server/session_handler.php';
session_start();
include 'server/koneksi.php';
include 'server/auth.php';
requireLogin();

$user = me(); $fDiv = getDivisiFilter();

$msgs = ['added'=>['ok','✅ Obat berhasil ditambahkan!'],'deleted'=>['ok','✅ Obat dihapus!'],
         'output'=>['ok','✅ Pengeluaran stok berhasil!'],'invalid'=>['err','❌ Data tidak valid!'],
         'db'=>['err','❌ Gagal simpan ke database!'],'stok_kurang'=>['err','❌ Stok tidak cukup!']];
$notif = isset($_GET['success']) ? ($msgs[$_GET['success']]??null) : (isset($_GET['error']) ? ($msgs[$_GET['error']]??null) : null);

$keyword = trim($_GET['cari']??''); $fStatus = $_GET['status']??'semua';
$where = "WHERE $fDiv";
if ($keyword) { $kw = mysqli_real_escape_string($koneksi,$keyword); $where .= " AND (nama LIKE '%$kw%' OR kategori LIKE '%$kw%')"; }
if ($fStatus==='Aman')    $where .= " AND stok>=stok_min AND stok>0";
if ($fStatus==='Menipis') $where .= " AND stok>0 AND stok<stok_min";
if ($fStatus==='Habis')   $where .= " AND stok=0";

$allObat  = mysqli_query($koneksi,"SELECT * FROM obat $where ORDER BY nama ASC");
$selObat  = mysqli_query($koneksi,"SELECT id,nama,stok,satuan FROM obat WHERE $fDiv ORDER BY nama ASC");
$divisiList = mysqli_query($koneksi,"SELECT DISTINCT divisi FROM users WHERE divisi IS NOT NULL ORDER BY divisi ASC");

$expNotif = [];
$eR = mysqli_query($koneksi,"SELECT *,DATEDIFF(exp_date,CURDATE()) AS sisa_hari FROM obat WHERE exp_date<=DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND $fDiv ORDER BY exp_date ASC LIMIT 5");
while ($e=mysqli_fetch_assoc($eR)) $expNotif[]=$e;

function stBadge($s,$m){ if($s==0) return ['Habis','badge-danger']; if($s<$m) return ['Menipis','badge-warn']; return ['Aman','badge-ok']; }
$pageTitle='Stok Obat'; $pageSubtitle='Input & Output';
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stok — SiMoSoBa</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../css/main.css"/>
</head><body>
<?php include 'includes/topbar.php'; ?>
<div id="main-content"><div class="page-body">

<?php if ($notif): ?>
  <div class="alert alert-<?= $notif[0] ?>" style="margin-top:.875rem;"><?= $notif[1] ?></div>
<?php endif; ?>

<!-- ── Form Row ──────────────────────────────────────────── -->
<div style="display:grid;grid-template-columns:<?= canManageObat()?'1fr 1fr':'1fr' ?>;gap:1rem;margin:1rem 0;">

<?php if (canManageObat()): // Tambah obat: hanya super_admin & admin_staff ?>
<div class="content-card">
  <div class="card-title">➕ Input Obat Baru</div>
  <form action="server/prosesObat.php" method="POST">
    <input type="hidden" name="aksi" value="tambah"/>
    <div class="form-group" style="margin-bottom:.75rem;">
      <label class="form-label">Nama Obat <span class="req">*</span></label>
      <input type="text" name="nama" class="form-ctrl" placeholder="Paracetamol 500mg" required/>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.75rem;">
      <div>
        <label class="form-label">Kategori <span class="req">*</span></label>
        <select name="kategori" class="form-ctrl" required>
          <option value="">-- Pilih --</option>
          <?php foreach(['Analgesik','Antibiotik','Antasida','Vitamin','Antihipertensi','Antihistamin','Lainnya'] as $k): ?>
            <option value="<?= $k ?>"><?= $k ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label">Satuan</label>
        <select name="satuan" class="form-ctrl">
          <?php foreach(['Tablet','Kapsul','Botol','Strip','Ampul','Sachet'] as $s): ?>
            <option value="<?= $s ?>"><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.75rem;">
      <div>
        <label class="form-label">Stok <span class="req">*</span></label>
        <input type="number" name="stok" class="form-ctrl" placeholder="100" min="0" required/>
      </div>
      <div>
        <label class="form-label">Stok Min. <span class="req">*</span></label>
        <input type="number" name="stok_min" class="form-ctrl" placeholder="30" min="1" required/>
      </div>
    </div>
    <div style="margin-bottom:.75rem;">
      <label class="form-label">Tanggal Kadaluarsa <span class="req">*</span></label>
      <input type="date" name="exp_date" class="form-ctrl" required min="<?= date('Y-m-d') ?>"/>
    </div>
    <?php if (isSuperAdmin()): ?>
    <div style="margin-bottom:.75rem;">
      <label class="form-label">Divisi</label>
      <input type="text" name="divisi" class="form-ctrl" placeholder="Apotek A" list="div-list"/>
      <datalist id="div-list">
        <?php while($d=mysqli_fetch_assoc($divisiList)): ?><option value="<?= htmlspecialchars($d['divisi']) ?>"><?php endwhile; ?>
      </datalist>
    </div>
    <?php endif; ?>
    <button type="submit" class="btn-primary btn-full">➕ Simpan Obat</button>
  </form>
</div>
<?php endif; ?>

<!-- Form Output Stok (semua role yang canInputStok) -->
<div class="content-card">
  <div class="card-title">📤 Output / Pengeluaran Stok</div>
  <form action="server/prosesObat.php" method="POST">
    <input type="hidden" name="aksi" value="output"/>
    <div style="margin-bottom:.75rem;">
      <label class="form-label">Pilih Obat <span class="req">*</span></label>
      <select name="obat_id" class="form-ctrl" onchange="previewStok(this)" required>
        <option value="">-- Pilih Obat --</option>
        <?php while($o=mysqli_fetch_assoc($selObat)): ?>
          <option value="<?= $o['id'] ?>" data-stok="<?= $o['stok'] ?>" data-sat="<?= htmlspecialchars($o['satuan']) ?>">
            <?= htmlspecialchars($o['nama']) ?> (<?= $o['stok'] ?> <?= $o['satuan'] ?>)
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div id="stok-preview" style="display:none;background:var(--primary-light);border-radius:10px;padding:.5rem .875rem;margin-bottom:.75rem;font-size:.8rem;color:var(--primary-dark);font-weight:600;">
      Stok tersedia: <span class="mono" id="stok-val">-</span>
    </div>
    <div style="margin-bottom:.75rem;">
      <label class="form-label">Jumlah Keluar <span class="req">*</span></label>
      <input type="number" name="jumlah" class="form-ctrl" placeholder="Masukkan jumlah" min="1" required/>
    </div>
    <div style="margin-bottom:.875rem;">
      <label class="form-label">Keterangan</label>
      <input type="text" name="keterangan" class="form-ctrl" placeholder="Contoh: Resep dr. Budi"/>
    </div>
    <button type="submit" class="btn-primary btn-full" style="background:var(--info);">📤 Kurangi Stok</button>
  </form>
</div>
</div><!-- /grid form -->

<!-- ── Tabel Daftar Obat ─────────────────────────────────── -->
<div class="content-card" style="margin-bottom:1.5rem;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.875rem;flex-wrap:wrap;gap:.5rem;">
    <div class="card-title">📋 Daftar Obat</div>
    <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;">
      <input type="text" name="cari" class="form-ctrl" style="max-width:180px;" placeholder="🔍 Cari…" value="<?= htmlspecialchars($keyword) ?>"/>
      <select name="status" class="form-ctrl" style="max-width:150px;" onchange="this.form.submit()">
        <option value="semua" <?= $fStatus==='semua'?'selected':'' ?>>Semua</option>
        <option value="Aman" <?= $fStatus==='Aman'?'selected':'' ?>>✅ Aman</option>
        <option value="Menipis" <?= $fStatus==='Menipis'?'selected':'' ?>>⚠️ Menipis</option>
        <option value="Habis" <?= $fStatus==='Habis'?'selected':'' ?>>🚨 Habis</option>
      </select>
      <button type="submit" class="btn-primary" style="padding:.55rem .875rem;">Cari</button>
    </form>
  </div>
  <div class="overflow-x">
    <table class="table">
      <thead><tr>
        <th>No</th><th>Nama Obat</th><th>Kategori</th><th>Stok</th>
        <th>Stok Min.</th><th>Kadaluarsa</th><th>Status</th>
        <?php if(canManageObat()): ?><th>Aksi</th><?php endif; ?>
      </tr></thead>
      <tbody>
      <?php $no=1; while($o=mysqli_fetch_assoc($allObat)):
        [$lbl,$cls] = stBadge($o['stok'],$o['stok_min']); ?>
      <tr>
        <td class="mono text-muted" style="font-size:.72rem;"><?= $no++ ?></td>
        <td><strong><?= htmlspecialchars($o['nama']) ?></strong></td>
        <td><span class="badge badge-gray"><?= htmlspecialchars($o['kategori']) ?></span></td>
        <td class="mono fw-bold"><?= $o['stok'] ?> <span style="color:var(--text-muted);font-size:.7rem;"><?= $o['satuan'] ?></span></td>
        <td class="mono"><?= $o['stok_min'] ?></td>
        <td style="font-size:.8rem;color:var(--text-sub);"><?= date('d/m/Y',strtotime($o['exp_date'])) ?></td>
        <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
        <?php if(canManageObat()): ?>
        <td><a href="server/prosesObat.php?aksi=hapus&id=<?= $o['id'] ?>" class="btn-danger"
               onclick="return confirm('Hapus obat: <?= htmlspecialchars(addslashes($o['nama'])) ?>?')">
          <i class="bi bi-trash"></i>
        </a></td>
        <?php endif; ?>
      </tr>
      <?php endwhile; ?>
      <?php if(mysqli_num_rows($allObat)===0): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">Tidak ada data.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div></div>
<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/scripts.php'; ?>
<script>
function previewStok(sel){
  const opt=sel.options[sel.selectedIndex],box=document.getElementById('stok-preview'),val=document.getElementById('stok-val');
  if(sel.value){val.textContent=opt.dataset.stok+' '+opt.dataset.sat;box.style.display='block';}
  else box.style.display='none';
}
</script>
</body></html>
