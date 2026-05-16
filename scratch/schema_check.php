<?php
require_once __DIR__ . '/../config/database.php';

echo "Checking tables in " . $db . "\n";

try {
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $table = $row[0];
        echo "Table: $table\n";
        $stmtCol = $pdo->query("DESCRIBE $table");
        while ($col = $stmtCol->fetch(PDO::FETCH_ASSOC)) {
            echo "  Column: " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
