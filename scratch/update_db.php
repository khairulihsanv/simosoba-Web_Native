<?php
require_once 'config/database.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_token VARCHAR(255) DEFAULT NULL;");
    echo "Column added successfully or already exists.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
