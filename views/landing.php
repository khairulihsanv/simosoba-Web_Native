<?php
if (!defined('BASE_PATH')) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SiMoSoBa – Sistem Monitoring Stok Obat Cerdas. Kelola inventori farmasi dengan prediksi AI, laporan otomatis, dan alert real-time.">
    <title>SiMoSoBa – Manajemen Stok Obat Cerdas</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Theme init -->
    <script>
        (function() {
            const saved = localStorage.getItem('simo-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', saved || (prefersDark ? 'dark' : 'light'));
        })();
    </script>

    <style>
        /* ── Design Tokens ────────────────────────────────────── */
        :root {
            --accent:       #6366f1;
            --accent-h:     #4f46e5;
            --accent-light: #eef2ff;
            --green:        #22c55e;
            --amber:        #f59e0b;
            --bg:           #f8faff;
            --bg-card:      #ffffff;
            --text:         #0f172a;
            --text-2:       #475569;
            --text-3:       #94a3b8;
            --border:       #e2e8f0;
            --ease:         cubic-bezier(.4,0,.2,1);
        }
        [data-theme="dark"] {
            --bg:       #0b0d17;
            --bg-card:  #141826;
            --text:     #f1f5f9;
            --text-2:   #94a3b8;
            --text-3:   #64748b;
            --border:   #1e2a3a;
            --accent-light: #1e2040;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            transition: background .3s, color .3s;
        }
        a { text-decoration: none; color: inherit; }

        /* ── Navbar ──────────────────────────────────────────── */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(248,250,255,.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            height: 68px;
            display: flex; align-items: center;
            transition: background .3s;
        }
        [data-theme="dark"] .navbar {
            background: rgba(11,13,23,.88);
        }
        .navbar-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 0 24px;
            display: flex; align-items: center; justify-content: space-between;
            width: 100%;
        }
        .nav-logo {
            display: flex; align-items: center; gap: 10px;
        }
        .nav-logo-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(99,102,241,.3);
        }
        .nav-brand {
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem; font-weight: 800;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .nav-links {
            display: flex; align-items: center; gap: 8px;
        }
        .nav-link-item {
            padding: 8px 16px; border-radius: 9px;
            font-size: .875rem; font-weight: 500;
            color: var(--text-2);
            transition: all .2s;
        }
        .nav-link-item:hover { background: var(--accent-light); color: var(--accent); }
        .btn-nav-login {
            padding: 9px 20px; border-radius: 10px;
            background: var(--accent); color: #fff;
            font-size: .875rem; font-weight: 600;
            border: none; cursor: pointer;
            transition: all .2s;
            box-shadow: 0 2px 8px rgba(99,102,241,.3);
        }
        .btn-nav-login:hover { background: var(--accent-h); transform: translateY(-1px); }
        .btn-theme-nav {
            width: 36px; height: 36px;
            border-radius: 9px; border: 1px solid var(--border);
            background: var(--bg-card); color: var(--text-2);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1rem; transition: all .2s;
        }
        .btn-theme-nav:hover { color: var(--accent); border-color: var(--accent); }

        /* ── Hero Section ────────────────────────────────────── */
        .hero {
            padding: 140px 24px 80px;
            position: relative; overflow: hidden;
        }
        .hero-bg {
            position: absolute; inset: 0; pointer-events: none;
            overflow: hidden;
        }
        .hero-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .35;
        }
        .hero-blob-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, #6366f1, #8b5cf6);
            top: -200px; right: -100px;
        }
        .hero-blob-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #22c55e, #06b6d4);
            bottom: -100px; left: -100px;
        }
        .hero-inner {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 60px; align-items: center;
            position: relative; z-index: 1;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 6px 14px; border-radius: 99px;
            background: var(--accent-light);
            border: 1px solid rgba(99,102,241,.3);
            font-size: .78rem; font-weight: 600; color: var(--accent);
            margin-bottom: 20px;
            animation: fadeUp .6s var(--ease) both;
        }
        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 3.5rem; font-weight: 900;
            line-height: 1.1; color: var(--text);
            margin-bottom: 20px;
            animation: fadeUp .6s .1s var(--ease) both;
        }
        .hero-title .gradient {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-desc {
            font-size: 1.05rem; color: var(--text-2); line-height: 1.7;
            margin-bottom: 32px;
            animation: fadeUp .6s .2s var(--ease) both;
        }
        .hero-btns {
            display: flex; gap: 12px; flex-wrap: wrap;
            animation: fadeUp .6s .3s var(--ease) both;
        }
        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px; border-radius: 12px;
            background: var(--accent); color: #fff;
            font-size: 1rem; font-weight: 700; border: none; cursor: pointer;
            box-shadow: 0 4px 24px rgba(99,102,241,.4);
            transition: all .25s;
        }
        .btn-hero-primary:hover {
            background: var(--accent-h);
            box-shadow: 0 8px 32px rgba(99,102,241,.5);
            transform: translateY(-2px);
        }
        .btn-hero-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px; border-radius: 12px;
            background: var(--bg-card); color: var(--text);
            font-size: 1rem; font-weight: 600;
            border: 2px solid var(--border); cursor: pointer;
            transition: all .25s;
        }
        .btn-hero-secondary:hover { border-color: var(--accent); color: var(--accent); }

        /* Hero Visual */
        .hero-visual {
            position: relative;
            animation: fadeUp .6s .2s var(--ease) both;
        }
        .hero-dashboard-card {
            background: var(--bg-card);
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
            padding: 24px;
            position: relative;
        }
        .hero-stat-row {
            display: grid; grid-template-columns: repeat(3,1fr); gap: 12px;
            margin-bottom: 20px;
        }
        .hero-stat-mini {
            background: var(--bg);
            border-radius: 12px; border: 1px solid var(--border);
            padding: 14px;
            text-align: center;
        }
        .hero-stat-mini .val {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem; font-weight: 800; color: var(--text);
        }
        .hero-stat-mini .lbl {
            font-size: .68rem; color: var(--text-3); margin-top: 3px;
            text-transform: uppercase; letter-spacing: .4px;
        }
        .hero-chart-bar {
            display: flex; align-items: flex-end; gap: 8px;
            height: 80px;
        }
        .h-bar {
            flex: 1; border-radius: 6px 6px 0 0;
            transition: height .5s var(--ease);
        }
        .hero-floating-badge {
            position: absolute;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,.1);
            display: flex; align-items: center; gap: 8px;
            font-size: .78rem; font-weight: 600; color: var(--text);
            white-space: nowrap;
            animation: float 4s ease-in-out infinite;
        }
        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }
        .badge-dot { width: 8px; height: 8px; border-radius: 50%; }

        /* ── Stats Section ───────────────────────────────────── */
        .stats-section {
            padding: 64px 24px;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }
        .stats-inner {
            max-width: 1000px; margin: 0 auto;
            display: grid; grid-template-columns: repeat(4,1fr);
            gap: 40px; text-align: center;
        }
        .stat-big-val {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem; font-weight: 900;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-big-label {
            font-size: .84rem; color: var(--text-2); margin-top: 6px;
        }

        /* ── Features ────────────────────────────────────────── */
        .features-section {
            padding: 96px 24px;
        }
        .section-label {
            text-align: center;
            font-size: .78rem; font-weight: 700;
            letter-spacing: 1px; text-transform: uppercase;
            color: var(--accent); margin-bottom: 12px;
        }
        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.4rem; font-weight: 800;
            text-align: center; color: var(--text);
            margin-bottom: 12px;
        }
        .section-desc {
            text-align: center; color: var(--text-2);
            font-size: 1rem; max-width: 560px;
            margin: 0 auto 56px;
            line-height: 1.7;
        }
        .features-grid {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .feature-card {
            background: var(--bg-card);
            border-radius: 20px; border: 1px solid var(--border);
            padding: 28px;
            transition: all .25s;
            cursor: default;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(99,102,241,.12);
            border-color: rgba(99,102,241,.3);
        }
        .feature-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 18px;
        }
        .feature-card h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem; font-weight: 700;
            color: var(--text); margin-bottom: 10px;
        }
        .feature-card p {
            font-size: .875rem; color: var(--text-2); line-height: 1.7;
        }

        /* ── CTA ─────────────────────────────────────────────── */
        .cta-section {
            padding: 96px 24px;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
        }
        .cta-inner {
            max-width: 700px; margin: 0 auto; text-align: center;
        }
        .cta-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.6rem; font-weight: 900;
            color: var(--text); margin-bottom: 16px;
        }
        .cta-desc {
            font-size: 1rem; color: var(--text-2);
            margin-bottom: 36px; line-height: 1.7;
        }
        .cta-btns {
            display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;
        }

        /* ── Footer ──────────────────────────────────────────── */
        .landing-footer {
            padding: 40px 24px;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 16px;
            max-width: 1200px; margin: 0 auto;
        }
        .footer-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 700; color: var(--text);
        }
        .footer-copy {
            font-size: .8rem; color: var(--text-3);
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: none; }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .hero-inner { grid-template-columns: 1fr; text-align: center; }
            .hero-btns { justify-content: center; }
            .hero-visual { display: none; }
            .stats-inner { grid-template-columns: repeat(2, 1fr); }
            .features-grid { grid-template-columns: 1fr 1fr; }
            .hero-title { font-size: 2.6rem; }
        }

        @media (max-width: 600px) {
            .features-grid { grid-template-columns: 1fr; }
            .stats-inner { grid-template-columns: 1fr 1fr; gap: 24px; }
            .hero-title { font-size: 2rem; }
            .nav-links .nav-link-item { display: none; }
        }
    </style>
