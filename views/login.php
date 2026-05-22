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
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/main.css">

    <script>
        (function() {
            const saved = localStorage.getItem('simo-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', saved || (prefersDark ? 'dark' : 'light'));
        })();
    </script>

    <style>
        body {
            min-height: 100vh;
            background: var(--bg-page);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        /* Background blobs */
        .auth-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            opacity: .25;
        }
        .auth-blob-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #6366f1, #8b5cf6);
            top: -200px; right: -100px;
        }
        .auth-blob-2 {
            width: 350px; height: 350px;
            background: radial-gradient(circle, #22c55e, #06b6d4);
            bottom: -100px; left: -80px;
        }

        .auth-wrap {
            width: 100%; max-width: 440px;
            position: relative; z-index: 1;
        }

        .auth-logo {
            display: flex; align-items: center; gap: 10px;
            justify-content: center; margin-bottom: 28px;
        }

        .auth-logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.2rem;
            box-shadow: 0 4px 16px rgba(99,102,241,.4);
        }

        .auth-brand {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem; font-weight: 900;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-card {
            background: var(--bg-card);
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 8px 40px rgba(0,0,0,.1);
            overflow: hidden;
        }

        /* Tabs */
        .auth-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
        }

        .auth-tab {
            flex: 1; padding: 16px;
            text-align: center;
            font-size: .9rem; font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all .2s;
            border: none; background: none;
            position: relative;
        }

        .auth-tab.active {
            color: var(--accent);
        }

        .auth-tab.active::after {
            content: '';
            position: absolute; bottom: -1px; left: 0; right: 0;
            height: 2px; background: var(--accent);
            border-radius: 2px 2px 0 0;
        }

        .auth-body {
            padding: 28px;
        }

        .auth-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.4rem; font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .auth-subtitle {
            font-size: .84rem; color: var(--text-muted);
            margin-bottom: 24px;
        }

        .auth-form {
            display: flex; flex-direction: column; gap: 16px;
        }

        .auth-input-wrap {
            position: relative;
        }

        .auth-input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); font-size: .95rem;
            pointer-events: none;
        }

        .auth-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: .9rem;
            color: var(--text-primary);
            background: var(--bg-page);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            font-family: inherit;
        }

        .auth-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
            background: var(--bg-card);
        }

        .auth-input::placeholder { color: var(--text-muted); }

        .password-toggle {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer; font-size: .9rem;
            background: none; border: none;
            transition: color .2s;
        }
        .password-toggle:hover { color: var(--accent); }

        .auth-btn {
            width: 100%; padding: 13px;
            border: none; border-radius: 12px;
            background: var(--accent); color: #fff;
            font-size: .95rem; font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all .2s;
            box-shadow: 0 4px 16px rgba(99,102,241,.3);
            margin-top: 4px;
        }

        .auth-btn:hover {
            background: var(--accent-hover);
            box-shadow: 0 6px 24px rgba(99,102,241,.4);
            transform: translateY(-1px);
        }

        .auth-btn:active { transform: scale(.97); }

        .auth-btn:disabled {
            opacity: .7; cursor: not-allowed; transform: none;
        }

        .auth-divider {
            display: flex; align-items: center; gap: 12px;
            font-size: .78rem; color: var(--text-muted); margin: 4px 0;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1;
            height: 1px; background: var(--border);
        }

        .alert-auth {
            padding: 11px 14px;
            border-radius: 10px;
            font-size: .84rem; font-weight: 500;
            display: flex; align-items: center; gap: 8px;
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

        /* Theme toggle button */
        .theme-btn-auth {
            position: fixed; top: 20px; right: 20px;
            width: 40px; height: 40px;
            border-radius: 10px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1.1rem;
            z-index: 10; transition: all .2s;
        }
        .theme-btn-auth:hover { color: var(--accent); border-color: var(--accent); }

        /* Tab panels */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* Role select styling */
        .auth-select {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: .9rem;
            color: var(--text-primary);
            background: var(--bg-page);
            outline: none;
            transition: border-color .2s;
            font-family: inherit;
            appearance: none;
            cursor: pointer;
        }
        .auth-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
            background: var(--bg-card);
        }

        /* Back to landing */
        .back-landing {
            display: flex; align-items: center; gap: 6px;
            font-size: .82rem; color: var(--text-muted);
            justify-content: center; margin-top: 20px;
            text-decoration: none; transition: color .2s;
        }
        .back-landing:hover { color: var(--accent); }
    </style>
