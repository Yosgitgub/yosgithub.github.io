<?php
require_once 'db.php';
try {
    $pdo->exec("ALTER TABLE reserva ADD COLUMN cant_personas INT DEFAULT 1");
    echo "Exito";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
