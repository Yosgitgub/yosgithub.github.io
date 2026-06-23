<?php
require_once 'db.php';
$stmt = $pdo->query("SELECT * FROM detalle_receta LIMIT 10");
$detalles = $stmt->fetchAll();
echo "Detalles de Receta:\n";
print_r($detalles);

$stmt = $pdo->query("SELECT * FROM platillo LIMIT 5");
$platillos = $stmt->fetchAll();
echo "\nPlatillos:\n";
print_r($platillos);
?>
