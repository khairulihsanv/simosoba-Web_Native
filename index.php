<?php
// index.php - Single Entry Point (Router)

require_once 'init.php';

// Route Logic
$page = $_GET['page'] ?? '';

// Default page based on authentication
if (empty($page)) {
    $page = isset($_SESSION['user_id']) ? 'dashboard' : 'landing';
}

// Security: Prevent accessing sensitive pages without login
$auth_pages = ['dashboard', 'stok', 'laporan', 'users'];
if (in_array($page, $auth_pages) && !isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}

$view_file = BASE_PATH . '/views/' . $page . '.php';

if (file_exists($view_file)) {
    include $view_file;
} else {
    echo "<div style='padding:50px; text-align:center; font-family:sans-serif;'>
            <h1 style='font-size:80px; margin:0; color:#cbd5e1;'>404</h1>
            <p style='color:#64748b;'>Halaman tidak ditemukan.</p>
            <a href='index.php' style='color:#10b981; font-weight:bold; text-decoration:none;'>Kembali ke Beranda</a>
          </div>";
}
?>
