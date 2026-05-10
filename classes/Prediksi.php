<?php
// classes/Prediksi.php

class Prediksi extends Obat {
    
    public function getWeeklyAverageUsage(int $obat_id): float {
        $query = "SELECT SUM(jumlah) as total FROM transaksi 
                  WHERE obat_id = :obat_id AND tipe = 'keluar' 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":obat_id", $obat_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return (float)($row['total'] ?? 0) / 7;
    }

    public function predictDaysRemaining(int $obat_id, int $current_stock): string {
        $avgUsage = $this->getWeeklyAverageUsage($obat_id);
        if ($avgUsage <= 0) return "∞";
        return (string)floor($current_stock / $avgUsage);
    }
}
?>
