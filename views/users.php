<?php
// views/users.php - User Management (Super Admin Only)
if (!defined('BASE_PATH')) die('Access Denied');

// Proteksi Halaman: Hanya Super Admin
$auth->requireRole('super_admin');

$error = '';
$success = '';

// Inisialisasi variabel edit
$edit_id = intval($_GET['edit'] ?? 0);
$editUser = null;

// ── 1. PROSES POST ACTIONS ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $email = strtolower(trim($_POST['email'] ?? ''));
        $role = $_POST['role'] ?? 'user';
        $is_active = intval($_POST['is_active'] ?? 1);
        $password = $_POST['password'] ?? '';

        // Validasi input
        if (!$nama || !$username || !$email) {
            $error = 'Nama Lengkap, Username, dan Email wajib diisi.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            $error = 'Username hanya boleh huruf, angka, underscore (3-30 karakter).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } else {
            try {
                // Cek duplikasi username atau email pada user lain
                $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $stmt->execute([$username, $email, $id]);
                if ($stmt->fetch()) {
                    $error = 'Username atau Email sudah terdaftar pada pengguna lain.';
                } else {
                    // Update data
                    if ($password) {
                        if (strlen($password) < 8) {
                            $error = 'Password minimal 8 karakter.';
                        } else {
                            $hash = password_hash($password, PASSWORD_BCRYPT);
                            $stmt = $pdo->prepare("UPDATE users SET nama = ?, username = ?, email = ?, password = ?, role = ?, is_active = ? WHERE id = ?");
                            $stmt->execute([$nama, $username, $email, $hash, $role, $is_active, $id]);
                            $success = 'User berhasil diperbarui (dengan password baru)!';
                        }
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET nama = ?, username = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
                        $stmt->execute([$nama, $username, $email, $role, $is_active, $id]);
                        $success = 'User berhasil diperbarui!';
                    }

                    // Jika user mengedit dirinya sendiri, update session
                    if ($id === intval($_SESSION['user_id'])) {
                        $_SESSION['nama'] = $nama;
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = $role;
                    }
                }
            } catch (Exception $e) {
                $error = 'Gagal memperbarui user: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id === intval($_SESSION['user_id'])) {
            $error = 'Anda tidak dapat menghapus akun Anda sendiri.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'User berhasil dihapus!';
                // Jika sedang mengedit user yang didelete, batalkan mode edit
                if ($edit_id === $id) {
                    $edit_id = 0;
                }
            } catch (Exception $e) {
                $error = 'Gagal menghapus user: ' . $e->getMessage();
            }
        }
    }
}

// ── 2. FETCH DATA UNTUK EDIT / TABEL ─────────────────────────────────
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editUser = $stmt->fetch();
    if (!$editUser) {
        $error = 'User yang ingin diedit tidak ditemukan.';
    }
}

// Ambil semua data user
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$allUsers = $stmt->fetchAll();

// Mapping badge role warna
$roleBadges = [
    'super_admin' => 'badge-red',
    'admin_staff' => 'badge-blue',
    'staff'       => 'badge-green',
    'user'        => 'badge-gray'
];
?>

