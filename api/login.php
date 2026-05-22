<?php
/**
 * api/login.php – Login & Register Handler
 * Handles both login and register POST requests
 */
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
}

// Prevent any output before headers
ob_start();

$action = $_POST['action'] ?? '';

// ── REGISTER ─────────────────────────────────────────────────
if ($action === 'register') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $klinik   = trim($_POST['klinik'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? $password;

    // Auto-derive nama from klinik or username
    $nama = $klinik ?: ucwords(str_replace(['_', '-'], ' ', $username));

    // ── Validate fields ──
    if (!$username || !$email || !$password) {
        $_SESSION['login_error'] = 'Username, email, dan password wajib diisi.';
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    if (!$klinik) {
        $_SESSION['login_error'] = 'Nama klinik / apotek wajib diisi.';
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $_SESSION['login_error'] = 'Username hanya boleh huruf, angka, underscore (3-30 karakter).';
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = 'Format email tidak valid.';
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['login_error'] = 'Password minimal 8 karakter.';
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['login_error'] = 'Password dan konfirmasi password tidak cocok.';
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    try {
        ensureUsersTable($pdo);

        // Check duplicate username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['login_error'] = 'Username sudah digunakan, pilih yang lain.';
            ob_end_clean();
            header('Location: ' . BASE_URL . '/?page=login&tab=register');
            exit();
        }

        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['login_error'] = 'Email sudah terdaftar. Silakan login.';
            ob_end_clean();
            header('Location: ' . BASE_URL . '/?page=login&tab=login');
            exit();
        }

        // Generate next ID safely (TiDB serverless compatibility)
        $stmtId = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM users");
        $nextId = (int)$stmtId->fetch()['next_id'];

        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert with klinik stored in divisi column (reuse existing schema)
        $stmt = $pdo->prepare(
            "INSERT INTO users (id, nama, username, email, password, role, divisi, created_at)
             VALUES (?, ?, ?, ?, ?, 'user', ?, NOW())"
        );
        $stmt->execute([$nextId, $nama, $username, $email, $hash, $klinik]);

        $_SESSION['login_success'] = 'Akun berhasil dibuat! Silakan login.';
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login&tab=login');
        exit();

    } catch (Throwable $e) {
        $_SESSION['login_error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }
}

// ── LOGIN ─────────────────────────────────────────────────────
if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $_SESSION['login_error'] = 'Username dan password wajib diisi.';
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login');
        exit();
    }

    try {
        ensureUsersTable($pdo);

        // Support login by username (case-insensitive) or email
        $usernameLower = strtolower($username);
        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE LOWER(username) = ? OR LOWER(email) = ? LIMIT 1"
        );
        $stmt->execute([$usernameLower, $usernameLower]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['login_error'] = 'Username/email atau password salah.';
            ob_end_clean();
            header('Location: ' . BASE_URL . '/?page=login');
            exit();
        }

        // ── Set session ──
        session_regenerate_id(true);
        $_SESSION['user_id']         = (int)$user['id'];
        $_SESSION['username']        = $user['username'] ?? '';
        $_SESSION['nama']            = $user['nama'] ?? $user['username'];
        $_SESSION['email']           = $user['email'];
        $_SESSION['role']            = $user['role'] ?? 'user';
        $_SESSION['divisi']          = $user['divisi'] ?? '';
        $_SESSION['klinik']          = $user['divisi'] ?? ''; // klinik stored in divisi
        $_SESSION['last_regenerate'] = time();

        // ── Redirect to dashboard ──
        $dashboardUrl = BASE_URL . '/?page=dashboard';
        ob_end_clean();
        header('HTTP/1.1 303 See Other');
        header('Location: ' . $dashboardUrl);
        exit();

    } catch (Throwable $e) {
        $_SESSION['login_error'] = 'Terjadi kesalahan saat login: ' . $e->getMessage();
        ob_end_clean();
        header('Location: ' . BASE_URL . '/?page=login');
        exit();
    }
}

// ── Not a valid POST action ────────────────────────────────────
ob_end_clean();
header('Location: ' . BASE_URL . '/?page=login');
exit();

// ── Helper: Ensure users table exists ────────────────────────
function ensureUsersTable(PDO $pdo): void {
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
    } catch (Throwable $e) {
        // Table already exists or creation failed gracefully
    }

    // Add username column if missing (older schema migration)
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) NOT NULL UNIQUE AFTER nama");
        }
    } catch (Throwable $e) { /* ignore */ }
}
