<?php
/**
 * init.php - Global Initialization & Autoloading
 */

// 1. Path Standardization
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

// 1.1 Base URL Detection (Robust Vercel & Localhost)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host;

if (strpos($host, 'localhost') !== false) {
    // Deteksi subfolder secara otomatis di localhost
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Jika kita di dalam folder api/ atau actions/, ambil parent-nya
    if (strpos($script_dir, '/api') !== false || strpos($script_dir, '/actions') !== false) {
        $base_url .= str_replace(['/api', '/actions'], '', $script_dir);
    } else {
        $base_url .= ($script_dir === '/') ? '' : $script_dir;
    }
}

define('BASE_URL', rtrim($base_url, '/'));

// 2. Session Start (Vercel Fix)
if (session_status() === PHP_SESSION_NONE) {
    if (PHP_SAPI !== 'cli') {
        session_save_path('/tmp');
    }
    session_start();
}

// 3. Global Autoloading for Classes
spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . "/classes/" . str_replace('\\', '/', $class) . ".php";
    if (file_exists($file)) {
        require_once $file;
    }
});

// 4. Database Connection
require_once BASE_PATH . '/config/database.php';
// $pdo is provided by config/database.php

/**
 * 5. Global Instance Sharing (Dependency Injection)
 * Objek ini akan digunakan di seluruh aplikasi untuk mengurangi redundansi.
 */
$auth = new Auth();
$currentUser = new User($auth->me()); // Inisialisasi User Object
$obatModel = new Obat($pdo);
$prediksiModel = new Prediksi($pdo);

/**
 * Helper Global (Opsional: Jika user masih ingin menggunakan fungsi standalone)
 * Namun disarankan memanggil via $auth->method()
 */
if (!function_exists('me')) {
    function me(): array { global $auth; return $auth->me(); }
}
if (!function_exists('getDivisiFilter')) {
    function getDivisiFilter(string $alias = ''): string { global $auth; return $auth->getDivisiFilter($alias); }
}
if (!function_exists('requireLogin')) {
    function requireLogin(): void { global $auth; $auth->requireLogin(); }
}
if (!function_exists('requireRole')) {
    function requireRole(array|string $roles): void { global $auth; $auth->requireRole($roles); }
}
if (!function_exists('canManageObat')) {
    function canManageObat(): bool { global $auth; return $auth->canManageObat(); }
}
?>
