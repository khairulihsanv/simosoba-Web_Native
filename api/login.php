<?php
/**
 * api/login.php – Login & Register Handler
 * Handles both login and register POST requests
 */
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
}

header('Content-Type: text/html; charset=utf-8');

$action = $_POST['action'] ?? '';

// ── REGISTER ─────────────────────────────────────────────────
if ($action === 'register') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = 'user'; // default role, managed by super_admin later

    // Validate
    if (!$nama || !$username || !$email || !$password || !$confirm) {
        $_SESSION['login_error'] = 'Semua field wajib diisi.';
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $_SESSION['login_error'] = 'Username hanya boleh huruf, angka, underscore (3-30 karakter).';
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = 'Format email tidak valid.';
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['login_error'] = 'Password minimal 8 karakter.';
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['login_error'] = 'Password dan konfirmasi password tidak cocok.';
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }

    try {
        // Check if table exists, create if not
        ensureUsersTable($pdo);

        // Check duplicate username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['login_error'] = 'Username sudah digunakan.';
            header('Location: ' . BASE_URL . '/?page=login&tab=register');
            exit();
        }

        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['login_error'] = 'Email sudah terdaftar. Silakan login.';
            header('Location: ' . BASE_URL . '/?page=login&tab=login');
            exit();
        }

        // Insert user (generate next ID manually as TiDB auto_increment is not configured on this existing table)
        $stmtId = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM users");
        $nextId = (int)$stmtId->fetch()['next_id'];

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (id, nama, username, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$nextId, $nama, $username, $email, $hash, $role]);

        $_SESSION['login_success'] = 'Akun berhasil dibuat! Silakan login.';
        header('Location: ' . BASE_URL . '/?page=login&tab=login');
        exit();

    } catch (Throwable $e) {
        $_SESSION['login_error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/?page=login&tab=register');
        exit();
    }
}

// ── LOGIN ─────────────────────────────────────────────────────
if ($action === 'login') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $_SESSION['login_error'] = 'Username dan password wajib diisi.';
        header('Location: ' . BASE_URL . '/?page=login');
        exit();
    }

    try {
        ensureUsersTable($pdo);

        // Support login by username or email for flexibility
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['login_error'] = 'Username atau password salah.';
            header('Location: ' . BASE_URL . '/?page=login');
            exit();
        }

        // Set session
        session_regenerate_id(true);
        $_SESSION['user_id']         = (int)$user['id'];
        $_SESSION['nama']            = $user['nama'];
        $_SESSION['email']           = $user['email'];
        $_SESSION['role']            = $user['role'];
        $_SESSION['divisi']          = $user['divisi'] ?? '';
        $_SESSION['last_regenerate'] = time();

        header('Location: ' . BASE_URL . '/?page=dashboard');
        exit();

    } catch (Throwable $e) {
        $_SESSION['login_error'] = 'Terjadi kesalahan saat login: ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/?page=login');
        exit();
    }
}

// Not a valid POST action
header('Location: ' . BASE_URL . '/?page=login');
exit();

// ── Helper: Ensure users table exists ────────────────────────
function ensureUsersTable(PDO $pdo): void {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        $tableExists = false;
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->fetch()) {
            $tableExists = true;
        }

        if (!$tableExists) {
            $pdo->exec("
                CREATE TABLE users (
                    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
                    nama        VARCHAR(100) NOT NULL,
                    username    VARCHAR(50) NOT NULL UNIQUE,
                    email       VARCHAR(120) NOT NULL UNIQUE,
                    password    VARCHAR(255) NOT NULL,
                    role        VARCHAR(50) DEFAULT 'user',
                    divisi      VARCHAR(80)  DEFAULT '',
                    remember_token VARCHAR(128) DEFAULT NULL,
                    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
                    updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_username (username),
                    INDEX idx_email (email)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    } catch (Throwable $e) {
        // Fallback or ignore error
    }

    // Add username column if table exists but doesn't have it
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) NOT NULL UNIQUE AFTER nama");
        }
    } catch (Throwable $e) {
        // Ignore column addition error if it fails
    }
}
