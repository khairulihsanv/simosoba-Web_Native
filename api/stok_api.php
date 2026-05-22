<?php
/**
 * api/stok_api.php – Inventory CRUD JSON API
 */
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/init.php';
}
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Ensure obat table has required columns
ensureObatColumns($pdo);

try {
    // ── DELETE ───────────────────────────────────────────────
    if ($method === 'DELETE' || $action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'error' => 'Invalid ID']); exit; }

        $pdo->prepare("DELETE FROM obat WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Obat berhasil dihapus.']);
        exit;
    }

    // ── GET (list) ───────────────────────────────────────────
    if ($method === 'GET') {
        $search = $_GET['q'] ?? '';
        $cat    = $_GET['kategori'] ?? '';
        $status = $_GET['status'] ?? '';

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = '(nama LIKE ? OR kategori LIKE ? OR barcode LIKE ?)';
            $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
        }
        if ($cat) { $where[] = 'kategori = ?'; $params[] = $cat; }
        if ($status === 'low')  $where[] = 'stok > 0 AND stok <= stok_min';
        if ($status === 'out')  $where[] = 'stok = 0';
        if ($status === 'in')   $where[] = 'stok > stok_min';

        $sql  = "SELECT * FROM obat WHERE " . implode(' AND ', $where) . " ORDER BY nama ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    // ── POST (create/update) ─────────────────────────────────
    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $id        = (int)($body['id'] ?? 0);
        $nama      = trim($body['nama'] ?? '');
        $kategori  = trim($body['kategori'] ?? '');
        $satuan    = trim($body['satuan'] ?? 'Tablet');
        $pabrik    = trim($body['pabrik'] ?? '');
        $barcode   = trim($body['barcode'] ?? '');
        $batch     = trim($body['batch'] ?? '');
        $stok      = max(0, (int)($body['stok'] ?? 0));
        $stok_min  = max(0, (int)($body['stok_min'] ?? 10));
        $harga     = max(0, (float)($body['harga'] ?? 0));
        $harga_beli= max(0, (float)($body['harga_beli'] ?? 0));
        $exp_date  = $body['exp_date'] ?? null;
        $lokasi    = trim($body['lokasi'] ?? '');
        $catatan   = trim($body['catatan'] ?? '');
        $supplier_id = $body['supplier_id'] ? (int)$body['supplier_id'] : null;

        if (!$nama) {
            echo json_encode(['success' => false, 'error' => 'Nama obat wajib diisi.']);
            exit;
        }

        if ($id) {
            // UPDATE
            $sql = "UPDATE obat SET nama=?, kategori=?, satuan=?, pabrik=?, barcode=?, batch=?,
                        stok=?, stok_min=?, harga=?, harga_beli=?, exp_date=?,
                        lokasi=?, catatan=?, supplier_id=?, updated_at=NOW()
                    WHERE id=?";
            $pdo->prepare($sql)->execute([
                $nama, $kategori, $satuan, $pabrik, $barcode, $batch,
                $stok, $stok_min, $harga, $harga_beli, $exp_date ?: null,
                $lokasi, $catatan, $supplier_id, $id
            ]);

            // Log transaction for stock change
            $oldStok = (int)$pdo->query("SELECT stok FROM obat WHERE id=$id")->fetchColumn();

            echo json_encode(['success' => true, 'message' => 'Obat berhasil diperbarui.', 'id' => $id]);
        } else {
            // CREATE
            $sql = "INSERT INTO obat (nama, kategori, satuan, pabrik, barcode, batch, stok, stok_min,
                        harga, harga_beli, exp_date, lokasi, catatan, supplier_id, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $pdo->prepare($sql)->execute([
                $nama, $kategori, $satuan, $pabrik, $barcode, $batch,
                $stok, $stok_min, $harga, $harga_beli, $exp_date ?: null,
                $lokasi, $catatan, $supplier_id
            ]);
            $newId = (int)$pdo->lastInsertId();

            // Auto-log as stock-in
            if ($stok > 0) {
                logTranaksi($pdo, $newId, 'masuk', $stok, 'Initial stock entry');
            }

            echo json_encode(['success' => true, 'message' => 'Obat berhasil ditambahkan.', 'id' => $newId]);
        }
        exit;
    }

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

/* ── Helpers ─────────────────────────────────────────────── */
function ensureObatColumns(PDO $pdo): void {
    static $done = false;
    if ($done) return; $done = true;

    $cols = ['barcode VARCHAR(100) DEFAULT NULL',
             'batch VARCHAR(100) DEFAULT NULL',
             'harga_beli DECIMAL(15,2) DEFAULT 0',
             'lokasi VARCHAR(100) DEFAULT NULL',
             'catatan TEXT DEFAULT NULL',
             'supplier_id INT DEFAULT NULL',
             'updated_at DATETIME DEFAULT NULL'];
    foreach ($cols as $def) {
        $colName = explode(' ', $def)[0];
        try {
            $pdo->exec("ALTER TABLE obat ADD COLUMN $def");
        } catch (Throwable $e) {}
    }

    // Ensure obat table has updated_at
    try { $pdo->exec("ALTER TABLE obat ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP"); } catch(Throwable $e) {}
    try { $pdo->exec("ALTER TABLE obat ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"); } catch(Throwable $e) {}
}

function logTranaksi(PDO $pdo, int $obatId, string $tipe, int $jumlah, string $ket = ''): void {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS transaksi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            obat_id INT NOT NULL,
            tipe ENUM('masuk','keluar','input','output') DEFAULT 'masuk',
            jumlah INT NOT NULL DEFAULT 0,
            keterangan TEXT DEFAULT NULL,
            user_id INT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_obat (obat_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->prepare("INSERT INTO transaksi (obat_id, tipe, jumlah, keterangan, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
            ->execute([$obatId, $tipe, $jumlah, $ket, $_SESSION['user_id'] ?? null]);
    } catch (Throwable $e) {}
}
