<?php
/**
 * init.php - Konfigurasi Inti Aplikasi
 *
 * CRITICAL ORDER:
 *   1. BASE_PATH + BASE_URL  (no deps)
 *   2. Autoloader            (needed by DbSession)
 *   3. Database ($pdo)       (needed by DbSession)
 *   4. Session ini_set       (must be BEFORE session_start)
 *   5. Register DbSession    (must be BEFORE session_start)
 *   6. session_start()
 *   7. Auth instance + helpers
 */

error_reporting(E_ALL);
@ini_set('display_errors', '1');

/* ── 1. PATH ─────────────────────────────────────────── */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

/* ── 2. BASE_URL (Vercel-aware + localhost subfolder) ── */
$host     = $_SERVER['HTTP_HOST'] ?? '';
$isHttps  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['SERVER_PORT'] ?? 80) == 443)
          || (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
              && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
$protocol = $isHttps ? 'https://' : 'http://';
$base_url = $protocol . $host;

if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if (strpos($script_dir, '/api') !== false || strpos($script_dir, '/actions') !== false) {
        $base_url .= str_replace(['/api', '/actions'], '', $script_dir);
    } else {
        $base_url .= ($script_dir === '/') ? '' : $script_dir;
    }
}
define('BASE_URL', rtrim($base_url, '/'));

/* ── 3. AUTOLOADER ───────────────────────────────────── */
spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

/* ── 4. DATABASE ($pdo) ──────────────────────────────── */
require_once BASE_PATH . '/config/database.php';
// $pdo is now available

/* ── 5. SESSION SETTINGS (before session_start!) ─────── */
if (PHP_SAPI !== 'cli') {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_path', '/');        // IMPORTANT: '/' so cookie works in subdirs
    $secure = ($protocol === 'https://') ? '1' : '0';
    ini_set('session.cookie_secure', $secure);

    /* ── 6. REGISTER DB SESSION HANDLER ──────────────── */
    // File sessions don't work on Vercel (ephemeral /tmp per cold start).
    // Storing sessions in TiDB makes them survive across serverless invocations.
    if (!empty($pdo)) {
        $dbSession = new DbSession($pdo);
        session_set_save_handler($dbSession, true);
    }
}

/* ── 7. START SESSION ────────────────────────────────── */
if (session_status() === PHP_SESSION_NONE) {
    if (PHP_SAPI !== 'cli') {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }
    session_start();
}

/* ── 8. AUTH INSTANCE ────────────────────────────────── */
$auth = new Auth();

/* ── 9. GLOBAL HELPER FUNCTIONS ─────────────────────── */
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

/* ── 10. QR CODE HELPER (Google Charts — serverless safe) ── */
if (!function_exists('generateQR')) {
    function generateQR(string $data, string $filename): string {
        return 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='
             . urlencode($data) . '&choe=UTF-8';
    }
}
