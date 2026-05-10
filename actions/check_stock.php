<?php
// actions/check_stock.php
require_once dirname(__DIR__) . '/init.php';

$stats = $obatModel->getStats();
header('Content-Type: application/json');
echo json_encode([
    'critical_count' => (int)$stats['kritis'],
    'status' => 'success'
]);
?>
