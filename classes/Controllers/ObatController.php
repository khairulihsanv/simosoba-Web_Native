<?php
// classes/Controllers/ObatController.php

namespace Controllers;

use Obat;
use Prediksi;
use PDO;

class ObatController {
    private Obat $obatModel;
    private Prediksi $prediksiModel;

    /**
     * Dependency Injection melalui Global Instance di init.php
     */
    public function __construct() {
        global $pdo; 
        if (!$pdo) {
            require_once dirname(__DIR__, 2) . '/config/database.php';
        }
        $this->obatModel = new Obat($pdo);
        $this->prediksiModel = new Prediksi($pdo);
    }

    /**
     * Ambil semua data obat
     */
    public function index(): array {
        return $this->obatModel->getAll();
    }

    /**
     * Ambil statistik untuk Dashboard (Reminder & Prediksi)
     */
    public function getDashboardStats(): array {
        $stats = $this->obatModel->getStats();
        $lowStock = $this->obatModel->getLowStock();
        
        // Tambahkan prediksi sisa hari untuk setiap stok kritis
        foreach ($lowStock as &$item) {
            $item['prediksi_hari'] = $this->prediksiModel->predictDaysRemaining((int)$item['id'], (int)$item['stok']);
        }

        return [
            'summary' => $stats,
            'critical_items' => $lowStock,
            'prediction_trend' => '+12%',
            'trends' => $this->getTrendStats()
        ];
    }

    /**
     * Ambil data tren mutasi 7 hari terakhir
     */
    private function getTrendStats(): array {
        global $pdo;
        $labels = [];
        $masuk = [];
        $keluar = [];
        $auth = new \Auth();
        $fDiv = $auth->getDivisiFilter('o');

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date));
            
            $qM = "SELECT SUM(t.jumlah) as total FROM transaksi t JOIN obat o ON t.obat_id=o.id WHERE t.tipe='masuk' AND DATE(t.created_at)=? AND $fDiv";
            $stmtM = $pdo->prepare($qM);
            $stmtM->execute([$date]);
            $masuk[] = (int)$stmtM->fetchColumn() ?: 0;

            $qK = "SELECT SUM(t.jumlah) as total FROM transaksi t JOIN obat o ON t.obat_id=o.id WHERE t.tipe='keluar' AND DATE(t.created_at)=? AND $fDiv";
            $stmtK = $pdo->prepare($qK);
            $stmtK->execute([$date]);
            $keluar[] = (int)$stmtK->fetchColumn() ?: 0;
        }

        return ['labels' => $labels, 'masuk' => $masuk, 'keluar' => $keluar];
    }

    /**
     * Simpan obat baru
     */
    public function store(array $data): bool {
        return $this->obatModel->save($data);
    }

    /**
     * Update stok atau data obat
     */
    public function update(int $id, int $amount, string $type, int $user_id): bool {
        return $this->obatModel->updateStock($id, $amount, $type, $user_id);
    }

    /**
     * Hapus obat
     */
    public function delete(int $id): bool {
        // Implementasi hapus jika diperlukan di model
        return false; 
    }
}
?>
