<?php
// api/login_process.php
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
}

/**
 * login_process.php - Logika Verifikasi Login (Vercel Optimized)
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Semua field wajib diisi.';
        header("Location: index.php?page=login&error=empty");
        exit();
    }

    try {
        /**
         * TiDB Compatibility: PDO + Case-insensitive check
         */
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            /**
             * Password Verification
             */
            $isValid = password_verify($password, $user['password']) || ($password === $user['password']);

            if ($isValid) {
                // Set Session
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['divisi']   = $user['divisi'];

                // Berhasil: Redirect ke Dashboard menggunakan rute utama
                header("Location: index.php?page=dashboard");
                exit();
            } else {
                $_SESSION['error'] = "Password salah.";
            }
        } else {
            $_SESSION['error'] = "User tidak ditemukan.";
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
    }

    // Jika gagal, tampilkan debug jika di environment pengembangan atau redirect ke login
    if (isset($_SESSION['error'])) {
        header("Location: index.php?page=login&error=invalid");
        exit();
    }
} else {
    header("Location: index.php?page=login");
    exit();
}
?>
