<?php
// classes/Obat.php

/**
 * Kelas dasar Obat yang mengimplementasikan prinsip OOP
 * - Enkapsulasi: menyembunyikan data sensitif dengan private/protected properties
 * - Inheritance: dapat diturunkan untuk jenis obat spesifik
 * - Polimorfisme: metode dapat dioverride oleh kelas turunan
 */
abstract class Obat {
    // Enkapsulasi: properties yang disembunyikan dari akses langsung
    protected PDO $db;
    protected string $table = "obat";
    
    // Data sensitif yang dienkapsulasi
    private float $hargaBeli; // Harga beli obat (sensitif)
    private string $kategori; // Kategori obat
    private string $nama;     // Nama obat
    private string $satuan;   // Satuan obat
    private int $stok;        // Stok saat ini
    private int $stokMin;     // Stok minimum
    private string $expDate;  // Tanggal kadaluarsa

    /**
     * Constructor dengan Dependency Injection
     * @param PDO $db Instance PDO untuk koneksi database
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Getter dan Setter untuk enkapsulasi
    public function getHargaBeli(): float {
        return $this->hargaBeli;
    }

    public function setHargaBeli(float $harga): void {
        // Validasi: harga beli tidak boleh negatif
        if ($harga < 0) {
            throw new InvalidArgumentException("Harga beli tidak boleh negatif");
        }
        $this->hargaBeli = $harga;
    }

    public function getKategori(): string {
        return $this->kategori;
    }

    public function setKategori(string $kategori): void {
        $this->kategori = $kategori;
    }

    public function getNama(): string {
        return $this->nama;
    }

    public function setNama(string $nama): void {
        if (empty(trim($nama))) {
            throw new InvalidArgumentException("Nama obat tidak boleh kosong");
        }
        $this->nama = $nama;
    }

    public function getSatuan(): string {
        return $this->satuan;
    }

    public function setSatuan(string $satuan): void {
        $this->satuan = $satuan;
    }

    public function getStok(): int {
        return $this->stok;
    }

    public function setStok(int $stok): void {
        if ($stok < 0) {
            throw new InvalidArgumentException("Stok tidak boleh negatif");
        }
        $this->stok = $stok;
    }

    public function getStokMin(): int {
        return $this->stokMin;
    }

    public function setStokMin(int $stokMin): void {
        if ($stokMin < 0) {
            throw new InvalidArgumentException("Stok minimum tidak boleh negatif");
        }
        $this->stokMin = $stokMin;
    }

    public function getExpDate(): string {
        return $this->expDate;
    }

    public function setExpDate(string $expDate): void {
        // Validasi format tanggal YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expDate)) {
            throw new InvalidArgumentException("Format tanggal harus YYYY-MM-DD");
        }
        $this->expDate = $expDate;
    }

    /**
     * Helper: Execute Statement dengan Prepared Statement (mencegah SQL Injection)
     * @param string $sql Query SQL dengan placeholder
     * @param array $params Parameter untuk query
     * @return PDOStatement Statement yang dieksekusi
     */
    protected function execute(string $sql, array $params = []): PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Mendapatkan semua data obat
     * @return array Array obat
     */
    public function getAll(): array {
        return $this->execute("SELECT * FROM {$this->table} ORDER BY nama ASC")->fetchAll();
    }

    /**
     * Mendapatkan statistik obat
     * @return array Statistik obat (total, kritis, habis, expired)
     */
    public function getStats(): array {
        return [
            'total'   => (int)$this->execute("SELECT COUNT(*) FROM {$this->table}")->fetchColumn(),
            'kritis'  => (int)$this->execute("SELECT COUNT(*) FROM {$this->table} WHERE stok < stok_min AND stok > 0")->fetchColumn(),
            'habis'   => (int)$this->execute("SELECT COUNT(*) FROM {$this->table} WHERE stok = 0")->fetchColumn(),
            'expired' => (int)$this->execute("SELECT COUNT(*) FROM {$this->table} WHERE exp_date <= CURDATE()")->fetchColumn(),
        ];
    }

    /**
     * Mendapatkan obat dengan stok rendah
     * @return array Array obat dengan stok di bawah minimum
     */
    public function getLowStock(): array {
        return $this->execute("SELECT * FROM {$this->table} WHERE stok < stok_min AND stok > 0 ORDER BY stok ASC")->fetchAll();
    }

