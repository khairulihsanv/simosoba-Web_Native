<?php
require_once dirname(__DIR__) . '/init.php';

if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit(); }

$tab = $_GET['tab'] ?? 'login';

$errLogin = ['invalid' => 'Username atau password salah.', 'empty' => 'Semua field wajib diisi.'];
$errReg = [
    'empty' => 'Semua field wajib diisi.', 'short' => 'Password minimal 6 karakter.',
    'mismatch' => 'Konfirmasi password tidak cocok.', 'duplicate' => 'Username sudah digunakan.',
    'db' => 'Gagal menyimpan data.'
];
$errKey = $_GET['error'] ?? '';
$sucKey = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiMoSoBa — Masuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { navy: '#1e293b', emerald: '#10b981', softgrey: '#f8fafc' },
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        .form-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .tab-active { background: #1e293b; color: white; box-shadow: 0 10px 15px -3px rgba(30, 41, 59, 0.2); }
    </style>
</head>
<body class="bg-softgrey min-h-screen flex items-center justify-center p-6">

    <!-- Decorative Elements -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-5%] w-[40%] h-[40%] bg-emerald/5 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] bg-navy/5 blur-[120px] rounded-full"></div>
    </div>

    <div class="w-full max-w-[450px]">
        <!-- Brand -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-emerald text-3xl mx-auto shadow-xl border border-slate-100 mb-4">
                <i class="bi bi-capsule-pill"></i>
            </div>
            <h1 class="font-display font-bold text-3xl text-navy">SiMo<span class="text-emerald">SoBa</span></h1>
            <p class="text-slate-500 font-medium">Sistem Monitoring Stok Obat</p>
        </div>

        <div class="bg-white rounded-[32px] p-8 shadow-2xl shadow-navy/5 border border-slate-100">
            <!-- Tabs -->
            <div class="flex bg-slate-100 p-1.5 rounded-2xl mb-8">
                <button onclick="switchTab('login')" id="tab-login" class="flex-1 py-3 text-sm font-bold rounded-xl form-transition <?= $tab==='login'?'tab-active':'text-slate-500 hover:text-navy' ?>">
                    Login
                </button>
                <button onclick="switchTab('register')" id="tab-register" class="flex-1 py-3 text-sm font-bold rounded-xl form-transition <?= $tab==='register'?'tab-active':'text-slate-500 hover:text-navy' ?>">
                    Daftar
                </button>
            </div>

            <!-- Login Form -->
            <div id="sec-login" class="form-transition <?= $tab==='login'?'block':'hidden scale-95 opacity-0' ?>">
                <?php if ($tab==='login' && $errKey && isset($errLogin[$errKey])): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 text-sm font-semibold rounded-xl flex items-center gap-3">
                        <i class="bi bi-exclamation-triangle"></i> <?= $errLogin[$errKey] ?>
                    </div>
                <?php endif; ?>
                <?php if ($sucKey === 'registered'): ?>
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 text-sm font-semibold rounded-xl flex items-center gap-3">
                        <i class="bi bi-check-circle"></i> Berhasil! Silakan masuk.
                    </div>
                <?php endif; ?>

                <form action="index.php?page=login_process" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Username</label>
                        <div class="relative group">
                            <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald transition-colors"></i>
                            <input type="text" name="username" class="w-full pl-11 pr-4 py-3.5 bg-softgrey border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-emerald focus:ring-4 focus:ring-emerald/5 transition-all" placeholder="Masukkan username" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Password</label>
                        <div class="relative group">
                            <i class="bi bi-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald transition-colors"></i>
                            <input type="password" id="pw-login" name="password" class="w-full pl-11 pr-12 py-3.5 bg-softgrey border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-emerald focus:ring-4 focus:ring-emerald/5 transition-all" placeholder="••••••••" required>
                            <button type="button" onclick="togglePw('pw-login','eye-login')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-navy transition-colors">
                                <i class="bi bi-eye" id="eye-login"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-4 bg-navy text-white font-bold rounded-2xl hover:bg-slate-800 transition-all shadow-lg shadow-navy/20 flex items-center justify-center gap-3">
                        Masuk Sekarang <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>

            <!-- Register Form -->
            <div id="sec-register" class="form-transition <?= $tab==='register'?'block':'hidden scale-95 opacity-0' ?>">
                <?php if ($tab==='register' && $errKey && isset($errReg[$errKey])): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 text-sm font-semibold rounded-xl flex items-center gap-3">
                        <i class="bi bi-exclamation-triangle"></i> <?= $errReg[$errKey] ?>
                    </div>
                <?php endif; ?>

                <form action="server/prosesRegister.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-navy mb-1.5">Nama Lengkap</label>
                        <input type="text" name="nama" class="w-full px-4 py-3 bg-softgrey border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="Nama lengkap Anda" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-navy mb-1.5">Username</label>
                        <input type="text" name="username" class="w-full px-4 py-3 bg-softgrey border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="Buat username" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-navy mb-1.5">Password</label>
                        <input type="password" id="pw-reg" name="password" class="w-full px-4 py-3 bg-softgrey border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="Min. 6 karakter" required minlength="6">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-navy mb-1.5">Konfirmasi</label>
                        <input type="password" name="konfirmasi" class="w-full px-4 py-3 bg-softgrey border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-emerald transition-all" placeholder="Ulangi password" required>
                    </div>
                    <button type="submit" class="w-full py-4 bg-emerald text-white font-bold rounded-2xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald/20 mt-4">
                        Buat Akun
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(name) {
            const isLogin = name === 'login';
            const loginTab = document.getElementById('tab-login');
            const regTab = document.getElementById('tab-register');
            const loginSec = document.getElementById('sec-login');
            const regSec = document.getElementById('sec-register');

            loginTab.className = `flex-1 py-3 text-sm font-bold rounded-xl form-transition ${isLogin ? 'tab-active' : 'text-slate-500 hover:text-navy'}`;
            regTab.className = `flex-1 py-3 text-sm font-bold rounded-xl form-transition ${!isLogin ? 'tab-active' : 'text-slate-500 hover:text-navy'}`;

            if(isLogin) {
                regSec.classList.add('hidden', 'scale-95', 'opacity-0');
                loginSec.classList.remove('hidden');
                setTimeout(() => loginSec.classList.remove('scale-95', 'opacity-0'), 10);
            } else {
                loginSec.classList.add('hidden', 'scale-95', 'opacity-0');
                regSec.classList.remove('hidden');
                setTimeout(() => regSec.classList.remove('scale-95', 'opacity-0'), 10);
            }
        }

        function togglePw(id, iconId) {
            const inp = document.getElementById(id);
            const icon = document.getElementById(iconId);
            const isPw = inp.type === 'password';
            inp.type = isPw ? 'text' : 'password';
            icon.className = isPw ? 'bi bi-eye-slash' : 'bi bi-eye';
        }
    </script>
</body>
</html>
