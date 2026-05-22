<?php
/**
 * api/login.php – Login & Register Handler
 *
 * Key design decisions:
 *  - Auth cookie is set via _smsb_setAuthCookie() — survives Vercel serverless
 *  - Flash messages use URL params (?ok=code / ?err=code), NOT session flash
 *    → eliminates all session-write dependencies
 *  - safeRedirect() does NOT call session_write_close() — no DbSession errors
 */
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
}

ob_start(); // Buffer output so stray whitespace never blocks headers

/* ── Redirect helper ───────────────────────────────────────── */
function safeRedirect(string $url): never
{
    if (ob_get_level() > 0) ob_end_clean();
    @session_write_close(); // Best-effort; suppress any warning
    header('HTTP/1.1 303 See Other');
    header('Location: ' . $url);
    exit();
}

$action = $_POST['action'] ?? '';

/* ══════════════════════════════════════════════════════════════
   REGISTER
   ══════════════════════════════════════════════════════════ */
if ($action === 'register') {
    $username = strtolower(trim($_POST['username']         ?? ''));
    $email    = strtolower(trim($_POST['email']            ?? ''));
    $klinik   = trim($_POST['klinik']                      ?? '');
    $password = $_POST['password']                         ?? '';
    $confirm  = $_POST['confirm_password']                 ?? '';

    // Auto-generate display name from klinik or username
    $nama = $klinik ?: ucwords(str_replace(['_', '-'], ' ', $username));

    $errBase = BASE_URL . '/?page=login&tab=register';

    /* ── Validate ──────────────────────────────────────── */
    if (!$username || !$email || !$password || !$confirm) {
        safeRedirect($errBase . '&err=field_required');
    }
    if (!$klinik) {
        safeRedirect($errBase . '&err=klinik_req');
    }
    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        safeRedirect($errBase . '&err=user_format');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        safeRedirect($errBase . '&err=email_bad');
    }
    if (strlen($password) < 8) {
        safeRedirect($errBase . '&err=pass_short');
    }
    if ($password !== $confirm) {
        safeRedirect($errBase . '&err=pass_mismatch');
    }

    /* ── Database ──────────────────────────────────────── */
    try {
        ensureUsersTable($pdo);

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            safeRedirect($errBase . '&err=user_taken');
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            safeRedirect($errBase . '&err=email_taken');
        }

        // TiDB-safe ID generation
        $stmtId = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 AS nid FROM users");
        $nextId  = (int) $stmtId->fetch(PDO::FETCH_ASSOC)['nid'];

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "INSERT INTO users (id, nama, username, email, password, role, divisi, created_at)
             VALUES (?, ?, ?, ?, ?, 'user', ?, NOW())"
        );
        $stmt->execute([$nextId, $nama, $username, $email, $hash, $klinik]);

        // ✅ Success — URL param carries flash message
        safeRedirect(BASE_URL . '/?page=login&tab=login&ok=registered');

    } catch (Throwable $e) {
        safeRedirect($errBase . '&err=db_err&detail=' . urlencode($e->getMessage()));
    }
}

/* ══════════════════════════════════════════════════════════════
   LOGIN
   ══════════════════════════════════════════════════════════ */
if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']     ?? '';

    if (!$username || !$password) {
        safeRedirect(BASE_URL . '/?page=login&err=field_required');
    }

    try {
        ensureUsersTable($pdo);

        $lower = strtolower($username);
        $stmt  = $pdo->prepare(
            "SELECT * FROM users
              WHERE LOWER(username) = ? OR LOWER(email) = ?
              LIMIT 1"
        );
        $stmt->execute([$lower, $lower]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            safeRedirect(BASE_URL . '/?page=login&err=invalid_creds');
        }

        // ── Regenerate session ID for security ──
        session_regenerate_id(true);

        // ── Set session (for within-request use) ──
        $_SESSION['user_id']         = (int)   $user['id'];
        $_SESSION['username']        = (string)($user['username'] ?? '');
        $_SESSION['nama']            = (string)($user['nama']     ?? $user['username']);
        $_SESSION['email']           = (string) $user['email'];
        $_SESSION['role']            = (string)($user['role']     ?? 'user');
        $_SESSION['divisi']          = (string)($user['divisi']   ?? '');
        $_SESSION['klinik']          = (string)($user['divisi']   ?? '');
        $_SESSION['last_regenerate'] = time();

        // ── Set signed auth cookie (persists across serverless invocations) ──
        _smsb_setAuthCookie($user);

        // ── Redirect to dashboard ──
        safeRedirect(BASE_URL . '/?page=dashboard');

    } catch (Throwable $e) {
        safeRedirect(BASE_URL . '/?page=login&err=login_err');
    }
}

/* ── Unknown action ────────────────────────────────────────── */
safeRedirect(BASE_URL . '/?page=login');

/* ══════════════════════════════════════════════════════════════
   HELPER: Ensure users table exists in TiDB
   ══════════════════════════════════════════════════════════ */
function ensureUsersTable(PDO $pdo): void
{
    static $done = false;
    if ($done) return;
    $done = true;

    try {
        $s = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($s->fetch()) return; // already exists
        $pdo->exec("
            CREATE TABLE users (
                id             BIGINT       AUTO_INCREMENT PRIMARY KEY,
                nama           VARCHAR(100) NOT NULL,
                username       VARCHAR(50)  NOT NULL,
                email          VARCHAR(120) NOT NULL,
                password       VARCHAR(255) NOT NULL,
                role           VARCHAR(50)  DEFAULT 'user',
                divisi         VARCHAR(120) DEFAULT '',
                remember_token VARCHAR(128),
                created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP,
                updated_at     DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_username (username),
                UNIQUE KEY uk_email    (email),
                INDEX  idx_username    (username),
                INDEX  idx_email       (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    } catch (Throwable $e) { /* table already exists */ }

    // Migrate: add username column if missing (old schema)
    try {
        $s = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
        if (!$s->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE AFTER nama");
        }
    } catch (Throwable $e) { /* ignore */ }
}
