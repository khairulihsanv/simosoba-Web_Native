<?php
/**
 * init.php - Konfigurasi Inti Aplikasi
 *
 * Session persistence on Vercel serverless is solved by:
 *   1. Signed HMAC cookie  → restores auth on every request
 *   2. URL params           → delivers flash messages (no session flash needed)
 *   File-based sessions are kept for within-request state only.
 *
 * EXECUTION ORDER (critical):
 *   BASE_PATH → BASE_URL → APP_SECRET → Autoloader → Database → Session → Auth
 */

error_reporting(E_ALL);
@ini_set('display_errors', '1');

/* ── 1. PATHS ────────────────────────────────────────────────── */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

/* ── 2. BASE_URL (Vercel-aware + localhost subfolder) ────────── */
$host    = $_SERVER['HTTP_HOST'] ?? '';
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
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

/* ── 3. APP SECRET (for signed auth cookie) ──────────────────── */
if (!defined('APP_SECRET')) {
    // Change this to any random string in production
    define('APP_SECRET', 'smsb2_7gKpXzQ_simosoba_2024');
}

/* ── 4. AUTOLOADER ───────────────────────────────────────────── */
spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

/* ── 5. DATABASE ─────────────────────────────────────────────── */
require_once BASE_PATH . '/config/database.php';
// $pdo is now available globally

/* ── 6. SESSION (file-based, within-request only) ────────────── */
if (PHP_SAPI !== 'cli') {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_secure', ($protocol === 'https://') ? '1' : '0');
}

if (session_status() === PHP_SESSION_NONE) {
    if (PHP_SAPI !== 'cli') {
        // Vercel /tmp is writable within a single invocation
        if (DIRECTORY_SEPARATOR === '/' && is_dir('/tmp') && is_writable('/tmp')) {
            session_save_path('/tmp');
        }
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }
    session_start();
}

/* ── 7. RESTORE AUTH FROM SIGNED COOKIE ──────────────────────── */
// This is the key fix for Vercel: file sessions are lost between serverless
// invocations, but cookies persist. We rebuild $_SESSION from the cookie.
if (PHP_SAPI !== 'cli' && !isset($_SESSION['user_id'])) {
    _smsb_restoreAuth();
}

/* ── 8. AUTH INSTANCE ────────────────────────────────────────── */
$auth = new Auth();

/* ── 9. HELPER FUNCTIONS ─────────────────────────────────────── */
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

/* ── 10. QR CODE HELPER ──────────────────────────────────────── */
if (!function_exists('generateQR')) {
    function generateQR(string $data, string $filename): string {
        return 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='
             . urlencode($data) . '&choe=UTF-8';
    }
}

/* ═══════════════════════════════════════════════════════════════
   AUTH COOKIE HELPERS
   Signed HMAC-SHA256 cookies — stateless, works on any serverless platform.
   ═══════════════════════════════════════════════════════════════ */

/**
 * Set a signed auth cookie after successful login.
 */
function _smsb_setAuthCookie(array $user): void
{
    $payload = rtrim(strtr(base64_encode(json_encode([
        'i' => (int)   ($user['id']       ?? 0),
        'u' => (string)($user['username'] ?? ''),
        'n' => (string)($user['nama']     ?? ''),
        'm' => (string)($user['email']    ?? ''),
        'r' => (string)($user['role']     ?? 'user'),
        'd' => (string)($user['divisi']   ?? ''),
        'e' => time() + (30 * 86400),          // 30-day expiry
    ])), '+/', '-_'), '=');

    $sig = hash_hmac('sha256', $payload, APP_SECRET);

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    setcookie('simosoba_auth', $payload . '.' . $sig, [
        'expires'  => time() + (30 * 86400),
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Clear the auth cookie (on logout).
 */
function _smsb_clearAuthCookie(): void
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    setcookie('simosoba_auth', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Try to restore $_SESSION from the signed auth cookie.
 * Called early in every request when session is empty.
 */
function _smsb_restoreAuth(): void
{
    $raw = $_COOKIE['simosoba_auth'] ?? '';
    if (!$raw) return;

    // Split payload.signature
    $dot = strrpos($raw, '.');
    if ($dot === false) return;

    $payload = substr($raw, 0, $dot);
    $sig     = substr($raw, $dot + 1);

    // Verify signature (timing-safe comparison)
    $expected = hash_hmac('sha256', $payload, APP_SECRET);
    if (!hash_equals($expected, $sig)) return;

    // Decode payload
    $data = json_decode(
        base64_decode(strtr($payload, '-_', '+/')),
        true
    );
    if (!is_array($data) || ($data['e'] ?? 0) < time()) return;
    if (empty($data['i'])) return; // No user ID = invalid

    // Restore session from cookie data
    $_SESSION['user_id']         = (int)   $data['i'];
    $_SESSION['username']        = (string)($data['u'] ?? '');
    $_SESSION['nama']            = (string)($data['n'] ?? '');
    $_SESSION['email']           = (string)($data['m'] ?? '');
    $_SESSION['role']            = (string)($data['r'] ?? 'user');
    $_SESSION['divisi']          = (string)($data['d'] ?? '');
    $_SESSION['klinik']          = (string)($data['d'] ?? '');
    $_SESSION['last_regenerate'] = time();
}
