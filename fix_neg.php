<?php
require_once 'db.php';
$pdo->exec("UPDATE ingredientes SET stock_actual = 0 WHERE stock_actual < 0");
echo "Fixed negative stock";
?>
