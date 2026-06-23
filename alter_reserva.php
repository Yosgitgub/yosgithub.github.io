<?php
require_once 'db.php';

try {
    // Agregar columna hora_fin a la tabla reserva
    $pdo->exec("ALTER TABLE reserva ADD COLUMN hora_fin TIME NULL AFTER hora_reserva");
    echo "Columna 'hora_fin' agregada correctamente a la tabla 'reserva'.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "La columna 'hora_fin' ya existe.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
