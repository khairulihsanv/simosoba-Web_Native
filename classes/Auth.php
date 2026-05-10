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
            die("Akses Ditolak: Anda tidak memiliki izin untuk halaman ini.");
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
            'user'        => 'Staff Divisi'
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
