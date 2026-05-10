<?php
/**
 * api/login.php - Login Fragment
 */
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
}

if (!empty($_SESSION['user_id'])) { 
    echo "<script>window.location.href='index.php?page=dashboard';</script>";
    exit(); 
}

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

<!-- Login/Register UI Fragment -->
<div class="min-h-[80vh] flex items-center justify-center p-6">
    <div class="w-full max-w-[450px]">
        <!-- Brand -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-emerald text-3xl mx-auto shadow-xl border border-slate-100 mb-4">
                <i class="bi bi-capsule-pill"></i>
            </div>
            <h1 class="font-display font-bold text-3xl text-navy">SiMo<span class="text-emerald">SoBa</span></h1>
            <p class="text-slate-500 font-medium text-sm">Sistem Monitoring Stok Obat</p>
        </div>

        <div class="bg-white rounded-[32px] p-8 shadow-2xl shadow-navy/5 border border-slate-100">
            <!-- Tabs -->
            <div class="flex bg-slate-100 p-1.5 rounded-2xl mb-8">
                <button onclick="switchTab('login')" id="tab-login" class="flex-1 py-3 text-sm font-bold rounded-xl transition-all <?= $tab==='login'?'bg-navy text-white shadow-lg':'text-slate-500 hover:text-navy' ?>">
                    Login
                </button>
                <button onclick="switchTab('register')" id="tab-register" class="flex-1 py-3 text-sm font-bold rounded-xl transition-all <?= $tab==='register'?'bg-navy text-white shadow-lg':'text-slate-500 hover:text-navy' ?>">
                    Daftar
                </button>
            </div>

            <!-- Login Form -->
            <div id="sec-login" class="<?= $tab==='login'?'block':'hidden' ?>">
                <?php if ($errKey && isset($errLogin[$errKey])): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 text-sm font-semibold rounded-xl flex items-center gap-3">
                        <i class="bi bi-exclamation-triangle"></i> <?= $errLogin[$errKey] ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?page=login_process" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Username</label>
                        <input type="text" name="username" class="w-full px-5 py-3.5 bg-softgrey border border-slate-200 rounded-2xl text-sm focus:border-emerald transition-all" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-navy mb-2">Password</label>
                        <input type="password" name="password" class="w-full px-5 py-3.5 bg-softgrey border border-slate-200 rounded-2xl text-sm focus:border-emerald transition-all" required>
                    </div>
                    <button type="submit" class="w-full py-4 bg-navy text-white font-bold rounded-2xl hover:bg-slate-800 transition-all shadow-lg flex items-center justify-center gap-3">
                        Masuk Sekarang <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>
            
            <!-- Register logic placeholder... -->
        </div>
    </div>
</div>

<script>
    function switchTab(name) {
        window.location.href = 'index.php?page=login&tab=' + name;
    }
</script>
