<!-- ============================================================
     includes/scripts.php — JavaScript Global
     Berisi: jam real-time, toggle menu avatar, notif popup,
             auto-hide alert
     ============================================================ -->

<!-- ── Jam Real-time ──────────────────────────────────── -->
<script>
// Memperbarui elemen #clock setiap detik
// Format: HH:MM (ubah format di sini jika perlu)
function updateClock() {
  const now = new Date();
  const p = n => String(n).padStart(2, '0');
  const el = document.getElementById('clock');
  if (el) el.textContent = p(now.getHours()) + ':' + p(now.getMinutes());
}
updateClock();
setInterval(updateClock, 1000);
</script>

<!-- ── Toggle Dropdown Menu Avatar ───────────────────── -->
<script>
// Buka/tutup dropdown menu dari avatar di topbar
function toggleMenu() {
  document.getElementById('topbar-menu').classList.toggle('open');
}
// Klik di luar menu → tutup dropdown
document.addEventListener('click', function(e) {
  const menu = document.getElementById('topbar-menu');
  const btn  = document.getElementById('avatar-btn');
  if (menu && btn && !menu.contains(e.target) && !btn.contains(e.target)) {
    menu.classList.remove('open');
  }
});
</script>

<!-- ── Auto-hide Alert ────────────────────────────────── -->
<script>
// Alert sukses/error hilang otomatis setelah 4 detik
const alertEl = document.querySelector('.alert');
if (alertEl) {
  setTimeout(() => {
    alertEl.style.transition = 'opacity .4s';
    alertEl.style.opacity = '0';
    setTimeout(() => alertEl.remove(), 400);
  }, 4000);
}
</script>

<!-- ── Notifikasi Popup Kadaluarsa ────────────────────── -->
<!-- Dirender oleh PHP di halaman yang include scripts.php -->
<!-- $expNotif harus di-set sebelum include scripts.php    -->
<?php if (!empty($expNotif) && count($expNotif) > 0): ?>
<div id="notif-exp">
  <div class="notif-hdr">
    ⚠️ <?= count($expNotif) ?> obat segera kadaluarsa
    <!-- Klik × untuk tutup popup tanpa navigasi -->
    <button class="notif-close" onclick="document.getElementById('notif-exp').remove()">×</button>
  </div>
  <div class="notif-body">
    <?php foreach ($expNotif as $e): ?>
    <div class="notif-row">
      <strong><?= htmlspecialchars($e['nama']) ?></strong><br>
      <span style="font-size:.72rem;color:<?= $e['sisa_hari'] <= 0 ? 'var(--danger)' : ($e['sisa_hari'] <= 7 ? 'var(--warn)' : 'var(--text-muted)') ?>;">
        <?= $e['sisa_hari'] <= 0
            ? '❌ Kadaluarsa ' . abs($e['sisa_hari']) . ' hari lalu'
            : '⏰ ' . $e['sisa_hari'] . ' hari lagi'
        ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>
  <div style="padding:.5rem .875rem .625rem;">
    <a href="expired.php" style="font-size:.75rem;color:var(--primary-dark);font-weight:700;text-decoration:none;">
      Lihat semua →
    </a>
  </div>
</div>
<?php endif; ?>
