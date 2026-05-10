<?php
define('BASE_PATH', __DIR__);
session_start();

require_once BASE_PATH . '/config/database.php';
$db = (new Database())->getConnection();

spl_autoload_register(fn($class) => require_once BASE_PATH . "/classes/$class.php");

$obatModel = new Obat($db);
$prediksiModel = new Prediksi($db);
?>