    /**
     * Menyimpan data obat ke database
     * @param array $data Data obat yang akan disimpan
     * @return bool True jika berhasil, False jika gagal
     */
    public function save(array $data): bool {
        // Validasi data yang diperlukan
        if (empty($data['nama']) || empty($data['exp_date'])) {
            return false;
        }

        // Set properties melalui setter untuk memastikan validasi
        $this->setNama($data['nama']);
        $this->setKategori($data['kategori'] ?? 'UMUM');
        $this->setSatuan($data['satuan'] ?? 'pcs');
        $this->setHargaBeli((float)($data['harga'] ?? 0));
        $this->setStok((int)($data['stok'] ?? 0));
        $this->setStokMin((int)($data['stok_min'] ?? 0));
        $this->setExpDate($data['exp_date']);

        $sql = "INSERT INTO {$this->table} (nama, kategori, satuan, harga, stok, stok_min, exp_date)
                VALUES (:nama, :kategori, :satuan, :harga, :stok, :stok_min, :exp_date)";

        try {
            return $this->execute($sql, [
                ':nama' => $this->getNama(),
                ':kategori' => $this->getKategori(),
                ':satuan' => $this->getSatuan(),
                ':harga' => $this->getHargaBeli(),
                ':stok' => $this->getStok(),
                ':stok_min' => $this->getStokMin(),
                ':exp_date' => $this->getExpDate()
            ])->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Save Obat Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Memperbarui stok obat (polimorfisme: dapat dioverride oleh kelas turunan)
     * @param int $id ID obat
     * @param int $amount Jumlah yang ditambahkan/dikurangkan
     * @param string $type Jenis transaksi ('masuk' atau 'keluar')
     * @param int $user_id ID user yang melakukan transaksi
     * @param string $note Catatan tambahan
     * @return bool True jika berhasil, False jika gagal
     */
    public function updateStock(int $id, int $amount, string $type, int $user_id, string $note = ''): bool {
        // Mendapatkan stok saat ini
        $obat = $this->execute("SELECT stok FROM {$this->table} WHERE id = ?", [$id])->fetch();
        if (!$obat) {
            return false;
        }

        $currentStok = (int)$obat['stok'];
        
        // Polimorfisme: perhitungan stok dapat dioverride oleh kelas turunan
        $newStock = $this->calculateNewStock($currentStok, $amount, $type);
        
        if ($newStock < 0) {
            return false; // Stok tidak boleh negatif
        }

        $this->db->beginTransaction();
        try {
            // Update stok obat
            $this->execute("UPDATE {$this->table} SET stok = ? WHERE id = ?", [$newStock, $id]);
            
            // Catat transaksi
            $this->execute("INSERT INTO transaksi (obat_id, user_id, tipe, jumlah, stok_sesudah, keterangan)
                            VALUES (?, ?, ?, ?, ?, ?)", [$id, $user_id, $type, $amount, $newStock, $note]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Update Stock Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Menghitung stok baru (polimorfisme: dapat dioverride oleh kelas turunan)
     * @param int $currentStok Stok saat ini
     * @param int $amount Jumlah yang ditambahkan/dikurangkan
     * @param string $type Jenis transaksi ('masuk' atau 'keluar')
     * @return int Stok baru setelah perhitungan
     */
    protected function calculateNewStock(int $currentStok, int $amount, string $type): int {
        return ($type === 'masuk') ? ($currentStok + $amount) : ($currentStok - $amount);
    }

    /**
     * Menghitung masa kadaluarsa obat (polimorfisme: dapat dioverride oleh kelas turunan)
     * @param string $expDate Tanggal kadaluarsa (format YYYY-MM-DD)
     * @return int Hari hingga kadaluarsa (negatif jika sudah kadaluarsa)
     */
    public function daysUntilExpired(string $expDate): int {
        $today = new DateTime();
        $expire = new DateTime($expDate);
        $interval = $today->diff($expire);
        return (int)$interval->format('%r%a');
    }

    /**
     * Memeriksa apakah obat sudah kadaluarsa
     * @param string $expDate Tanggal kadaluarsa (format YYYY-MM-DD)
     * @return bool True jika sudah kadaluarsa
     */
    public function isExpired(string $expDate): bool {
        return $this->daysUntilExpired($expDate) < 0;
    }
}
?>