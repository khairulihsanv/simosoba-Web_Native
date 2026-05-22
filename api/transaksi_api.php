<?php
// api/transaksi_api.php – Transaction JSON API
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
}
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Ensure table
    $pdo->exec("CREATE TABLE IF NOT EXISTS transaksi (
        id INT AUTO_INCREMENT PRIMARY KEY, obat_id INT NOT NULL,
        tipe ENUM('masuk','keluar','input','output') DEFAULT 'masuk',
        jumlah INT NOT NULL DEFAULT 0, keterangan TEXT DEFAULT NULL,
        user_id INT DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($method === 'GET') {
        $rows = $pdo->query("SELECT t.*, o.nama AS nama_obat FROM transaksi t LEFT JOIN obat o ON t.obat_id=o.id ORDER BY t.created_at DESC LIMIT 200")->fetchAll();
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    if ($method === 'POST') {
        $body     = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $obat_id  = (int)($body['obat_id'] ?? 0);
        $tipe     = in_array($body['tipe'] ?? '', ['masuk','keluar','input','output']) ? $body['tipe'] : 'masuk';
        $jumlah   = max(1, (int)($body['jumlah'] ?? 1));
        $ket      = trim($body['keterangan'] ?? '');

        if (!$obat_id) { echo json_encode(['success'=>false,'error'=>'Pilih obat terlebih dahulu.']); exit; }

        // Check stock for keluar
        if (in_array($tipe, ['keluar','output'])) {
            $stok = (int)$pdo->query("SELECT stok FROM obat WHERE id=$obat_id")->fetchColumn();
            if ($stok < $jumlah) {
                echo json_encode(['success'=>false,'error'=>"Stok tidak cukup. Tersedia: $stok unit."]);
                exit;
            }
            $pdo->prepare("UPDATE obat SET stok = stok - ?, updated_at=NOW() WHERE id=?")->execute([$jumlah, $obat_id]);
        } else {
            $pdo->prepare("UPDATE obat SET stok = stok + ?, updated_at=NOW() WHERE id=?")->execute([$jumlah, $obat_id]);
        }

        $pdo->prepare("INSERT INTO transaksi (obat_id, tipe, jumlah, keterangan, user_id, created_at) VALUES (?,?,?,?,?,NOW())")
            ->execute([$obat_id, $tipe, $jumlah, $ket, $_SESSION['user_id'] ?? null]);

        echo json_encode(['success'=>true,'message'=>'Transaksi berhasil disimpan.']);
        exit;
    }

} catch (Throwable $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
