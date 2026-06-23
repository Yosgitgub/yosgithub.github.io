<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE platillo ADD COLUMN tiempo_preparacion INT DEFAULT 15 AFTER img_platillo");
    echo "Added tiempo_preparacion.\n";
} catch (Exception $e) { echo "Error 1: " . $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE pedido ADD COLUMN hora_entrega DATETIME AFTER fecha_hora");
    echo "Added hora_entrega.\n";
} catch (Exception $e) { echo "Error 2: " . $e->getMessage() . "\n"; }
?>