</head>
<body>

<!-- ══ NAVBAR ══════════════════════════════════════════════════ -->
<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="navbar-inner">
        <a href="<?= BASE_URL ?>/" class="nav-logo" aria-label="SiMoSoBa Home">
            <div class="nav-logo-icon" aria-hidden="true">
                <i class="bi bi-capsule-pill"></i>
            </div>
            <span class="nav-brand">SiMoSoBa</span>
        </a>

        <div class="nav-links">
            <a href="#features" class="nav-link-item">Features</a>
            <a href="#stats" class="nav-link-item">About</a>

            <button class="btn-theme-nav" onclick="toggleTheme()" aria-label="Toggle dark mode" id="theme-btn">
                <i class="bi bi-moon-stars" id="theme-nav-icon"></i>
            </button>

            <a href="<?= BASE_URL ?>/?page=login" class="btn-nav-login" aria-label="Login to SiMoSoBa">
                <i class="bi bi-box-arrow-in-right"></i> Masuk
            </a>
        </div>
    </div>
</nav>

<!-- ══ HERO ════════════════════════════════════════════════════ -->
<section class="hero" aria-label="Hero section">
    <div class="hero-bg" aria-hidden="true">
        <div class="hero-blob hero-blob-1"></div>
        <div class="hero-blob hero-blob-2"></div>
    </div>

    <div class="hero-inner">
        <div class="hero-text">
            <div class="hero-badge">
                <i class="bi bi-stars"></i>
                Sistem Farmasi Generasi Baru
            </div>
            <h1 class="hero-title">
                Kelola Stok Obat<br>
                <span class="gradient">Lebih Cerdas.</span>
            </h1>
            <p class="hero-desc">
                SiMoSoBa membantu apotek & klinik memantau inventori obat secara real-time,
                memprediksi kebutuhan dengan AI, dan menghasilkan laporan PDF bulanan otomatis.
            </p>
            <div class="hero-btns">
                <a href="<?= BASE_URL ?>/?page=login" class="btn-hero-primary" aria-label="Start managing stock">
                    <i class="bi bi-rocket-takeoff"></i>
                    Mulai Sekarang
                </a>
                <a href="#features" class="btn-hero-secondary" aria-label="Learn more about features">
                    <i class="bi bi-play-circle"></i>
                    Pelajari Fitur
                </a>
            </div>
        </div>

        <!-- Dashboard Preview -->
        <div class="hero-visual" aria-hidden="true">
            <div class="hero-dashboard-card">
                <div style="font-family:'Outfit',sans-serif;font-size:.9rem;font-weight:700;color:var(--text);margin-bottom:16px">
                    <i class="bi bi-grid-1x2" style="color:var(--accent)"></i>
                    Dashboard Overview
                </div>
                <div class="hero-stat-row">
                    <div class="hero-stat-mini">
                        <div class="val" style="color:#6366f1">248</div>
                        <div class="lbl">Medications</div>
                    </div>
                    <div class="hero-stat-mini">
                        <div class="val" style="color:#22c55e">97%</div>
                        <div class="lbl">In Stock</div>
                    </div>
                    <div class="hero-stat-mini">
                        <div class="val" style="color:#f59e0b">3</div>
                        <div class="lbl">Low Alert</div>
                    </div>
                </div>
                <div style="font-size:.72rem;color:var(--text-3);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;font-weight:700">Stock Movement (7 days)</div>
                <div class="hero-chart-bar">
                    <?php
                    $bars = [40,65,50,80,60,90,75];
                    $colors = ['#6366f140','#6366f160','#6366f180','#6366f1a0','#6366f1c0','#6366f1','#6366f1'];
                    foreach($bars as $i => $h): ?>
                    <div class="h-bar" style="height:<?= $h ?>%;background:<?= $colors[$i] ?>;min-height:8px"></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Floating badges -->
            <div class="hero-floating-badge" style="top:-20px;left:-30px">
                <span class="badge-dot" style="background:#22c55e"></span>
                Stok diperbarui real-time
            </div>
            <div class="hero-floating-badge" style="bottom:-16px;right:-20px">
                <i class="bi bi-file-earmark-pdf" style="color:#ef4444"></i>
                PDF Report siap diekspor
            </div>
        </div>
    </div>
