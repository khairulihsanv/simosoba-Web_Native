<?php
// ============================================================
// login.php — Halaman Login & Register
// session_start() WAJIB di baris paling atas sebelum HTML
// ============================================================
require_once 'server/session_handler.php';
session_start();
include 'server/koneksi.php';

// Sudah login → langsung ke dashboard
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit(); }

// Tab aktif: 'login' atau 'register'
// Ditentukan dari URL ?tab=register setelah redirect error
$tab = $_GET['tab'] ?? 'login';

// Pesan error
$errLogin = [
    'invalid'  => 'Username atau password salah.',
    'empty'    => 'Semua field wajib diisi.',
];
$errReg = [
    'empty'     => 'Semua field wajib diisi.',
    'short'     => 'Password minimal 6 karakter.',
    'mismatch'  => 'Konfirmasi password tidak cocok.',
    'duplicate' => 'Username sudah digunakan, coba yang lain.',
    'db'        => 'Gagal menyimpan data, coba lagi.',
];
$errKey = $_GET['error']   ?? '';
$sucKey = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SiMoSoBa — Masuk</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700&display=swap"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>

  <style>
    /* ============================================================
       LOGIN PAGE CSS
       Untuk mengubah background → cari komentar "BG GRADIENT"
       Untuk mengubah warna card glass → cari komentar "GLASS CARD"
       Untuk mengubah warna tombol login → cari komentar "BTN LOGIN"
       Untuk mengubah animasi → cari komentar "ANIMASI"
       ============================================================ */

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      padding: 2rem 0; /* Tambahkan padding agar tidak mentok di HP */
      -webkit-font-smoothing: antialiased;
    }

    /* ── BG GRADIENT ─────────────────────────────────────────
       Untuk ganti warna background, ubah nilai di bawah.
       Contoh ganti ke biru: #1e3a8a → #0ea5e9
       Mengedit 3 nilai warna berarti 3 titik gradien berbeda */
    body::before {
      content: '';
      position: fixed; inset: 0;
      /* Gradien utama: hijau tua ke hijau mint */
      background:
        radial-gradient(ellipse 80% 60% at 20% 30%, #00a878 0%, transparent 60%),
        radial-gradient(ellipse 60% 80% at 80% 70%, #00c896 0%, transparent 55%),
        linear-gradient(135deg, #005c40 0%, #00915e 40%, #00c896 100%);
      z-index: -2;
    }

    /* ── DEKORASI Y2K ────────────────────────────────────────
       Lingkaran-lingkaran dekoratif di background.
       Untuk hilangkan → hapus ::after dan .orb-* */
    body::after {
      content: '';
      position: fixed; inset: 0;
      /* Pola grid halus ala Y2K */
      background-image:
        linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
      background-size: 40px 40px;
      z-index: -1;
    }

    /* Blob dekoratif (bisa dihapus jika tidak suka) */
    .orb {
      position: fixed; border-radius: 50%;
      filter: blur(60px); opacity: .35; z-index: -1;
      pointer-events: none;
      /* ANIMASI: blob bergerak perlahan (min-animasi, tidak tabrakan JS) */
      animation: drift 8s ease-in-out infinite alternate;
    }
    /* Untuk ganti warna blob → ubah background di masing-masing .orb */
    .orb-1 { width:320px;height:320px;background:#a8ff3e;top:-80px;right:-60px; animation-delay:0s; }
    .orb-2 { width:200px;height:200px;background:#7dd3fc;bottom:10%;left:-40px; animation-delay:-3s; }
    .orb-3 { width:150px;height:150px;background:#ffffff;bottom:5%;right:15%;  animation-delay:-6s; opacity:.15; }

    @keyframes drift {
      /* ANIMASI: blob melayang. Ubah translate nilai untuk range gerak */
      from { transform: translate(0, 0) scale(1); }
      to   { transform: translate(20px, 30px) scale(1.05); }
    }

    /* ── GLASS CARD ──────────────────────────────────────────
       Card utama dengan efek kaca (glassmorphism).
       Untuk ubah transparansi: ubah rgba keempat angka (0.18 = 18% opak)
       Untuk ubah blur: ubah backdrop-filter nilai blur()
       Untuk ubah lebar: ubah max-width */
    .glass-card {
      width: 100%;
      max-width: 420px;     /* ← lebar maksimum card login */
      margin: 1.5rem;
      background: rgba(255, 255, 255, 0.18);  /* ← transparansi glass */
      backdrop-filter: blur(24px);             /* ← kekuatan blur glass */
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255, 255, 255, 0.35);
      border-radius: 24px;
      padding: 2rem 1.875rem;
      box-shadow:
        0 8px 32px rgba(0, 0, 0, .15),
        inset 0 1px 0 rgba(255,255,255,.4);
      /* ANIMASI: card muncul dari bawah saat halaman load */
      animation: cardUp .5s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes cardUp {
      from { opacity:0; transform: translateY(30px) scale(.95); }
      to   { opacity:1; transform: none; }
    }

    /* Judul brand di atas card */
    .brand {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    .brand-icon {
      font-size: 2.5rem;
      /* ANIMASI: ikon capsul berputar perlahan */
      display: inline-block;
      animation: spin 6s linear infinite;
    }
    @keyframes spin {
      /* Rotasi lambat. Ubah 360deg ke 0deg untuk balik arah */
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }
    .brand-name {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.6rem; font-weight: 700;
      color: #fff; letter-spacing: -1px;
      display: block; margin-top: .25rem;
      text-shadow: 0 2px 8px rgba(0,0,0,.2);
    }
    .brand-tagline {
      font-size: .75rem; color: rgba(255,255,255,.7);
      font-weight: 500; letter-spacing: .5px; margin-top: 2px;
    }

    /* ── Tab Login / Register ────────────────────────────
       Untuk tambah tab baru → tambah tab-btn + form section
       dan update logika JS toggleTab() */
    .tabs {
      display: flex; gap: 4px;
      background: rgba(0,0,0,.15);
      border-radius: 12px; padding: 4px;
      margin-bottom: 1.5rem;
    }
    .tab-btn {
      flex: 1; padding: .5rem;
      border: none; border-radius: 8px;
      font-family: inherit; font-size: .82rem; font-weight: 700;
      cursor: pointer;
      transition: all .2s;
      color: rgba(255,255,255,.7);
      background: transparent;
    }
    /* Tab aktif: putih solid */
    .tab-btn.active {
      background: rgba(255,255,255,.95);
      color: #005c40;
      box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }

    /* ── Form Section ────────────────────────────────────
       Setiap form (login/register) ada di .form-section
       Tersembunyi dengan display:none, ditampilkan via JS */
    .form-section { display: none; }
    .form-section.show { display: block; }

    /* Label & Input */
    .form-group { margin-bottom: 1rem; }
    .form-label {
      display: block; font-size: .74rem; font-weight: 700;
      color: rgba(255,255,255,.85); margin-bottom: 5px; letter-spacing: .3px;
    }
    .input-wrap { position: relative; }
    .input-wrap .ico {
      position: absolute; left: 11px; top: 50%;
      transform: translateY(-50%);
      color: rgba(255,255,255,.5); font-size: .95rem; pointer-events: none;
    }
    /* ── INPUT FIELD ──────────────────────────────────────
       Untuk ubah warna background input → ubah rgba di background
       Untuk ubah border saat focus → ubah border-color di :focus */
    .form-ctrl {
      width: 100%;
      padding: .65rem .875rem .65rem 2.25rem;
      background: rgba(255,255,255,.15);  /* ← transparansi input */
      border: 1.5px solid rgba(255,255,255,.3);
      border-radius: 10px;
      font-family: inherit; font-size: .875rem;
      color: #fff; outline: none;
      transition: border-color .2s, background .2s;
    }
    .form-ctrl::placeholder { color: rgba(255,255,255,.45); }
    .form-ctrl:focus {
      border-color: rgba(255,255,255,.8);
      background: rgba(255,255,255,.22);
    }

    /* Tombol lihat password */
    .btn-eye {
      position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: rgba(255,255,255,.5);
      cursor: pointer; font-size: .95rem; padding: 0; transition: color .15s;
    }
    .btn-eye:hover { color: rgba(255,255,255,.9); }

    /* ── BTN LOGIN / REGISTER ────────────────────────────
       Untuk ganti warna tombol → ubah background di .btn-submit
       Untuk ganti warna hover → ubah background di .btn-submit:hover */
    .btn-submit {
      width: 100%; padding: .75rem;
      background: #fff;           /* ← warna tombol (putih di atas hijau) */
      color: #005c40;             /* ← warna teks tombol */
      border: none; border-radius: 12px;
      font-family: inherit; font-size: .92rem; font-weight: 800;
      cursor: pointer; margin-top: .5rem;
      display: flex; align-items: center; justify-content: center; gap: 7px;
      transition: background .2s, transform .1s, box-shadow .2s;
      box-shadow: 0 4px 16px rgba(0,0,0,.15);
    }
    .btn-submit:hover {
      background: #e0faf3;        /* ← warna hover tombol */
      box-shadow: 0 6px 20px rgba(0,0,0,.2);
    }
    .btn-submit:active { transform: scale(.97); }

    /* Alert error/sukses */
    .alert {
      padding: .625rem .875rem;
      border-radius: 10px; font-size: .8rem; font-weight: 600;
      margin-bottom: 1rem;
      display: flex; align-items: center; gap: 7px;
      animation: alertIn .3s ease;
    }
    @keyframes alertIn { from{opacity:0;transform:translateY(-5px)} to{opacity:1;transform:none} }
    .alert-err { background: rgba(239,68,68,.25); color: #fff; border:1px solid rgba(239,68,68,.4); }
    .alert-ok  { background: rgba(0,200,150,.25); color: #fff; border:1px solid rgba(0,200,150,.4); }
  </style>
</head>
<body>

<!-- Blob dekoratif Y2K (hapus 3 div ini jika tidak suka animasi blob) -->
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<!-- ── Glass Card Utama ─────────────────────────────── -->
<div class="glass-card">

  <!-- Brand -->
  <div class="brand">
    <span class="brand-icon">💊</span>
    <span class="brand-name">SiMoSoBa</span>
    <div class="brand-tagline">Sistem Monitoring Stok Obat</div>
  </div>

  <!-- Tab: Login | Register -->
  <div class="tabs">
    <button class="tab-btn <?= $tab==='login'?'active':'' ?>"
            onclick="switchTab('login')" id="tab-login">
      Masuk
    </button>
    <button class="tab-btn <?= $tab==='register'?'active':'' ?>"
            onclick="switchTab('register')" id="tab-register">
      Daftar
    </button>
  </div>

  <!-- ══ FORM LOGIN ═══════════════════════════════════════ -->
  <div class="form-section <?= $tab==='login'?'show':'' ?>" id="sec-login">

    <!-- Alert error (muncul jika ada ?error=... di URL) -->
    <?php if ($tab==='login' && $errKey && isset($errLogin[$errKey])): ?>
      <div class="alert alert-err"><i class="bi bi-x-circle-fill"></i><?= $errLogin[$errKey] ?></div>
    <?php endif; ?>
    <?php if ($sucKey === 'registered'): ?>
      <div class="alert alert-ok"><i class="bi bi-check-circle-fill"></i>Akun berhasil dibuat! Silakan masuk.</div>
    <?php endif; ?>
    <?php if (isset($_GET['logout'])): ?>
      <div class="alert alert-ok"><i class="bi bi-check-circle-fill"></i>Berhasil logout.</div>
    <?php endif; ?>

    <!-- Form action ke server/prosesLogin.php -->
    <form action="server/prosesLogin.php" method="POST" autocomplete="off">

      <div class="form-group">
        <label class="form-label">Username</label>
        <div class="input-wrap">
          <i class="bi bi-person ico"></i>
          <input type="text" name="username" class="form-ctrl"
                 placeholder="Masukkan username" required
                 value="<?= htmlspecialchars($_GET['username'] ?? '') ?>"/>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <i class="bi bi-lock ico"></i>
          <input type="password" id="pw-login" name="password" class="form-ctrl"
                 placeholder="Masukkan password" required/>
          <!-- Tombol show/hide password -->
          <button type="button" class="btn-eye" onclick="togglePw('pw-login','eye-login')">
            <i class="bi bi-eye" id="eye-login"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-submit">
        <i class="bi bi-box-arrow-in-right"></i> Masuk
      </button>

    </form>
  </div><!-- /sec-login -->

  <!-- ══ FORM REGISTER ════════════════════════════════════ -->
  <div class="form-section <?= $tab==='register'?'show':'' ?>" id="sec-register">

    <?php if ($tab==='register' && $errKey && isset($errReg[$errKey])): ?>
      <div class="alert alert-err"><i class="bi bi-x-circle-fill"></i><?= $errReg[$errKey] ?></div>
    <?php endif; ?>

    <!-- Form action ke server/prosesRegister.php -->
    <!-- Role default yang didapat: 'user' (bisa di-upgrade oleh super_admin) -->
    <form action="server/prosesRegister.php" method="POST" autocomplete="off">

      <div class="form-group">
        <label class="form-label">Nama Lengkap</label>
        <div class="input-wrap">
          <i class="bi bi-person-badge ico"></i>
          <input type="text" name="nama" class="form-ctrl"
                 placeholder="Nama lengkap kamu" required/>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Username</label>
        <div class="input-wrap">
          <i class="bi bi-at ico"></i>
          <input type="text" name="username" class="form-ctrl"
                 placeholder="Buat username unik" required/>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Divisi / Apotek</label>
        <div class="input-wrap">
          <i class="bi bi-building ico"></i>
          <input type="text" name="divisi" class="form-ctrl"
                 placeholder="Contoh: Apotek A"/>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Password <span style="color:rgba(255,255,255,.5);font-weight:400;">(min. 6 karakter)</span></label>
        <div class="input-wrap">
          <i class="bi bi-lock ico"></i>
          <input type="password" id="pw-reg" name="password" class="form-ctrl"
                 placeholder="Buat password" required minlength="6"/>
          <button type="button" class="btn-eye" onclick="togglePw('pw-reg','eye-reg')">
            <i class="bi bi-eye" id="eye-reg"></i>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Konfirmasi Password</label>
        <div class="input-wrap">
          <i class="bi bi-lock-fill ico"></i>
          <input type="password" name="konfirmasi" class="form-ctrl"
                 placeholder="Ulangi password" required/>
        </div>
      </div>

      <!-- Info: role default setelah register -->
      <div style="font-size:.71rem;color:rgba(255,255,255,.55);margin-bottom:.875rem;line-height:1.5;">
        ℹ️ Akun baru mendapat akses <strong style="color:rgba(255,255,255,.8);">User</strong>.
        Super Admin dapat mengubah role setelah registrasi.
      </div>

      <button type="submit" class="btn-submit">
        <i class="bi bi-person-plus-fill"></i> Buat Akun
      </button>

    </form>
  </div><!-- /sec-register -->

</div><!-- /glass-card -->

<script>
/* ── switchTab(): Ganti tab Login ↔ Register ─────────────
   Mengubah class active pada tombol tab dan
   class show pada form section yang sesuai
   Untuk tambah tab baru: tambah case di fungsi ini */
function switchTab(name) {
  ['login','register'].forEach(t => {
    document.getElementById('tab-' + t).classList.toggle('active', t === name);
    document.getElementById('sec-'  + t).classList.toggle('show',   t === name);
  });
}

/* ── togglePw(): Show/hide password ─────────────────────
   id = ID elemen input password
   iconId = ID elemen <i> ikon mata */
function togglePw(id, iconId) {
  const inp  = document.getElementById(id);
  const icon = document.getElementById(iconId);
  const hide = inp.type === 'password';
  inp.type   = hide ? 'text' : 'password';
  icon.className = hide ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>

</body>
</html>
