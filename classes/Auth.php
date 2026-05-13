<?php
// classes/Auth.php

class Auth {
    /**
     * Cek apakah user sudah login
     */
    public function requireLogin(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?page=login");
            exit();
        }
    }

    /**
     * Ambil data user yang sedang login
     */
    public function me(): array {
        if (!isset($_SESSION['user_id'])) {
            $this->attemptRememberedLogin();
        }
        return [
            'id' => $_SESSION['user_id'] ?? 0,
            'nama' => $_SESSION['nama'] ?? 'Guest',
            'role' => $_SESSION['role'] ?? '',
            'divisi' => $_SESSION['divisi'] ?? ''
        ];
    }

    /**
     * Cek role user
     */
    public function requireRole(array|string $roles): void {
        $this->requireLogin();
        $userRole = $_SESSION['role'] ?? '';
        $allowedRoles = is_array($roles) ? $roles : [$roles];
        
        if (!in_array($userRole, $allowedRoles)) {
            header("HTTP/1.1 403 Forbidden");
            include BASE_PATH . '/views/403.php';
            exit();
        }
    }

    /**
     * Remember Me: Set Token ke Cookie & Database
     */
    public function setRememberMe(int $userId): void {
        global $pdo;
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 Hari
        
        setcookie('remember_token', $token, $expiry, "/", "", false, true); // HttpOnly
        
        $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->execute([$token, $userId]);
    }

    /**
     * Remember Me: Bersihkan Cookie & Token
     */
    public function clearRememberMe(): void {
        global $pdo;
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
            $stmt->execute([$token]);
            setcookie('remember_token', '', time() - 3600, "/");
        }
    }

    /**
     * Autologin via Token
     */
    private function attemptRememberedLogin(): void {
        global $pdo;
        if (isset($_COOKIE['remember_token']) && !isset($_SESSION['user_id'])) {
            $token = $_COOKIE['remember_token'];
            $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['divisi']   = $user['divisi'];
            }
        }
    }

    /**
     * Ambil filter SQL berdasarkan divisi user
     * @param string $alias Alias tabel (opsional, misal 'o')
     */
    public function getDivisiFilter(string $alias = ''): string {
        $role = $_SESSION['role'] ?? '';
        $divisi = $_SESSION['divisi'] ?? '';
        $prefix = $alias ? "$alias." : "";

        if ($role === 'super_admin') {
            return "1=1";
        }
        
        return "{$prefix}divisi = '" . addslashes($divisi) . "'";
    }

    /**
     * Label cantik untuk Role
     */
    public static function roleLabel(string $role): string {
        $labels = [
            'super_admin' => 'Super Admin',
            'admin_staff' => 'Admin Staff',
            'staff'       => 'Staff Farmasi',
            'user'        => 'User Umum'
        ];
        return $labels[$role] ?? ucfirst($role);
    }

    /**
     * Cek apakah user bisa mengelola obat
     */
    public function canManageObat(): bool {
        $role = $_SESSION['role'] ?? '';
        return in_array($role, ['super_admin', 'admin_staff']);
    }
}
?>
