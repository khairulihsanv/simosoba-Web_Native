<?php
/**
 * index.php - Entry Point & Landing Page
 * Digunakan sebagai router dan tampilan utama (Landing Page)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: text/html; charset=utf-8");
require_once 'init.php';

// Route Logic
$page = $_GET['page'] ?? '';

// Tentukan halaman default
if (empty($page)) {
    $page = isset($_SESSION['user_id']) ? 'dashboard' : 'landing';
}

// Case: Logout
if ($page === 'logout') {
    require_once BASE_PATH . '/actions/logout.php';
    exit();
}

// Case: Landing Page (Integrated Design)
if ($page === 'landing') {
    ?>
    <!DOCTYPE html>
    <html lang="id" class="scroll-smooth">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SiMoSoBa — Manajemen Stok Obat Cerdas</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
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
            .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
            .hero-gradient { background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.1), transparent), radial-gradient(circle at bottom left, rgba(30, 41, 59, 0.05), transparent); }
        </style>
    </head>
    <body class="bg-softgrey text-navy font-sans antialiased">
        <nav class="fixed top-0 w-full z-50 glass border-b border-slate-200 h-20 flex items-center">
            <div class="max-w-7xl mx-auto px-6 w-full flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-emerald rounded-xl flex items-center justify-center text-white shadow-lg"><i class="bi bi-capsule-pill" aria-hidden="true"></i></div>
                    <span class="font-display font-bold text-2xl">SiMo<span class="text-emerald">SoBa</span></span>
                </div>
                <div class="flex items-center gap-8">
                    <a href="#features" class="hover:text-emerald font-medium" aria-label="Lihat keunggulan SiMoSoBa">Keunggulan</a>
                    <a href="index.php?page=login" class="px-6 py-2.5 bg-navy text-white rounded-full font-bold shadow-lg" aria-label="Masuk ke akun SiMoSoBa">Masuk</a>
                </div>
            </div>
        </nav>

        <section class="pt-32 pb-20 px-4 md:px-6 hero-gradient overflow-hidden">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <h1 class="font-display font-bold text-4xl md:text-5xl lg:text-6xl leading-tight mb-6">Manajemen Stok <span class="text-emerald">Cerdas</span> & Akurat.</h1>
                    <p class="text-base md:text-lg text-slate-600 mb-10 leading-relaxed">Optimalkan ketersediaan obat Anda dengan sistem monitoring cerdas yang memprediksi kebutuhan stok secara real-time dengan akurasi tinggi.</p>
                    <a href="index.php?page=login" class="px-8 py-4 bg-emerald text-white font-bold rounded-2xl shadow-xl flex items-center justify-center gap-2 w-max mx-auto lg:mx-0" aria-label="Mulai kelola stok obat sekarang">Mulai Kelola <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="flex justify-center"><img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?q=80&w=800&auto=format&fit=crop" alt="Ilustrasi manajemen stok obat di apotek" class="w-full max-w-lg rounded-3xl drop-shadow-2xl"></div>
            </div>
        </section>

        <section id="features" class="py-24 px-6 bg-white">
            <div class="max-w-7xl mx-auto text-center mb-16">
                <h2 class="font-display font-bold text-4xl mb-4">3 Pilar Utama SiMoSoBa</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-8 max-w-7xl mx-auto">
                <div class="p-10 rounded-[32px] bg-softgrey border border-slate-100 hover:shadow-xl transition-all">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-emerald text-3xl mb-8 shadow-sm"><i class="bi bi-lightning-charge" aria-hidden="true"></i></div>
                    <h3 class="font-display font-bold text-2xl mb-4">Efisien</h3>
                    <p class="text-slate-600">Pencatatan mutasi otomatis yang memangkas waktu administratif hingga 70%.</p>
                </div>
                <div class="p-10 rounded-[32px] bg-softgrey border border-slate-100 hover:shadow-xl transition-all">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-emerald text-3xl mb-8 shadow-sm"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></div>
                    <h3 class="font-display font-bold text-2xl mb-4">Prediktif</h3>
                    <p class="text-slate-600">Algoritma cerdas yang memprediksi sisa hari stok berdasarkan tren penggunaan.</p>
                </div>
                <div class="p-10 rounded-[32px] bg-softgrey border border-slate-100 hover:shadow-xl transition-all">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-emerald text-3xl mb-8 shadow-sm"><i class="bi bi-clock-history" aria-hidden="true"></i></div>
                    <h3 class="font-display font-bold text-2xl mb-4">Real-time</h3>
                    <p class="text-slate-600">Pantau ketersediaan secara langsung dari perangkat manapun dengan data tersinkronisasi.</p>
                </div>
            </div>
        </section>

        <footer class="py-12 px-6 border-t border-slate-200 text-center text-slate-500 text-sm">
            &copy; 2026 SiMoSoBa System. Hak Cipta Dilindungi.
        </footer>
    </body>
    </html>
    <?php
    exit();
}

// Route Logic untuk halaman lainnya
$auth_pages = ['dashboard', 'stok', 'laporan', 'users', 'pengaturan', 'mutasi'];
if (in_array($page, $auth_pages)) {
    // 1. Wajib Login
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit();
    }

    // 2. Proteksi Role (Admin Only)
    $admin_only = ['laporan', 'users', 'pengaturan'];
    if (in_array($page, $admin_only)) {
        $auth->requireRole(['super_admin', 'admin_staff']);
    }
}

$view_file = BASE_PATH . '/views/' . $page . '.php';

if (file_exists($view_file)) {
    include $view_file;
} else {
    // Jika tidak ada di views, coba cek di folder api/ (untuk legacy/modular)
    $api_file = BASE_PATH . '/api/' . $page . '.php';
    if (file_exists($api_file)) {
        include $api_file;
    } else {
        echo "<div style='padding:50px; text-align:center; font-family:sans-serif;'>
                <h1 style='font-size:80px; margin:0; color:#cbd5e1;'>404</h1>
                <p style='color:#64748b;'>Halaman tidak ditemukan.</p>
                <a href='index.php' style='color:#10b981; font-weight:bold; text-decoration:none;'>Kembali ke Beranda</a>
              </div>";
    }
}
?>