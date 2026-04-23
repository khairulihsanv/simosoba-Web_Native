<?php
// ============================================================
// includes/bottom_nav.php — Navigasi Bawah Mengambang
// Menampilkan menu sesuai role user yang sedang login
// Untuk tambah menu baru: tambah array di $navItems sesuai role
// ============================================================

$halaman = basename($_SERVER['PHP_SELF']);

// ── Hitung badge notifikasi kadaluarsa ──────────────────
// Badge merah muncul di icon Kadaluarsa jika ada obat < 30 hari
$fDiv      = getDivisiFilter();
$badgeExp  = (int)mysqli_fetch_assoc(
    mysqli_query($koneksi,
        "SELECT COUNT(*) AS n FROM obat
         WHERE exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND $fDiv"
    )
)['n'];

// ── Definisi menu per role ───────────────────────────────
// Setiap item: [href, icon (emoji), label, badge?]
// Untuk tambah menu → tambah baris baru di array role yang sesuai
// Untuk ganti icon → ganti emoji di kolom kedua

$menus = [];

if (isSuperAdmin()) {
    // Super Admin: fokus ke manajemen user & overview
    $menus = [
        ['dashboard.php', '🏠', 'Home',    ''],
        ['users.php',     '👥', 'Users',   ''],
        ['stok.php',      '💊', 'Stok',    ''],
        ['expired.php',   '⏰', 'Exp.',    $badgeExp ?: ''],
        ['laporan.php',   '📋', 'Laporan', ''],
    ];
} elseif (isAdminStaff()) {
    // Admin Staff: laporan + kadaluarsa adalah menu utama
    $menus = [
        ['dashboard.php', '🏠', 'Home',    ''],
        ['laporan.php',   '📋', 'Laporan', ''],
        ['expired.php',   '⏰', 'Exp.',    $badgeExp ?: ''],
        ['stok.php',      '💊', 'Stok',    ''],
    ];
} elseif (isStaff()) {
    // Staff: hanya input/output stok
    $menus = [
        ['dashboard.php', '🏠', 'Home',   ''],
        ['stok.php',      '💊', 'Stok',   ''],
        ['expired.php',   '⏰', 'Exp.',   $badgeExp ?: ''],
    ];
} else {
    // User biasa: hanya bisa lihat
    $menus = [
        ['dashboard.php', '🏠', 'Home',   ''],
        ['expired.php',   '⏰', 'Exp.',   $badgeExp ?: ''],
    ];
}
?>

<!-- ── Floating Bottom Nav ────────────────────────────── -->
<nav class="bottom-nav" role="navigation" aria-label="Navigasi utama">

  <?php foreach ($menus as [$href, $icon, $label, $badge]): ?>
    <a href="<?= $href ?>"
       class="nav-item <?= $halaman === $href ? 'active' : '' ?>"
       aria-label="<?= $label ?>">

      <span class="nav-icon"><?= $icon ?></span>
      <span><?= $label ?></span>

      <!-- Badge merah (angka notif) — hanya tampil jika $badge > 0 -->
      <?php if ($badge): ?>
        <span class="nav-badge"><?= $badge ?></span>
      <?php endif; ?>

    </a>
  <?php endforeach; ?>

</nav>
