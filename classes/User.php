<?php
// classes/User.php

class User {
    public int $id;
    public string $nama;
    public string $role;
    public string $divisi;

    /**
     * Constructor untuk inisialisasi properti user
     * Menghindari error 'Typed property must not be accessed before initialization'
     */
    public function __construct(array $data = []) {
        $this->id     = (int)($data['id'] ?? 0);
        $this->nama   = $data['nama'] ?? 'Guest';
        $this->role   = $data['role'] ?? 'user';
        $this->divisi = $data['divisi'] ?? 'Umum';
    }

    /**
     * Method untuk mendapatkan label role yang mudah dibaca
     * Menggunakan match expression agar kode lebih ringkas (PHP 8+)
     */
    public function roleLabel(): string {
        return match ($this->role) {
            'super_admin', '1' => 'Administrator Utama',
            'admin_staff', '2' => 'Apoteker',
            'user', '3'        => 'Staff Divisi',
            default            => 'User Terdaftar'
        };
    }

    /**
     * Contoh static method jika ingin dipanggil tanpa instance
     */
    public static function getRoleName(string $roleCode): string {
        return match ($roleCode) {
            'super_admin', '1' => 'Admin',
            'admin_staff', '2' => 'Apoteker',
            default            => 'Staff'
        };
    }
}
?>