</section>

<!-- ══ STATS ════════════════════════════════════════════════════ -->
<section class="stats-section" id="stats" aria-label="Statistics">
    <div class="stats-inner">
        <div>
            <div class="stat-big-val" data-target="500">0+</div>
            <div class="stat-big-label">Jenis Obat Terkelola</div>
        </div>
        <div>
            <div class="stat-big-val" data-target="98">0%</div>
            <div class="stat-big-label">Akurasi Prediksi Stok</div>
        </div>
        <div>
            <div class="stat-big-val" data-target="70">0%</div>
            <div class="stat-big-label">Efisiensi Administrasi</div>
        </div>
        <div>
            <div class="stat-big-val" data-target="24">0/7</div>
            <div class="stat-big-label">Monitoring Real-time</div>
        </div>
    </div>
</section>

<!-- ══ FEATURES ═════════════════════════════════════════════════ -->
<section class="features-section" id="features" aria-label="Features">
    <div class="section-label">Keunggulan Platform</div>
    <h2 class="section-title">Semua yang Anda Butuhkan</h2>
    <p class="section-desc">Satu platform lengkap untuk manajemen inventori farmasi modern dengan teknologi terkini.</p>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon" style="background:#eef2ff;color:#6366f1">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <h3>Analisis & Prediksi AI</h3>
            <p>Grafik prediksi kebutuhan stok 30 hari ke depan menggunakan linear regression berbasis data historis penggunaan.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background:#dcfce7;color:#22c55e">
                <i class="bi bi-box-seam"></i>
            </div>
            <h3>Inventory CRUD + Scanner</h3>
            <p>Kelola obat lengkap dengan barcode/QR scanner langsung dari kamera. Tambah, edit, hapus dengan mudah.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background:#fef3c7;color:#f59e0b">
                <i class="bi bi-bell-fill"></i>
            </div>
            <h3>Smart Alerts</h3>
            <p>Notifikasi otomatis saat stok mendekati batas minimum atau obat hampir kadaluarsa. Tidak ada yang terlewat.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background:#dbeafe;color:#3b82f6">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <h3>Manajemen Transaksi</h3>
            <p>Catat mutasi stok masuk dan keluar secara akurat. Riwayat transaksi lengkap dengan filter dan export CSV.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background:#ede9fe;color:#8b5cf6">
                <i class="bi bi-truck"></i>
            </div>
            <h3>Manajemen Supplier</h3>
            <p>Kelola data supplier dengan kontak, kategori, dan relasi ke produk. Mudah dihubungi saat stok menipis.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background:#fee2e2;color:#ef4444">
                <i class="bi bi-file-earmark-pdf"></i>
            </div>
            <h3>Laporan PDF Bulanan</h3>
            <p>Generate laporan komprehensif setiap bulan: grafik, tabel, dan ringkasan inventori dalam format PDF siap cetak.</p>
        </div>
    </div>
