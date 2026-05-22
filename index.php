<?php
/**
 * index.php - Entry Point & Router
 * Digunakan sebagai router dan tampilan utama (Landing Page)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: text/html; charset=utf-8");
require_once 'init.php';

// Route Logic
$page = $_GET['page'] ?? '';

// Default page
if (empty($page)) {
    $page = 'landing';
}

// Case: Logout
if ($page === 'logout') {
    require_once BASE_PATH . '/actions/logout.php';
    exit();
}

// Case: Landing Page (standalone, no app shell)
if ($page === 'landing') {
    include BASE_PATH . '/views/landing.php';
    exit();
}

// Case: Login / Register (standalone, no app shell)
if ($page === 'login') {
    if (isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/?page=dashboard');
        exit();
    }
    include BASE_PATH . '/views/login.php';
    exit();
}

// ── Authenticated Pages (wrapped in app shell) ─────────────────────
$auth_pages = ['dashboard', 'stok', 'laporan', 'users', 'pengaturan', 'mutasi', 'suppliers', 'alerts'];

if (in_array($page, $auth_pages)) {

    // Wajib Login
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/?page=login");
        exit();
    }

    // Role protection for specific pages
    $admin_only = ['laporan', 'users', 'pengaturan'];
    if (in_array($page, $admin_only)) {
        $auth->requireRole(['super_admin', 'admin_staff']);
    }

    // Title mapping
    $titleMap = [
        'dashboard' => 'Dashboard',
        'stok'      => 'Inventory',
        'mutasi'    => 'Transactions',
        'suppliers' => 'Suppliers',
        'alerts'    => 'Alerts',
        'laporan'   => 'Reports',
        'users'     => 'Users',
    ];
    $pageTitle = $titleMap[$page] ?? ucfirst($page);

    // Include app shell header + sidebar
    include BASE_PATH . '/includes/header.php';

    // Include the page view
    $view_file = BASE_PATH . '/views/' . $page . '.php';
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo "<div class='empty-state' style='padding:80px'>
                <i class='bi bi-file-earmark-x' style='font-size:3rem;color:var(--text-muted)'></i>
                <h2 style='font-family:Outfit,sans-serif;margin-top:12px'>Halaman Tidak Ditemukan</h2>
                <p>Halaman <code>" . htmlspecialchars($page) . "</code> belum tersedia.</p>
                <a href='" . BASE_URL . "/?page=dashboard' class='btn btn-primary' style='margin-top:16px'>Kembali ke Dashboard</a>
              </div>";
    }

    // Close app shell
    include BASE_PATH . '/includes/footer.php';
    exit();
}

// ── Fallback: Unknown pages → redirect landing ──────────────────────
$view_file = BASE_PATH . '/views/' . $page . '.php';
if (file_exists($view_file)) {
    include $view_file;
} else {
    http_response_code(404);
    include BASE_PATH . '/views/landing.php';
}