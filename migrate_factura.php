<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE factura ADD COLUMN metodo_pago VARCHAR(50)");
    echo "Added metodo_pago.\n";
} catch (Exception $e) { echo "Error 1: " . $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE factura ADD COLUMN id_pedido INT");
    echo "Added id_pedido.\n";
} catch (Exception $e) { echo "Error 2: " . $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE factura ADD FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido)");
    echo "Added foreign key id_pedido.\n";
} catch (Exception $e) { echo "Error 3: " . $e->getMessage() . "\n"; }
?>
