<?php
/**
 * api/login_process.php - Proses Verifikasi (Tanpa Output)
 */
ob_start(); // Buffer output untuk mencegah error headers already sent

if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        header("Location: index.php?page=login&error=empty");
        exit();
    }

    try {
        // Query User
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Verifikasi Password
            $isValid = password_verify($password, $user['password']) || ($password === $user['password']);

            if ($isValid) {
                // Set Session
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['divisi']   = $user['divisi'];

                // Handle Remember Me
                if (isset($_POST['remember_me'])) {
                    $auth->setRememberMe($user['id']);
                }

                // Sukses: Redirect ke Dashboard
                header("Location: index.php?page=dashboard");
                exit();
            }
        }
        
        // Gagal: Redirect ke Login dengan parameter error
        header("Location: index.php?page=login&error=invalid");
        exit();

    } catch (PDOException $e) {
        // Error DB: Tetap redirect agar tidak merusak flow, simpan pesan di log
        error_log("Login DB Error: " . $e->getMessage());
        header("Location: index.php?page=login&error=db");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

ob_end_flush();
?>
