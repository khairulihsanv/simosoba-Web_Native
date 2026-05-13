<?php
require 'config/database.php';
$roles = $pdo->query('SELECT DISTINCT role FROM users')->fetchAll(PDO::FETCH_COLUMN);
print_r($roles);
