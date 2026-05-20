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

$errLogin = ['invalid' => 'Username atau password salah.', 'empty' => 'Semua field wajib diisi.'];
$errKey = $_GET['error'] ?? '';

include BASE_PATH . '/includes/header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="overflow-hidden rounded-[32px] border border-slate-200/80 bg-white/95 shadow-2xl shadow-slate-500/10 backdrop-blur-xl">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-slate-200 via-slate-300 to-slate-200"></div>
            <div class="p-8 sm:p-10">
                <div class="flex flex-col items-center text-center gap-4 mb-8">
                    <div class="relative group">
                        <div class="absolute inset-0 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 blur-2xl opacity-30 group-hover:opacity-40 transition-all"></div>
                        <span class="relative inline-flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-white shadow-lg ring-4 ring-white/70">
                            <i class="bi bi-capsule-pill text-3xl text-emerald"></i>
                        </span>
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-display font-bold text-slate-900">Welcome to SiMoSoBa</h1>
                        <p class="text-sm sm:text-base text-slate-500 font-medium">Sign in to continue</p>
                    </div>
                </div>

                <button type="button" class="w-full inline-flex items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                    <img src="https://www.svgrepo.com/show/355037/google.svg" alt="Google icon" class="h-5 w-5">
                    Continue with Google
                </button>

                <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-[0.35em] text-slate-400">
                    <span class="h-px flex-1 bg-slate-200"></span>
                    <span>or</span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>

                <?php if ($errKey && isset($errLogin[$errKey])): ?>
                    <div class="mb-6 rounded-3xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                        <i class="bi bi-exclamation-triangle-fill mr-2"></i> <?= $errLogin[$errKey] ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?page=login_process" method="POST" class="space-y-5">
                    <div>
                        <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Username</label>
                        <div class="relative rounded-3xl border border-slate-200 bg-slate-50 focus-within:border-emerald focus-within:ring-2 focus-within:ring-emerald/10">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400"><i class="bi bi-person"></i></span>
                            <input id="username" name="username" type="text" placeholder="username" class="w-full rounded-3xl bg-transparent py-3.5 pl-12 pr-4 text-sm text-slate-700 outline-none" required>
                        </div>
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                        <div class="relative rounded-3xl border border-slate-200 bg-slate-50 focus-within:border-emerald focus-within:ring-2 focus-within:ring-emerald/10">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400"><i class="bi bi-lock"></i></span>
                            <input id="password" name="password" type="password" placeholder="••••••••" class="w-full rounded-3xl bg-transparent py-3.5 pl-12 pr-4 text-sm text-slate-700 outline-none" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full rounded-3xl bg-navy px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-navy/10 transition hover:bg-slate-900">Sign in</button>
                </form>

                <div class="mt-6 flex flex-col gap-3 text-center text-sm text-slate-500 sm:flex-row sm:justify-between sm:text-left">
                    <a href="#" class="hover:text-slate-900 transition">Forgot password?</a>
                    <span>Need account? <a href="#" class="font-semibold text-navy hover:text-emerald">Contact admin</a></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
