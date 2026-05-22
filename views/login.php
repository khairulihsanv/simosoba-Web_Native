<?php
// views/login.php
if (!defined('BASE_PATH')) {
    header('Location: ../index.php?page=login');
    exit();
}
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/?page=dashboard');
    exit();
}

$error   = $_SESSION['login_error'] ?? '';
$success = $_SESSION['login_success'] ?? '';
$tab     = $_GET['tab'] ?? 'login'; // 'login' or 'register'
unset($_SESSION['login_error'], $_SESSION['login_success']);
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login atau Register ke SiMoSoBa – Sistem Monitoring Stok Obat Cerdas">
    <title>Login – SiMoSoBa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script>
        (function() {
            const saved = localStorage.getItem('simo-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', saved || (prefersDark ? 'dark' : 'light'));
        })();
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent: #6366f1;
            --accent-dark: #4f46e5;
            --accent-light: #e0e7ff;
            --text-primary: #1e1b4b;
            --text-secondary: #4b5563;
            --text-muted: #9ca3af;
            --bg-card: #ffffff;
            --bg-page: #f8fafc;
            --border: #e5e7eb;
            --input-bg: #f3f4f6;
            --input-focus-bg: #ffffff;
            --red-light: #fef2f2;
            --green-light: #f0fdf4;
            --shadow-card: 0 20px 60px rgba(99,102,241,.12), 0 4px 16px rgba(0,0,0,.06);
        }

        [data-theme="dark"] {
            --accent: #818cf8;
            --accent-dark: #6366f1;
            --accent-light: #1e1b4b;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --bg-card: #1e293b;
            --bg-page: #0f172a;
            --border: #334155;
            --input-bg: #0f172a;
            --input-focus-bg: #1e293b;
            --shadow-card: 0 20px 60px rgba(0,0,0,.4), 0 4px 16px rgba(0,0,0,.3);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            min-height: 100vh;
            background: var(--bg-page);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* ─── Theme Toggle ─── */
        .theme-btn {
            position: fixed; top: 20px; right: 20px;
            width: 42px; height: 42px;
            border-radius: 50%;
            background: var(--bg-card);
            border: 1.5px solid var(--border);
            color: var(--text-secondary);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1.05rem;
            z-index: 10; transition: all .25s;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        .theme-btn:hover { color: var(--accent); border-color: var(--accent); transform: scale(1.05); }

        /* ─── Main card wrapper (split layout) ─── */
        .auth-container {
            width: 100%;
            max-width: 900px;
            background: var(--bg-card);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: var(--shadow-card);
            display: flex;
            min-height: 560px;
        }

        /* ─── Left: Illustration panel ─── */
        .auth-panel-left {
            flex: 0 0 42%;
            background: linear-gradient(145deg, #6366f1 0%, #818cf8 40%, #06b6d4 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 32px;
            position: relative;
            overflow: hidden;
        }

        .auth-panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(6,182,212,.2) 0%, transparent 50%);
        }

        .auth-illustration {
            width: 100%;
            max-width: 280px;
            border-radius: 20px;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 16px 40px rgba(0,0,0,.25));
            transition: transform .4s ease;
        }
        .auth-illustration:hover { transform: scale(1.02) translateY(-4px); }

        .auth-panel-tagline {
            position: relative; z-index: 1;
            margin-top: 28px;
            text-align: center;
        }
        .auth-panel-tagline h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem; font-weight: 800;
            color: #ffffff;
            line-height: 1.3;
            text-shadow: 0 2px 8px rgba(0,0,0,.15);
        }
        .auth-panel-tagline p {
            color: rgba(255,255,255,.82);
            font-size: .85rem;
            margin-top: 8px;
            line-height: 1.6;
        }

        /* Floating decorative dots */
        .dot {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.18);
            animation: float 4s ease-in-out infinite;
        }
        .dot-1 { width: 70px; height: 70px; top: 8%; right: 10%; animation-delay: 0s; }
        .dot-2 { width: 40px; height: 40px; bottom: 15%; left: 8%; animation-delay: 1.2s; }
        .dot-3 { width: 20px; height: 20px; top: 40%; left: 5%; animation-delay: 2.1s; }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        /* ─── Right: Form panel ─── */
        .auth-panel-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            max-height: 700px;
        }

        /* Tabs */
        .auth-tabs {
            display: flex;
            border-bottom: 1.5px solid var(--border);
            flex-shrink: 0;
        }

        .auth-tab {
            flex: 1; padding: 18px 12px;
            text-align: center;
            font-size: .88rem; font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all .2s;
            border: none; background: none;
            position: relative;
            display: flex; align-items: center; justify-content: center; gap: 7px;
        }

        .auth-tab.active { color: var(--accent); }
        .auth-tab.active::after {
            content: '';
            position: absolute; bottom: -1.5px; left: 0; right: 0;
            height: 2.5px; background: var(--accent);
            border-radius: 2px 2px 0 0;
        }
        .auth-tab:hover:not(.active) { color: var(--text-secondary); }

        /* Body */
        .auth-body {
            padding: 36px 40px 32px;
            flex: 1;
        }

        .auth-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem; font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: .84rem; color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* Form */
        .auth-form { display: flex; flex-direction: column; gap: 16px; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }

        .form-label {
            font-size: .82rem; font-weight: 600;
            color: var(--text-secondary);
            display: flex; align-items: center; gap: 4px;
        }
        .req { color: #ef4444; }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); font-size: .92rem;
            pointer-events: none;
        }

        .auth-input, .auth-select {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: .9rem;
            color: var(--text-primary);
            background: var(--input-bg);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            font-family: inherit;
        }

        .auth-input:focus, .auth-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
            background: var(--input-focus-bg);
        }
        .auth-input::placeholder { color: var(--text-muted); }
        .auth-input.error { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.12); }

        .auth-select { appearance: none; cursor: pointer; }
        .select-arrow {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); font-size: .85rem;
            pointer-events: none;
        }

        .pass-toggle {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer; font-size: .9rem;
            background: none; border: none;
            transition: color .2s;
            padding: 4px;
        }
        .pass-toggle:hover { color: var(--accent); }

        /* Submit button */
        .auth-btn {
            width: 100%; padding: 14px;
            border: none; border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: #fff;
            font-size: .94rem; font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all .25s;
            box-shadow: 0 4px 20px rgba(99,102,241,.35);
            margin-top: 8px;
        }
        .auth-btn:hover {
            box-shadow: 0 6px 28px rgba(99,102,241,.5);
            transform: translateY(-1px);
        }
        .auth-btn:active { transform: scale(.98); }
        .auth-btn:disabled { opacity: .65; cursor: not-allowed; transform: none; }

        /* Divider & hints */
        .auth-divider {
            display: flex; align-items: center; gap: 12px;
            font-size: .76rem; color: var(--text-muted); margin: 6px 0 2px;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        .auth-hint {
            text-align: center; font-size: .83rem;
            color: var(--text-muted); margin-top: 10px;
        }
        .auth-hint a {
            color: var(--accent); font-weight: 600;
            text-decoration: underline; text-underline-offset: 2px;
            cursor: pointer;
        }
        .auth-hint a:hover { color: var(--accent-dark); }

        /* Alert banners */
        .alert-auth {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .84rem; font-weight: 500;
            display: flex; align-items: flex-start; gap: 10px;
            margin-bottom: 8px;
        }
        .alert-auth.err {
            background: var(--red-light);
            border: 1px solid #fca5a5;
            color: #b91c1c;
        }
        .alert-auth.ok {
            background: var(--green-light);
            border: 1px solid #86efac;
            color: #15803d;
        }

        /* Tab panel toggling */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* Back to landing */
        .back-link {
            display: flex; align-items: center; gap: 6px;
            font-size: .8rem; color: var(--text-muted);
            justify-content: center; padding: 16px;
            text-decoration: none; transition: color .2s;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }
        .back-link:hover { color: var(--accent); }

        /* Spinner */
        .spinner {
            width: 15px; height: 15px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: inline-block;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive */
        @media (max-width: 660px) {
            .auth-panel-left { display: none; }
            .auth-container { max-width: 440px; border-radius: 20px; }
            .auth-body { padding: 28px 24px 20px; }
        }
    </style>
</head>
<body>

    <!-- Theme Toggle -->
    <button class="theme-btn" onclick="toggleTheme()" aria-label="Toggle dark mode" id="theme-btn">
        <i class="bi bi-moon-stars" id="theme-icon"></i>
    </button>

    <div class="auth-container">

        <!-- ── LEFT PANEL: Illustration ── -->
        <div class="auth-panel-left">
            <div class="dot dot-1"></div>
            <div class="dot dot-2"></div>
            <div class="dot dot-3"></div>

            <img src="<?= BASE_URL ?>/assets/login_illustration.png"
                 alt="SiMoSoBa Pharmacy Illustration"
                 class="auth-illustration"
                 onerror="this.style.display='none'">

            <div class="auth-panel-tagline">
                <h2>Kelola Stok Obat<br>Lebih Cerdas</h2>
                <p>Monitoring inventori farmasi real-time<br>dengan teknologi terkini</p>
            </div>
        </div>

        <!-- ── RIGHT PANEL: Form ── -->
        <div class="auth-panel-right">

            <!-- Tabs -->
            <div class="auth-tabs" role="tablist">
                <button class="auth-tab <?= $tab === 'login' ? 'active' : '' ?>"
                        onclick="switchTab('login')"
                        id="tab-login-btn"
                        role="tab"
                        aria-selected="<?= $tab === 'login' ? 'true' : 'false' ?>"
                        aria-controls="tab-login">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>
                <button class="auth-tab <?= $tab === 'register' ? 'active' : '' ?>"
                        onclick="switchTab('register')"
                        id="tab-register-btn"
                        role="tab"
                        aria-selected="<?= $tab === 'register' ? 'true' : 'false' ?>"
                        aria-controls="tab-register">
                    <i class="bi bi-person-plus"></i> Daftar
                </button>
            </div>

            <!-- Body -->
            <div class="auth-body">

                <!-- Alerts -->
                <?php if ($error): ?>
                <div class="alert-auth err" role="alert" aria-live="polite">
                    <i class="bi bi-exclamation-circle-fill" style="flex-shrink:0;margin-top:1px"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert-auth ok" role="alert" aria-live="polite">
                    <i class="bi bi-check-circle-fill" style="flex-shrink:0;margin-top:1px"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <!-- ── LOGIN PANEL ── -->
                <div class="tab-panel <?= $tab === 'login' ? 'active' : '' ?>" id="tab-login" role="tabpanel" aria-labelledby="tab-login-btn">
                    <h1 class="auth-title">Selamat Datang</h1>
                    <p class="auth-subtitle">Masuk untuk mengelola inventori obat Anda</p>

                    <form class="auth-form" method="POST" action="<?= BASE_URL ?>/api/login.php" id="login-form" novalidate>
                        <input type="hidden" name="action" value="login">

                        <div class="form-group">
                            <label class="form-label" for="login-username">
                                <i class="bi bi-person" style="color:var(--accent)"></i> Username
                            </label>
                            <div class="input-wrap">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" id="login-username" name="username"
                                       class="auth-input" placeholder="Masukkan username atau email"
                                       required autocomplete="username" aria-required="true">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="login-password">
                                <i class="bi bi-lock" style="color:var(--accent)"></i> Password
                            </label>
                            <div class="input-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" id="login-password" name="password"
                                       class="auth-input" placeholder="Masukkan password"
                                       required autocomplete="current-password" aria-required="true">
                                <button type="button" class="pass-toggle"
                                        onclick="togglePass('login-password', this)"
                                        aria-label="Tampilkan/sembunyikan password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="auth-btn" id="login-btn">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Masuk ke Dashboard
                        </button>
                    </form>

                    <div class="auth-divider" style="margin-top:20px">atau</div>
                    <p class="auth-hint">
                        Belum punya akun?
                        <a onclick="switchTab('register')">Daftar sekarang</a>
                    </p>
                </div>

                <!-- ── REGISTER PANEL ── -->
                <div class="tab-panel <?= $tab === 'register' ? 'active' : '' ?>" id="tab-register" role="tabpanel" aria-labelledby="tab-register-btn">
                    <h2 class="auth-title">Buat Akun Baru</h2>
                    <p class="auth-subtitle">Daftarkan klinik Anda untuk memulai</p>

                    <form class="auth-form" method="POST" action="<?= BASE_URL ?>/api/login.php" id="register-form" novalidate>
                        <input type="hidden" name="action" value="register">

                        <div class="form-group">
                            <label class="form-label" for="reg-username">
                                Username <span class="req">*</span>
                            </label>
                            <div class="input-wrap">
                                <i class="bi bi-person-badge input-icon"></i>
                                <input type="text" id="reg-username" name="username"
                                       class="auth-input" placeholder="Buat username (3-30 karakter)"
                                       required autocomplete="username" aria-required="true">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-email">
                                Email <span class="req">*</span>
                            </label>
                            <div class="input-wrap">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" id="reg-email" name="email"
                                       class="auth-input" placeholder="nama@email.com"
                                       required autocomplete="email" aria-required="true">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-klinik">
                                Nama Klinik / Apotek <span class="req">*</span>
                            </label>
                            <div class="input-wrap">
                                <i class="bi bi-hospital input-icon"></i>
                                <input type="text" id="reg-klinik" name="klinik"
                                       class="auth-input" placeholder="Nama klinik atau apotek Anda"
                                       required autocomplete="organization" aria-required="true">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-password">
                                Password <span class="req">*</span>
                            </label>
                            <div class="input-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" id="reg-password" name="password"
                                       class="auth-input" placeholder="Min. 8 karakter"
                                       required minlength="8" autocomplete="new-password" aria-required="true">
                                <button type="button" class="pass-toggle"
                                        onclick="togglePass('reg-password', this)"
                                        aria-label="Tampilkan/sembunyikan password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-confirm">
                                Konfirmasi Password <span class="req">*</span>
                            </label>
                            <div class="input-wrap">
                                <i class="bi bi-shield-lock input-icon"></i>
                                <input type="password" id="reg-confirm" name="confirm_password"
                                       class="auth-input" placeholder="Ulangi password"
                                       required autocomplete="new-password" aria-required="true">
                            </div>
                        </div>

                        <button type="submit" class="auth-btn" id="register-btn">
                            <i class="bi bi-person-check"></i>
                            Buat Akun
                        </button>
                    </form>

                    <p class="auth-hint" style="margin-top:14px">
                        Sudah punya akun?
                        <a onclick="switchTab('login')">Masuk di sini</a>
                    </p>
                </div>

            </div><!-- /.auth-body -->

            <a href="<?= BASE_URL ?>/" class="back-link" aria-label="Kembali ke halaman utama">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
        </div><!-- /.auth-panel-right -->

    </div><!-- /.auth-container -->

<script>
/* ── Tab Switching ── */
function switchTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

    const btn = document.getElementById('tab-' + tab + '-btn');
    const panel = document.getElementById('tab-' + tab);
    if (btn) btn.classList.add('active');
    if (panel) panel.classList.add('active');

    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    history.replaceState(null, '', url);
}

/* ── Password Toggle ── */
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

/* ── Theme Toggle ── */
function toggleTheme() {
    const html = document.documentElement;
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('simo-theme', next);
    updateThemeIcon(next);
}
function updateThemeIcon(theme) {
    const icon = document.getElementById('theme-icon');
    if (icon) icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
}
(function() { updateThemeIcon(localStorage.getItem('simo-theme') || 'light'); })();

/* ── Login form: loading state ── */
document.getElementById('login-form')?.addEventListener('submit', function(e) {
    const username = document.getElementById('login-username').value.trim();
    const password = document.getElementById('login-password').value;
    if (!username || !password) {
        e.preventDefault();
        document.getElementById('login-username').classList.add('error');
        return;
    }
    const btn = document.getElementById('login-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Masuk...';
});

/* ── Register form: validate + loading ── */
document.getElementById('register-form')?.addEventListener('submit', function(e) {
    const username = document.getElementById('reg-username').value.trim();
    const email    = document.getElementById('reg-email').value.trim();
    const klinik   = document.getElementById('reg-klinik').value.trim();
    const pass     = document.getElementById('reg-password').value;
    const confirm  = document.getElementById('reg-confirm').value;

    let valid = true;

    if (!username || username.length < 3) {
        document.getElementById('reg-username').classList.add('error');
        valid = false;
    }
    if (!email || !email.includes('@')) {
        document.getElementById('reg-email').classList.add('error');
        valid = false;
    }
    if (!klinik) {
        document.getElementById('reg-klinik').classList.add('error');
        valid = false;
    }
    if (pass.length < 8) {
        e.preventDefault();
        alert('Password minimal 8 karakter!');
        return;
    }
    if (pass !== confirm) {
        e.preventDefault();
        document.getElementById('reg-confirm').classList.add('error');
        alert('Konfirmasi password tidak cocok!');
        return;
    }

    if (!valid) { e.preventDefault(); return; }

    const btn = document.getElementById('register-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Mendaftar...';
});

/* Remove error class on input */
document.querySelectorAll('.auth-input').forEach(inp => {
    inp.addEventListener('input', () => inp.classList.remove('error'));
});
</script>
</body>
</html>
