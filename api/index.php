<?php
/**
 * api/index.php - Master Template & Router (Vercel Optimized)
 */

require_once dirname(__DIR__) . '/init.php';

// 1. Ambil Halaman dari URL
$page = $_GET['page'] ?? '';

// 2. Tentukan halaman default
if (empty($page)) {
    $page = isset($_SESSION['user_id']) ? 'dashboard' : 'landing';
}

// 3. LOGIC SEPARATION: Jalankan "Action" (tanpa output) sebelum Header
$action_pages = ['login_process', 'logout', 'check_stock'];
if (in_array($page, $action_pages)) {
    $action_file = BASE_PATH . '/api/' . $page . '.php';
    if (file_exists($action_file)) {
        include $action_file;
        exit(); // Hentikan eksekusi setelah action selesai (biasanya redirect)
    }
}

// 4. Render Header (Pusat CSS & Framework)
include BASE_PATH . '/includes/header.php';

// 5. Render Main Content
echo '<body class="font-sans antialiased bg-softgrey">';

// Proteksi Halaman Dashboard/Data
$auth_pages = ['dashboard', 'stok', 'laporan', 'users'];
if (in_array($page, $auth_pages)) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit();
    }
    // Jika login, tampilkan sidebar
    include BASE_PATH . '/includes/sidebar.php';
    echo '<main class="ml-64 p-8">';
} else {
    echo '<main>';
}

// Routing Halaman View
$view_file = BASE_PATH . '/views/' . $page . '.php';
$api_file  = BASE_PATH . '/api/' . $page . '.php';

if (file_exists($view_file)) {
    include $view_file;
} elseif (file_exists($api_file) && $page !== 'index') {
    include $api_file;
} elseif ($page === 'landing') {
    // Default Landing Content (Bisa dipindah ke views/landing.php)
    include BASE_PATH . '/views/landing.php';
} else {
    // 404 Page
    echo "<div class='flex flex-col items-center justify-center min-h-[60vh]'>
            <h1 class='text-8xl font-bold text-slate-200'>404</h1>
            <p class='text-slate-500 text-sm'>Halaman tidak ditemukan.</p>
            <a href='index.php' class='mt-4 text-emerald font-bold text-sm'>Kembali ke Beranda</a>
          </div>";
}

echo '</main>';
echo '</body></html>';
?>
