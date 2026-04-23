<?php
// ============================================================
// includes/topbar.php — Header Atas Halaman
// Menampilkan: logo | judul halaman | jam | avatar + menu
// Variabel $pageTitle harus di-set di halaman pemanggil
// ============================================================

$user = me(); // dari auth.php

// Inisial nama (2 huruf) untuk avatar
$inisial = strtoupper(implode('',
    array_map(fn($w) => $w[0],
    array_slice(explode(' ', $user['nama']), 0, 2))
));
?>

<header class="topbar">

  <!-- Kiri: Logo + Judul halaman -->
  <div class="topbar-left">
    <span class="topbar-logo">💊 SiMoSoBa</span>
    <span style="color:var(--border-dark);font-size:1rem;">|</span>
    <div>
      <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
      <?php if (!empty($pageSubtitle)): ?>
        <div class="topbar-sub"><?= htmlspecialchars($pageSubtitle) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Kanan: Jam + Avatar + Dropdown menu -->
  <div class="topbar-right">

    <!-- Jam real-time (diperbarui oleh JS setiap detik) -->
    <div class="clock-pill" id="clock">--:--</div>

    <!-- Avatar + dropdown -->
    <!-- Klik avatar untuk buka/tutup dropdown menu -->
    <div style="position:relative;">
      <div class="topbar-avatar" onclick="toggleMenu()" id="avatar-btn"
           title="<?= htmlspecialchars($user['nama']) ?>">
        <?= $inisial ?>
      </div>

      <!-- Dropdown: Info user + logout -->
      <div class="topbar-menu" id="topbar-menu">

        <!-- Info user aktif -->
        <div style="padding:.5rem .875rem .625rem;border-bottom:1px solid var(--border);margin-bottom:.25rem;">
          <div style="font-size:.82rem;font-weight:700;color:var(--text-main);">
            <?= htmlspecialchars($user['nama']) ?>
          </div>
          <div style="font-size:.7rem;color:var(--text-muted);margin-top:1px;">
            <?= roleLabel($user['role']) ?>
            <?php if ($user['divisi'] !== '-'): ?>
              · <?= htmlspecialchars($user['divisi']) ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Menu aksi -->
        <?php if (isSuperAdmin()): ?>
          <a href="users.php"   class="menu-item">👥 Kelola User</a>
          <a href="laporan.php" class="menu-item">📋 Laporan</a>
        <?php elseif (isAdminStaff()): ?>
          <a href="laporan.php" class="menu-item">📋 Laporan</a>
        <?php endif; ?>

        <hr class="menu-sep"/>

        <!-- Tombol logout — konfirmasi sebelum logout -->
        <a href="server/logout.php" class="menu-item danger"
           onclick="return confirm('Yakin ingin logout?')">
          🚪 Logout
        </a>

      </div>
    </div><!-- /relative -->

  </div><!-- /topbar-right -->

</header>
