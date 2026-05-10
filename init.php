<?php
/**
 * init.php - Global Initialization & Autoloading
 */

// 1. Path Standardization
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

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