</head>
<body>
    <!-- Blobs -->
    <div class="auth-blob auth-blob-1" aria-hidden="true"></div>
    <div class="auth-blob auth-blob-2" aria-hidden="true"></div>

    <!-- Theme Toggle -->
    <button class="theme-btn-auth" onclick="toggleTheme()" aria-label="Toggle dark mode" id="theme-btn">
        <i class="bi bi-moon-stars" id="theme-icon-auth"></i>
    </button>

    <div class="auth-wrap">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon" aria-hidden="true">
                <i class="bi bi-capsule-pill"></i>
            </div>
            <span class="auth-brand">SiMoSoBa</span>
        </div>

        <div class="auth-card">
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

            <!-- Login Tab -->
            <div class="auth-body">

                <?php if ($error): ?>
                <div class="alert-auth err" role="alert" aria-live="polite">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert-auth ok" role="alert" aria-live="polite">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <!-- ── LOGIN PANEL ────────────────────────── -->
                <div class="tab-panel <?= $tab === 'login' ? 'active' : '' ?>" id="tab-login" role="tabpanel" aria-labelledby="tab-login-btn">
                    <h1 class="auth-title">Selamat Datang</h1>
                    <p class="auth-subtitle">Masuk untuk mengelola inventori obat Anda</p>

                    <form class="auth-form" method="POST" action="<?= BASE_URL ?>/api/login.php" id="login-form" novalidate>
                        <input type="hidden" name="action" value="login">

                        <div class="form-group">
                            <label class="form-label" for="login-username">Username</label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-person auth-input-icon" aria-hidden="true"></i>
                                <input type="text" id="login-username" name="username"
                                       class="auth-input" placeholder="Masukkan username"
                                       required autocomplete="username" aria-required="true">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="login-password">Password</label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-lock auth-input-icon" aria-hidden="true"></i>
                                <input type="password" id="login-password" name="password"
                                       class="auth-input" placeholder="Masukkan password"
                                       required autocomplete="current-password" aria-required="true">
                                <button type="button" class="password-toggle"
                                        onclick="togglePassword('login-password', this)"
                                        aria-label="Toggle password visibility">
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
                    <p style="text-align:center;font-size:.84rem;color:var(--text-muted);margin-top:12px">
                        Belum punya akun?
                        <a href="javascript:void(0)" onclick="switchTab('register')" style="color:var(--accent);font-weight:600">Daftar sekarang</a>
                    </p>
                </div>

                <!-- ── REGISTER PANEL ──────────────────────── -->
                <div class="tab-panel <?= $tab === 'register' ? 'active' : '' ?>" id="tab-register" role="tabpanel" aria-labelledby="tab-register-btn">
                    <h2 class="auth-title">Buat Akun Baru</h2>
                    <p class="auth-subtitle">Daftarkan diri Anda untuk memulai</p>

                    <form class="auth-form" method="POST" action="<?= BASE_URL ?>/api/login.php" id="register-form" novalidate>
                        <input type="hidden" name="action" value="register">

                        <div class="form-group">
                            <label class="form-label" for="reg-name">Nama Lengkap <span class="req">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-person auth-input-icon" aria-hidden="true"></i>
                                <input type="text" id="reg-name" name="nama"
                                       class="auth-input" placeholder="Nama Lengkap Anda"
                                       required autocomplete="name" aria-required="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="reg-username">Username <span class="req">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-person-badge auth-input-icon" aria-hidden="true"></i>
                                <input type="text" id="reg-username" name="username"
                                       class="auth-input" placeholder="Buat username Anda"
                                       required autocomplete="username" aria-required="true">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-email">Email <span class="req">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-envelope auth-input-icon" aria-hidden="true"></i>
                                <input type="email" id="reg-email" name="email"
                                       class="auth-input" placeholder="nama@email.com"
                                       required autocomplete="email" aria-required="true">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-password">Password <span class="req">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-lock auth-input-icon" aria-hidden="true"></i>
                                <input type="password" id="reg-password" name="password"
                                       class="auth-input" placeholder="Min. 8 karakter"
                                       required minlength="8" autocomplete="new-password" aria-required="true">
                                <button type="button" class="password-toggle"
                                        onclick="togglePassword('reg-password', this)"
                                        aria-label="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="reg-confirm">Konfirmasi Password <span class="req">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="bi bi-lock-fill auth-input-icon" aria-hidden="true"></i>
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

                    <p style="text-align:center;font-size:.84rem;color:var(--text-muted);margin-top:16px">
                        Sudah punya akun?
                        <a href="javascript:void(0)" onclick="switchTab('login')" style="color:var(--accent);font-weight:600">Masuk di sini</a>
                    </p>
                </div>

            </div>
        </div>

        <a href="<?= BASE_URL ?>/" class="back-landing" aria-label="Back to landing page">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>

<script>
/* Tab Switching */
function switchTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

    document.getElementById('tab-' + tab + '-btn').classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');

    // Update URL without reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    history.replaceState(null, '', url);
}

/* Password Toggle */
function togglePassword(inputId, btn) {
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

/* Theme Toggle */
function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('simo-theme', next);
    updateIcon(next);
}
function updateIcon(theme) {
    const icon = document.getElementById('theme-icon-auth');
    if (icon) icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
}
(function() { updateIcon(localStorage.getItem('simo-theme') || 'light'); })();

/* Form submission feedback */
document.getElementById('login-form')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('login-btn');
    btn.disabled = true;
    btn.innerHTML = '<span style="width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:inline-block"></span> Masuk...';
});

document.getElementById('register-form')?.addEventListener('submit', function(e) {
    const pass = document.getElementById('reg-password').value;
    const confirm = document.getElementById('reg-confirm').value;

    if (pass !== confirm) {
        e.preventDefault();
        alert('Password dan konfirmasi password tidak sama!');
        return;
    }
    if (pass.length < 8) {
        e.preventDefault();
        alert('Password minimal 8 karakter!');
        return;
    }

    const btn = document.getElementById('register-btn');
    btn.disabled = true;
    btn.innerHTML = '<span style="width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:inline-block"></span> Mendaftar...';
});

// CSS for spinner
const style = document.createElement('style');
style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
</body>
</html>
