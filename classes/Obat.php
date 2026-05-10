<?php
// classes/Obat.php - Centralized Logic (DRY)

class Obat {
    protected $db;
    protected $table = "obat";

    public function __construct($db) {
        $this->db = $db;
    }

    // Helper: Execute Statement
    private function execute($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function getAll() {
        return $this->execute("SELECT * FROM {$this->table} ORDER BY nama ASC")->fetchAll();
    }

    public function getStats() {
        return [
            'total'   => $this->execute("SELECT COUNT(*) FROM {$this->table}")->fetchColumn(),
            'kritis'  => $this->execute("SELECT COUNT(*) FROM {$this->table} WHERE stok < stok_min AND stok > 0")->fetchColumn(),
            'habis'   => $this->execute("SELECT COUNT(*) FROM {$this->table} WHERE stok = 0")->fetchColumn(),
            'expired' => $this->execute("SELECT COUNT(*) FROM {$this->table} WHERE exp_date <= CURDATE()")->fetchColumn(),
        ];
    }

    public function save($data) {
        // Guard Clause: Simple validation
        if (empty($data['nama']) || empty($data['exp_date'])) return false;

        $sql = "INSERT INTO {$this->table} (nama, kategori, satuan, harga, stok, stok_min, exp_date) 
                VALUES (:nama, :kategori, :satuan, :harga, :stok, :stok_min, :exp_date)";
        return $this->execute($sql, $data);
    }

    public function updateStock($id, $amount, $type, $user_id, $note = '') {
        // KISS: Handle stock and transaction log in one place
        $obat = $this->execute("SELECT stok FROM {$this->table} WHERE id = ?", [$id])->fetch();
        if (!$obat) return false;

        $new_stock = ($type === 'masuk') ? $obat['stok'] + $amount : $obat['stok'] - $amount;
        if ($new_stock < 0) return false;

        $this->db->beginTransaction();
        try {
            $this->execute("UPDATE {$this->table} SET stok = ? WHERE id = ?", [$new_stock, $id]);
            $this->execute("INSERT INTO transaksi (obat_id, user_id, tipe, jumlah, stok_sesudah, keterangan) 
                            VALUES (?, ?, ?, ?, ?, ?)", [$id, $user_id, $type, $amount, $new_stock, $note]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>
