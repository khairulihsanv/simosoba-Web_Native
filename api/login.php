<?php
/**
 * api/login.php – Login & Register Handler
 *
 * IMPORTANT: session_write_close() is called before every redirect.
 * With a DB session handler, PHP writes session data lazily. If the process
 * exits via header(Location:) before writing finishes, the flash message
 * (or login session) is lost — causing the "refresh" symptom.
 */
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
}

ob_start(); // Buffer output so headers can always be sent

/* ── Redirect helper: flush session then redirect ────── */
function safeRedirect(string $url, int $code = 303): never {
    session_write_close();           // Force DB write BEFORE redirect
    ob_end_clean();
    header('HTTP/1.1 ' . $code . ' See Other');
    header('Location: ' . $url);
    exit();
}

$action = $_POST['action'] ?? '';

/* ══════════════════════════════════════════════════════
   REGISTER
   ══════════════════════════════════════════════════ */
if ($action === 'register') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email    = strtolower(trim($_POST['email']    ?? ''));
    $klinik   = trim($_POST['klinik']              ?? '');
    $password = $_POST['password']                 ?? '';
    $confirm  = $_POST['confirm_password']         ?? $password;

    // Derive display name from klinik, fallback to username
    $nama = $klinik ?: ucwords(str_replace(['_', '-'], ' ', $username));

    $errUrl  = BASE_URL . '/?page=login&tab=register';

    // ── Validate ──────────────────────────────────────
    if (!$username || !$email || !$password) {
        $_SESSION['login_error'] = 'Username, email, dan password wajib diisi.';
        safeRedirect($errUrl);
    }

    if (!$klinik) {
        $_SESSION['login_error'] = 'Nama klinik / apotek wajib diisi.';
        safeRedirect($errUrl);
    }

    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $_SESSION['login_error'] = 'Username hanya boleh huruf, angka, underscore (3–30 karakter).';
        safeRedirect($errUrl);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = 'Format email tidak valid.';
        safeRedirect($errUrl);
    }

    if (strlen($password) < 8) {
        $_SESSION['login_error'] = 'Password minimal 8 karakter.';
        safeRedirect($errUrl);
    }

    if ($password !== $confirm) {
        $_SESSION['login_error'] = 'Password dan konfirmasi password tidak cocok.';
        safeRedirect($errUrl);
    }

    // ── Database operations ───────────────────────────
    try {
        ensureUsersTable($pdo);

        // Duplicate username check
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['login_error'] = 'Username sudah digunakan, pilih yang lain.';
            safeRedirect($errUrl);
        }

        // Duplicate email check
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['login_error'] = 'Email sudah terdaftar. Silakan login.';
            safeRedirect(BASE_URL . '/?page=login&tab=login');
        }

        // Safe ID generation (TiDB serverless auto_increment compatibility)
        $stmtId = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM users");
        $nextId = (int) $stmtId->fetch(PDO::FETCH_ASSOC)['next_id'];

        $hash = password_hash($password, PASSWORD_BCRYPT);

        // klinik is stored in the `divisi` column (existing schema reuse)
        $stmt = $pdo->prepare(
            "INSERT INTO users (id, nama, username, email, password, role, divisi, created_at)
             VALUES (?, ?, ?, ?, ?, 'user', ?, NOW())"
        );
        $stmt->execute([$nextId, $nama, $username, $email, $hash, $klinik]);

        // ✅ Success flash — user will see this on the login tab
        $_SESSION['login_success'] = '🎉 Akun berhasil dibuat! Silakan masuk dengan username dan password Anda.';
        safeRedirect(BASE_URL . '/?page=login&tab=login');

    } catch (Throwable $e) {
        $_SESSION['login_error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        safeRedirect($errUrl);
    }
}

/* ══════════════════════════════════════════════════════
   LOGIN
   ══════════════════════════════════════════════════ */
if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']     ?? '';

    if (!$username || !$password) {
        $_SESSION['login_error'] = 'Username dan password wajib diisi.';
        safeRedirect(BASE_URL . '/?page=login');
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
            $_SESSION['login_error'] = 'Username/email atau password salah.';
            safeRedirect(BASE_URL . '/?page=login');
        }

        // ── Regenerate session ID (security) ──────────
        session_regenerate_id(true);

        // ── Store user data in session ─────────────────
        $_SESSION['user_id']         = (int)  $user['id'];
        $_SESSION['username']        =        $user['username'] ?? '';
        $_SESSION['nama']            =        $user['nama']     ?? $user['username'];
        $_SESSION['email']           =        $user['email'];
        $_SESSION['role']            =        $user['role']     ?? 'user';
        $_SESSION['divisi']          =        $user['divisi']   ?? '';
        $_SESSION['klinik']          =        $user['divisi']   ?? ''; // alias
        $_SESSION['last_regenerate'] = time();

        // ── Redirect to dashboard (303 forces GET) ─────
        safeRedirect(BASE_URL . '/?page=dashboard');

    } catch (Throwable $e) {
        $_SESSION['login_error'] = 'Terjadi kesalahan saat login: ' . $e->getMessage();
        safeRedirect(BASE_URL . '/?page=login');
    }
}

/* ── Unknown action ─────────────────────────────────── */
safeRedirect(BASE_URL . '/?page=login');

/* ══════════════════════════════════════════════════════
   HELPER: Ensure users table exists
   ══════════════════════════════════════════════════ */
function ensureUsersTable(PDO $pdo): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if (!$stmt->fetch()) {
            $pdo->exec("
                CREATE TABLE users (
                    id             BIGINT AUTO_INCREMENT PRIMARY KEY,
                    nama           VARCHAR(100) NOT NULL,
                    username       VARCHAR(50)  NOT NULL UNIQUE,
                    email          VARCHAR(120) NOT NULL UNIQUE,
                    password       VARCHAR(255) NOT NULL,
                    role           VARCHAR(50)  DEFAULT 'user',
                    divisi         VARCHAR(120) DEFAULT '',
                    remember_token VARCHAR(128) DEFAULT NULL,
                    created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP,
                    updated_at     DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_username (username),
                    INDEX idx_email    (email)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    } catch (Throwable $e) { /* table exists */ }

    // Add username column if older schema lacks it
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) NOT NULL UNIQUE AFTER nama");
        }
    } catch (Throwable $e) { /* ignore */ }
}
