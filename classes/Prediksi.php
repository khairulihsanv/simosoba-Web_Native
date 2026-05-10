<?php
// classes/Prediksi.php

class Prediksi extends Obat {
    
    public function getWeeklyAverageUsage($obat_id) {
        $query = "SELECT SUM(jumlah) as total FROM transaksi 
                  WHERE obat_id = :obat_id AND tipe = 'keluar' 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":obat_id", $obat_id);
        $stmt->execute();
        $row = $stmt->fetch();
        return ($row['total'] ?? 0) / 7;
    }

    public function predictDaysRemaining($obat_id, $current_stock) {
        $avgUsage = $this->getWeeklyAverageUsage($obat_id);
        if ($avgUsage <= 0) return "∞";
        return floor($current_stock / $avgUsage);
    }
}
?>
