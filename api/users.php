<?php
session_start(); include 'server/koneksi.php'; include 'server/auth.php';
requireRole('super_admin');
$msgs=['added'=>['ok','✅ User ditambahkan!'],'updated'=>['ok','✅ User diperbarui!'],'deleted'=>['ok','✅ User dihapus!'],
       'invalid'=>['err','❌ Data tidak lengkap!'],'duplicate'=>['err','❌ Username sudah dipakai!'],'self'=>['err','❌ Tidak bisa hapus akun sendiri!']];
$notif=isset($_GET['success'])?($msgs[$_GET['success']]??null):(isset($_GET['error'])?($msgs[$_GET['error']]??null):null);
$users=mysqli_query($koneksi,"SELECT * FROM users ORDER BY role ASC, nama ASC");
$editUser=null;
if(!empty($_GET['edit'])){$eid=intval($_GET['edit']);$editUser=mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT * FROM users WHERE id=$eid LIMIT 1"));}
$divisiArr=[];
$dRes=mysqli_query($koneksi,"SELECT DISTINCT divisi FROM users WHERE divisi IS NOT NULL ORDER BY divisi ASC");
while($d=mysqli_fetch_assoc($dRes)) $divisiArr[]=$d['divisi'];
$rlColors=['super_admin'=>'badge-danger','admin_staff'=>'badge-info','staff'=>'badge-lime','user'=>'badge-gray'];
$expNotif=[];
$pageTitle='Kelola User'; $pageSubtitle='Super Admin Only';
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Users — SiMoSoBa</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="../css/main.css"/>
</head><body>
<?php include 'includes/topbar.php'; ?>
<div id="main-content"><div class="page-body">

<?php if ($notif): ?><div class="alert alert-<?= $notif[0] ?>" style="margin-top:.875rem;"><?= $notif[1] ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.8fr;gap:1rem;margin-top:1rem;">

<!-- Form Tambah / Edit User -->
<div class="content-card" style="align-self:start;">
  <div class="card-title"><?= $editUser?'✏️ Edit User':'👤 Tambah User' ?></div>
  <form action="server/prosesUser.php" method="POST">
    <input type="hidden" name="aksi" value="<?= $editUser?'edit':'tambah' ?>"/>
    <?php if($editUser): ?><input type="hidden" name="id" value="<?= $editUser['id'] ?>"/><?php endif; ?>

    <div style="margin-bottom:.75rem;">
      <label class="form-label">Nama Lengkap <span class="req">*</span></label>
      <input type="text" name="nama" class="form-ctrl" required placeholder="Nama lengkap" value="<?= htmlspecialchars($editUser['nama']??'') ?>"/>
    </div>
    <?php if(!$editUser): ?>
    <div style="margin-bottom:.75rem;">
      <label class="form-label">Username <span class="req">*</span></label>
      <input type="text" name="username" class="form-ctrl" required placeholder="username_unik"/>
    </div>
    <?php endif; ?>
    <div style="margin-bottom:.75rem;">
      <label class="form-label">Password <?= $editUser?'<span style="color:var(--text-muted);font-weight:400;">(kosong = tidak ubah)</span>':'<span class="req">*</span>' ?></label>
      <input type="password" name="password" class="form-ctrl" placeholder="••••••" <?= !$editUser?'required':'' ?> autocomplete="new-password"/>
    </div>
    <div style="margin-bottom:.75rem;">
      <label class="form-label">Role <span class="req">*</span></label>
      <select name="role" id="sel-role" class="form-ctrl" required onchange="toggleDiv(this.value)">
        <option value="">-- Pilih Role --</option>
        <!-- 4 role tersedia: super_admin, admin_staff, staff, user -->
        <option value="super_admin"  <?= ($editUser['role']??'')==='super_admin' ?'selected':'' ?>>⚡ Super Admin</option>
        <option value="admin_staff"  <?= ($editUser['role']??'')==='admin_staff' ?'selected':'' ?>>🏥 Admin Staff</option>
        <option value="staff"        <?= ($editUser['role']??'')==='staff'       ?'selected':'' ?>>💊 Staff</option>
        <option value="user"         <?= ($editUser['role']??'')==='user'        ?'selected':'' ?>>👤 User</option>
      </select>
    </div>
    <div id="div-wrap" style="margin-bottom:.75rem;display:<?= in_array($editUser['role']??'',['admin_staff','staff','user'])||!$editUser?'block':'none' ?>;">
      <label class="form-label">Divisi</label>
      <input type="text" name="divisi" class="form-ctrl" placeholder="Apotek A" list="div-list" value="<?= htmlspecialchars($editUser['divisi']??'') ?>"/>
      <datalist id="div-list">
        <?php foreach($divisiArr as $d): ?><option value="<?= htmlspecialchars($d) ?>"><?php endforeach; ?>
      </datalist>
    </div>
    <?php if($editUser): ?>
    <div style="margin-bottom:.875rem;">
      <label class="form-label">Status Akun</label>
      <select name="is_active" class="form-ctrl">
        <option value="1" <?= $editUser['is_active']?'selected':'' ?>>✅ Aktif</option>
        <option value="0" <?= !$editUser['is_active']?'selected':'' ?>>🚫 Nonaktif</option>
      </select>
    </div>
    <?php endif; ?>
    <div style="display:flex;gap:.5rem;">
      <button type="submit" class="btn-primary btn-full">
        <i class="bi bi-<?= $editUser?'check-lg':'person-plus' ?>"></i> <?= $editUser?'Simpan':'Tambah User' ?>
      </button>
      <?php if($editUser): ?><a href="users.php" class="btn-outline">Batal</a><?php endif; ?>
    </div>
  </form>

  <!-- Keterangan Role -->
  <div style="margin-top:1.25rem;padding:1rem;background:var(--primary-light);border-radius:12px;border:1px solid var(--border);">
    <div style="font-size:.72rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.625rem;">Keterangan Role</div>
    <div style="font-size:.75rem;color:var(--text-sub);line-height:1.9;">
      <div>⚡ <strong>Super Admin</strong> — kelola semua user & data</div>
      <div>🏥 <strong>Admin Staff</strong> — laporan + kadaluarsa divisi</div>
      <div>💊 <strong>Staff</strong> — input/output stok saja</div>
      <div>👤 <strong>User</strong> — read-only dashboard</div>
    </div>
  </div>
</div>

<!-- Tabel User -->
<div class="content-card">
  <div class="card-title" style="margin-bottom:.875rem;">👥 Daftar Pengguna</div>
  <div class="overflow-x">
    <table class="table">
      <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Divisi</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php while($u=mysqli_fetch_assoc($users)):
        $ini=strtoupper(implode('',array_map(fn($w)=>$w[0],array_slice(explode(' ',$u['nama']),0,2)))); ?>
      <tr style="<?= !$u['is_active']?'opacity:.5;':'' ?>">
        <td>
          <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:30px;height:30px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;color:var(--primary-dark);flex-shrink:0;"><?= $ini ?></div>
            <strong style="font-size:.84rem;"><?= htmlspecialchars($u['nama']) ?></strong>
          </div>
        </td>
        <td class="mono" style="font-size:.8rem;color:var(--text-sub);">@<?= htmlspecialchars($u['username']) ?></td>
        <td><span class="badge <?= $rlColors[$u['role']]??'badge-gray' ?>"><?= roleLabel($u['role']) ?></span></td>
        <td style="font-size:.8rem;color:var(--text-sub);"><?= htmlspecialchars($u['divisi']??'—') ?></td>
        <td><span class="badge <?= $u['is_active']?'badge-ok':'badge-gray' ?>"><?= $u['is_active']?'Aktif':'Nonaktif' ?></span></td>
        <td>
          <div style="display:flex;gap:.375rem;">
            <a href="users.php?edit=<?= $u['id'] ?>" class="btn-outline" style="padding:.3rem .6rem;font-size:.75rem;"><i class="bi bi-pencil"></i></a>
            <?php if($u['id']!=$_SESSION['user_id']): ?>
            <a href="server/prosesUser.php?aksi=hapus&id=<?= $u['id'] ?>" class="btn-danger" style="padding:.3rem .6rem;"
               onclick="return confirm('Hapus user: <?= htmlspecialchars(addslashes($u['nama'])) ?>?')">
              <i class="bi bi-trash"></i>
            </a>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</div>
</div></div>
<?php include 'includes/bottom_nav.php'; ?>
<?php include 'includes/scripts.php'; ?>
<script>
function toggleDiv(role){ document.getElementById('div-wrap').style.display=role==='super_admin'?'none':'block'; }
toggleDiv(document.getElementById('sel-role').value);
</script>
</body></html>