<div class="page-content page-enter">
    
    <!-- Info Alerts -->
    <?php if ($error): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <i class="bi bi-exclamation-octagon-fill"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <i class="bi bi-check-circle-fill"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
    <?php endif; ?>

    <div class="grid-3" style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px; align-items: start;">
        
        <!-- ── 3. FORM EDIT USER ──────────────────────────────────────── -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-person-gear" style="color:var(--accent)"></i>
                    <?= $editUser ? 'Edit Pengguna' : 'Informasi Pengguna' ?>
                </div>
                <div class="card-sub">
                    <?= $editUser ? 'Ubah informasi dan hak akses user' : 'Pilih user dari tabel untuk mengedit' ?>
                </div>
            </div>
            
            <div class="card-body" style="padding-top:0">
                <?php if ($editUser): ?>
                <form method="POST" action="<?= BASE_URL ?>/?page=users&edit=<?= $editUser['id'] ?>" style="display:flex; flex-direction:column; gap:16px; padding-top:16px">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $editUser['id'] ?>">

                    <div class="form-group">
                        <label class="form-label" for="user-nama">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" id="user-nama" name="nama" class="form-ctrl" required
                               placeholder="Nama Lengkap" value="<?= htmlspecialchars($editUser['nama'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-username">Username <span class="req">*</span></label>
                        <input type="text" id="user-username" name="username" class="form-ctrl" required
                               placeholder="Username" value="<?= htmlspecialchars($editUser['username'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-email">Email <span class="req">*</span></label>
                        <input type="email" id="user-email" name="email" class="form-ctrl" required
                               placeholder="nama@email.com" value="<?= htmlspecialchars($editUser['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-password">
                            Password Baru <span style="font-weight:400; color:var(--text-muted)">(kosongkan jika tidak diubah)</span>
                        </label>
                        <input type="password" id="user-password" name="password" class="form-ctrl" 
                               placeholder="Min. 8 karakter" minlength="8" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-role">Role Akses <span class="req">*</span></label>
                        <select id="user-role" name="role" class="form-ctrl" required>
                            <option value="user" <?= ($editUser['role'] ?? '') === 'user' ? 'selected' : '' ?>>User Umum (Read-only)</option>
                            <option value="staff" <?= ($editUser['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff Farmasi (Transaksi)</option>
                            <option value="admin_staff" <?= ($editUser['role'] ?? '') === 'admin_staff' ? 'selected' : '' ?>>Admin Staff (Laporan + Edit)</option>
                            <option value="super_admin" <?= ($editUser['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin (Semua Akses)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-status">Status Akun <span class="req">*</span></label>
                        <select id="user-status" name="is_active" class="form-ctrl" required>
                            <option value="1" <?= intval($editUser['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= intval($editUser['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Nonaktif / Ditangguhkan</option>
                        </select>
                    </div>

                    <div style="display:flex; gap:10px; margin-top:10px">
                        <button type="submit" class="btn btn-primary" style="flex:1">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                        <a href="<?= BASE_URL ?>/?page=users" class="btn btn-secondary">
                            Batal
                        </a>
                    </div>
                </form>
                <?php else: ?>
                <div style="text-align:center; padding:48px 16px; color:var(--text-muted)">
                    <i class="bi bi-arrow-right-circle" style="font-size:2.5rem; color:var(--accent); opacity:0.6; display:block; margin-bottom:12px"></i>
                    <p style="font-size:.86rem">Silakan klik tombol <i class="bi bi-pencil"></i> pada salah satu pengguna di tabel untuk mulai mengedit detail, merubah role, atau memperbarui status akun mereka.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── 4. TABEL DAFTAR USER ───────────────────────────────────── -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">
                        <i class="bi bi-people" style="color:var(--accent)"></i>
                        Daftar Pengguna
                    </div>
                    <div class="card-sub">
                        Manajemen kredensial dan tingkat otorisasi sistem
                    </div>
                </div>
                <div>
                    <input type="text" id="user-search" class="form-ctrl" placeholder="Cari pengguna..." 
                           style="width:200px" onkeyup="searchUsers()" aria-label="Cari pengguna">
                </div>
            </div>

            <div class="card-body" style="padding-top:0">
                <div class="table-wrap">
                    <table class="data-table" id="users-table">
                        <thead>
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th style="text-align:right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $u): ?>
                            <tr class="user-row" data-name="<?= htmlspecialchars(strtolower($u['nama'])) ?>" data-username="<?= htmlspecialchars(strtolower($u['username'])) ?>">
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px">
                                        <div style="width:32px; height:32px; border-radius:50%; background:var(--accent-light); color:var(--accent); font-weight:700; font-size:.8rem; display:flex; align-items:center; justify-content:center">
                                            <?= strtoupper(substr($u['nama'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight:600; color:var(--text-primary)"><?= htmlspecialchars($u['nama']) ?></div>
                                            <div style="font-size:.74rem; color:var(--text-muted)">Dibuat: <?= date('d M Y', strtotime($u['created_at'])) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size:.8rem; color:var(--accent)">@<?= htmlspecialchars($u['username'] ?? '-') ?></code>
                                </td>
                                <td>
                                    <span style="font-size:.8rem; color:var(--text-secondary)"><?= htmlspecialchars($u['email']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $roleBadges[$u['role']] ?? 'badge-gray' ?>">
                                        <?= htmlspecialchars(Auth::roleLabel($u['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (intval($u['is_active'] ?? 1) === 1): ?>
                                    <span class="badge badge-green">
                                        <span class="status-dot" style="background:var(--green)"></span> Aktif
                                    </span>
                                    <?php else: ?>
                                    <span class="badge badge-gray">
                                        <span class="status-dot" style="background:var(--text-muted)"></span> Nonaktif
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right">
                                    <div style="display:inline-flex; gap:6px">
                                        <a href="<?= BASE_URL ?>/?page=users&edit=<?= $u['id'] ?>" 
                                           class="btn btn-secondary btn-sm" style="padding:4px 8px" title="Edit User">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <?php if ($u['id'] !== intval($_SESSION['user_id'])): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/?page=users" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus user <?= htmlspecialchars($u['nama']) ?>? Tindakan ini tidak bisa dibatalkan.');" 
                                              style="display:inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px; background:var(--red); color:#fff; border:none" title="Hapus User">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function searchUsers() {
    const query = document.getElementById('user-search').value.toLowerCase();
    const rows = document.querySelectorAll('#users-table .user-row');
    
    rows.forEach(row => {
        const name = row.dataset.name || '';
        const username = row.dataset.username || '';
        if (name.includes(query) || username.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
