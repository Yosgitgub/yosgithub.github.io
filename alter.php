<?php
require_once 'db.php';
try {
    $pdo->exec("ALTER TABLE pedido ADD COLUMN cedula_emp VARCHAR(20) DEFAULT NULL");
    $pdo->exec("ALTER TABLE pedido ADD CONSTRAINT fk_pedido_mesero FOREIGN KEY (cedula_emp) REFERENCES empleado(cedula_emp)");
    echo "Exito";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
