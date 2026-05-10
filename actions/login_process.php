<?php
// actions/login_process.php
require_once dirname(__DIR__) . '/init.php';

/**
 * login_process.php - Logika Verifikasi Login
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Semua field wajib diisi.';
        header("Location: ../index.php?page=login&error=empty");
        exit();
    }

    try {
        /**
         * TiDB Compatibility: 
         * Gunakan PDO dengan pembersihan input otomatis.
         * Gunakan LOWER() untuk memastikan case-insensitive pada username.
         */
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            /**
             * Login Verification:
             * Mendukung password_verify (hashed) dan perbandingan string langsung (plain legacy).
             */
            $isValid = password_verify($password, $user['password']) || ($password === $user['password']);

            if ($isValid) {
                // Sesi Berhasil Disimpan
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['divisi']   = $user['divisi'];

                // Redirect Aman ke Dashboard
                header("Location: ../index.php?page=dashboard");
                exit();
            } else {
                $_SESSION['error'] = "Password salah untuk akun '$username'.";
            }
        } else {
            $_SESSION['error'] = "User dengan username '$username' tidak terdaftar.";
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Kesalahan Database: " . $e->getMessage();
    }

    /**
     * DEBUG MODE (Hanya tampil jika login gagal)
     */
    if (isset($_SESSION['error'])) {
        echo "<div style='padding:40px; font-family:sans-serif; background:#fff5f5; border:1px solid #feb2b2; border-radius:20px; max-width:600px; margin:50px auto;'>";
        echo "<h2 style='color:#c53030;'>⚠️ Login Gagal (Debug Mode)</h2>";
        echo "<p style='color:#742a2a;'><b>Pesan:</b> " . $_SESSION['error'] . "</p>";
        echo "<hr style='border:none; border-top:1px solid #feb2b2; margin:20px 0;'>";
        echo "<b>Data POST:</b><pre style='background:#fff; padding:15px; border-radius:10px;'>";
        var_dump($_POST);
        echo "</pre>";
        echo "<br><a href='../index.php?page=login' style='padding:10px 20px; background:#1e293b; color:white; text-decoration:none; border-radius:10px;'>Coba Lagi</a>";
        echo "</div>";
        exit();
    }
} else {
    header("Location: ../index.php?page=login");
    exit();
}
?>