</section>

<!-- ══ CTA ══════════════════════════════════════════════════════ -->
<section class="cta-section" aria-label="Call to action">
    <div class="cta-inner">
        <div class="hero-badge" style="margin:0 auto 20px;display:inline-flex">
            <i class="bi bi-lightning-charge-fill"></i>
            Mulai Gratis, Tanpa Kartu Kredit
        </div>
        <h2 class="cta-title">Siap Upgrade<br>Manajemen Farmasi Anda?</h2>
        <p class="cta-desc">
            Bergabung dengan ratusan apotek yang sudah menggunakan SiMoSoBa untuk efisiensi operasional yang lebih tinggi.
        </p>
        <div class="cta-btns">
            <a href="<?= BASE_URL ?>/?page=login&tab=register" class="btn-hero-primary" aria-label="Create account">
                <i class="bi bi-person-plus"></i>
                Daftar Sekarang
            </a>
            <a href="<?= BASE_URL ?>/?page=login" class="btn-hero-secondary" aria-label="Login to dashboard">
                <i class="bi bi-box-arrow-in-right"></i>
                Sudah Punya Akun
            </a>
        </div>
    </div>
</section>

<!-- ══ FOOTER ════════════════════════════════════════════════════ -->
<footer role="contentinfo">
    <div class="landing-footer">
        <div>
            <div class="footer-brand">SiMoSoBa</div>
            <div class="footer-copy">Sistem Monitoring Stok Obat Berbasis Analitik</div>
        </div>
        <div class="footer-copy">
            &copy; <?= date('Y') ?> SiMoSoBa. Hak Cipta Dilindungi.
        </div>
    </div>
</footer>

<script>
/* Theme Toggle */
function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('simo-theme', next);
    const icon = document.getElementById('theme-nav-icon');
    if (icon) icon.className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
}

(function() {
    const saved = localStorage.getItem('simo-theme') || 'light';
    const icon = document.getElementById('theme-nav-icon');
    if (icon) icon.className = saved === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
})();

/* Counter Animation */
function animateCounters() {
    document.querySelectorAll('.stat-big-val[data-target]').forEach(el => {
        const target = parseInt(el.dataset.target);
        const suffix = el.textContent.replace(/[\d.]/g, '');
        let current = 0;
        const step = Math.ceil(target / 40);
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current + suffix;
            if (current >= target) clearInterval(timer);
        }, 30);
    });
}

/* Intersection Observer for counter */
const statsSection = document.getElementById('stats');
if (statsSection) {
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            animateCounters();
            observer.disconnect();
        }
    }, { threshold: .3 });
    observer.observe(statsSection);
}

/* Smooth navbar scroll effect */
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 20) {
        navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,.08)';
    } else {
        navbar.style.boxShadow = 'none';
    }
});
</script>
</body>
</html>
