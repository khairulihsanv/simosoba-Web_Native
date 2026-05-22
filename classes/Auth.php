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
        
        // Session hijacking protection
        $this->validateSession();
    }

    /**
     * Validasi session untuk mencegah session hijacking
     */
    private function validateSession(): void {
        // Check IP address consistency (optional, might be too strict for mobile users)
        // if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        //     $this->destroySession();
        //     header("Location: index.php?page=login");
        //     exit();
        // }
        
        // Check user agent consistency
        // NOTE: Disable for stability. In real networks/proxies/browser updates, user agent can change and cause random logout.
        // if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        //     $this->destroySession();
        //     header("Location: index.php?page=login");
        //     exit();
        // }

        
        // Regenerate session ID periodically to prevent session fixation
        if (!isset($_SESSION['last_regenerate']) || (time() - $_SESSION['last_regenerate']) > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();
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
            'nama' => $this->sanitizeOutput($_SESSION['nama'] ?? 'Guest'),
            'role' => $this->sanitizeOutput($_SESSION['role'] ?? ''),
            'divisi' => $this->sanitizeOutput($_SESSION['divisi'] ?? '')
        ];
    }

    /**
     * Cek role user
     */
    public function requireRole(array|string $roles): void {
        $this->requireLogin();
        $userRole = $this->sanitizeInput($_SESSION['role'] ?? '');
        $allowedRoles = is_array($roles) ? $roles : [$roles];
        $allowedRoles = array_map([$this, 'sanitizeInput'], $allowedRoles);

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
            setcookie('remember_token', '', time() - 36000, "/");
        }
    }

    /**
     * Autologin via Token
     */
    private function attemptRememberedLogin(): void {
        global $pdo;
        if (isset($_COOKIE['remember_token']) && !isset($_SESSION['user_id'])) {
            $token = $this->sanitizeInput($_COOKIE['remember_token']);
            $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user_id']  = (int)$user['id'];
                $_SESSION['username'] = $this->sanitizeOutput($user['username'] ?? '');
                $_SESSION['nama']     = $this->sanitizeOutput($user['nama'] ?? '');
                $_SESSION['role']     = $this->sanitizeOutput($user['role'] ?? '');
                $_SESSION['divisi']   = $this->sanitizeOutput($user['divisi'] ?? '');
                
                // Set session metadata for security
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION['last_regenerate'] = time();
            }
        }
    }

    /**
     * Ambil filter SQL berdasarkan divisi user
     * @param string $alias Alias tabel (opsional, misal 'o')
     */
    public function getDivisiFilter(string $alias = ''): string {
        $role = $this->sanitizeInput($_SESSION['role'] ?? '');
        $divisi = $this->sanitizeInput($_SESSION['divisi'] ?? '');
        $prefix = $alias ? "$alias." : "";

        if ($role === 'super_admin') {
            return "1=1";
        }

        return "{$prefix}divisi = '" . $this->escapeSql($divisi) . "'";
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
        $role = $this->sanitizeInput($_SESSION['role'] ?? '');
        return in_array($role, ['super_admin', 'admin_staff']);
    }

    /**
     * Sanitasi input untuk mencegah XSS dan SQL Injection
     */
    protected function sanitizeInput(string $input): string {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }

    /**
     * Sanitasi output untuk mencegah XSS
     */
    protected function sanitizeOutput(string $input): string {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape string untuk SQL (menggunakan PDO quote untuk keamanan)
     */
    protected function escapeSql(string $input): string {
        global $pdo;
        return $pdo->quote($input);
    }

    /**
     * Destroy session dan hapus semua data session
     */
    private function destroySession(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
?>