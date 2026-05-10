<?php
// classes/Obat.php

class Obat {
    protected PDO $db;
    protected string $table = "obat";

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Helper: Execute Statement
     */
    private function execute(string $sql, array $params = []): PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function getAll(): array {
        return $this->execute("SELECT * FROM {$this->table} ORDER BY nama ASC")->fetchAll();
    }

    public function getStats(): array {
        return [
            'total'   => (int)$this->execute("SELECT COUNT(*) FROM {$this->table}")->fetchColumn(),
            'kritis'  => (int)$this->execute("SELECT COUNT(*) FROM {$this->table} WHERE stok < stok_min AND stok > 0")->fetchColumn(),
            'habis'   => (int)$this->execute("SELECT COUNT(*) FROM {$this->table} WHERE stok = 0")->fetchColumn(),
            'expired' => (int)$this->execute("SELECT COUNT(*) FROM {$this->table} WHERE exp_date <= CURDATE()")->fetchColumn(),
        ];
    }

    public function getLowStock(): array {
        return $this->execute("SELECT * FROM {$this->table} WHERE stok < stok_min AND stok > 0 ORDER BY stok ASC")->fetchAll();
    }

    public function save(array $data): bool {
        if (empty($data['nama']) || empty($data['exp_date'])) return false;

        $sql = "INSERT INTO {$this->table} (nama, kategori, satuan, harga, stok, stok_min, exp_date) 
                VALUES (:nama, :kategori, :satuan, :harga, :stok, :stok_min, :exp_date)";
        
        try {
            return $this->execute($sql, $data)->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Save Obat Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateStock(int $id, int $amount, string $type, int $user_id, string $note = ''): bool {
        $obat = $this->execute("SELECT stok FROM {$this->table} WHERE id = ?", [$id])->fetch();
        if (!$obat) return false;

        $new_stock = ($type === 'masuk') ? (int)$obat['stok'] + $amount : (int)$obat['stok'] - $amount;
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
            error_log("Update Stock Error: " . $e->getMessage());
            return false;
        }
    }
}
?>
