<?php
// classes/ObatGenerik.php

/**
 * Kelas ObatGenerik yang mewarisi dari kelas Obat dasar
 * Demonstrasi Inheritance (Pewarisan)
 */
class ObatGenerik extends Obat {
    // Properti khusus untuk obat generik
    private string $merk;
    private string $bentuk; // tablet, kapsul, sirup, dll

    /**
     * Constructor
     * @param PDO $db Instance PDO untuk koneksi database
     * @param string $merk Merk obat generik
     * @param string $bentuk Bentuk obat (tablet, kapsul, dll)
     */
    public function __construct(PDO $db, string $merk = '', string $bentuk = 'tablet') {
        parent::__construct($db);
        $this->merk = $merk;
        $this->bentuk = $bentuk;
    }

    // Getter dan Setter khusus
    public function getMerk(): string {
        return $this->merk;
    }

    public function setMerk(string $merk): void {
        $this->merk = $merk;
    }

    public function getBentuk(): string {
        return $this->bentuk;
    }

    public function setBentuk(string $bentuk): void {
        $this->bentuk = $bentuk;
    }

    /**
     * Override metode calculateNewStock untuk obat generik
     * Demonstrasi Polimorfisme: perhitungan stok berbeda untuk obat generik
     * (misalnya ada faktor degradasi yang berbeda)
     * @param int $currentStok Stok saat ini
     * @param int $amount Jumlah yang ditambahkan/dikurangkan
     * @param string $type Jenis transaksi ('masuk' atau 'keluar')
     * @return int Stok baru setelah perhitungan
     */
    protected function calculateNewStock(int $currentStok, int $amount, string $type): int {
        // Obat generik mungkin memiliki degradasi yang berbeda
        // Untuk contoh ini, kita asumsikan tidak ada perbedaan signifikan
        return parent::calculateNewStock($currentStok, $amount, $type);
    }

    /**
     * Override metode daysUntilExpired untuk obat generik
     * Demonstrasi Polimorfisme: perhitungan masa kadaluarsa berbeda
     * @param string $expDate Tanggal kadaluarsa (format YYYY-MM-DD)
     * @return int Hari hingga kadaluarsa
     */
    public function daysUntilExpired(string $expDate): int {
        // Obat generik mungkin memiliki stabilitas yang berbeda
        // Untuk contoh ini, kita kurangi 5 hari sebagai faktor keamanan
        $days = parent::daysUntilExpired($expDate);
        return max($days - 5, -365); // Maksimal -365 hari (sudah kadaluarsa lebih dari 1 tahun)
    }
}
?>