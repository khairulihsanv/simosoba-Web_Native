<?php
// api/supplier_api.php
if (!defined('BASE_PATH')) { require_once dirname(__DIR__) . '/init.php'; }
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
        id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(150) NOT NULL,
        kategori VARCHAR(100) DEFAULT NULL, alamat TEXT DEFAULT NULL,
        telepon VARCHAR(50) DEFAULT NULL, email VARCHAR(120) DEFAULT NULL,
        kontak_pic VARCHAR(100) DEFAULT NULL, catatan TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($method === 'DELETE' || $action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        $pdo->prepare("DELETE FROM suppliers WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true,'message'=>'Supplier dihapus.']); exit;
    }

    if ($method === 'GET') {
        echo json_encode(['success'=>true,'data'=>$pdo->query("SELECT * FROM suppliers ORDER BY nama ASC")->fetchAll()]); exit;
    }

    if ($method === 'POST') {
        $b = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = (int)($b['id'] ?? 0);
        $nama = trim($b['nama'] ?? '');
        if (!$nama) { echo json_encode(['success'=>false,'error'=>'Nama wajib diisi.']); exit; }

        if ($id) {
            $pdo->prepare("UPDATE suppliers SET nama=?,kategori=?,alamat=?,telepon=?,email=?,kontak_pic=?,catatan=? WHERE id=?")
                ->execute([$nama,$b['kategori']??'',$b['alamat']??'',$b['telepon']??'',$b['email']??'',$b['kontak_pic']??'',$b['catatan']??'',$id]);
            echo json_encode(['success'=>true,'message'=>'Supplier diperbarui.']);
        } else {
            $pdo->prepare("INSERT INTO suppliers (nama,kategori,alamat,telepon,email,kontak_pic,catatan,created_at) VALUES(?,?,?,?,?,?,?,NOW())")
                ->execute([$nama,$b['kategori']??'',$b['alamat']??'',$b['telepon']??'',$b['email']??'',$b['kontak_pic']??'',$b['catatan']??'']);
            echo json_encode(['success'=>true,'message'=>'Supplier ditambahkan.']);
        }
        exit;
    }
} catch (Throwable $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
