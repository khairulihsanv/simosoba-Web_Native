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
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? 'user';

    // Validate
    if (!$nama || !$email || !$password || !$confirm) {
        $_SESSION['login_error'] = 'Semua field wajib diisi.';
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

    // Sanitize role
    $allowedRoles = ['user', 'admin_staff', 'super_admin'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'user';
    }

    try {
        // Check if table exists, create if not
        ensureUsersTable($pdo);

        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['login_error'] = 'Email sudah terdaftar. Silakan login.';
            header('Location: ' . BASE_URL . '/?page=login&tab=login');
            exit();
        }

        // Insert user
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$nama, $email, $hash, $role]);

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
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $_SESSION['login_error'] = 'Email dan password wajib diisi.';
        header('Location: ' . BASE_URL . '/?page=login');
        exit();
    }

    try {
        ensureUsersTable($pdo);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['login_error'] = 'Email atau password salah.';
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

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            nama        VARCHAR(100) NOT NULL,
            email       VARCHAR(120) NOT NULL UNIQUE,
            password    VARCHAR(255) NOT NULL,
            role        ENUM('user','admin_staff','super_admin') DEFAULT 'user',
            divisi      VARCHAR(80)  DEFAULT '',
            remember_token VARCHAR(128) DEFAULT NULL,
            created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
            updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}
