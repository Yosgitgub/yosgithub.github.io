<?php
require_once 'db.php';
$pdo->exec("UPDATE ingredientes SET tipo_ingre='Kilos (Kg)' WHERE tipo_ingre='Kilos'");
$pdo->exec("UPDATE ingredientes SET tipo_ingre='Litros (L)' WHERE tipo_ingre='Litros'");
$pdo->exec("UPDATE ingredientes SET tipo_ingre='Unidades (uds)' WHERE tipo_ingre='Unidades'");
echo 'OK';
?>
