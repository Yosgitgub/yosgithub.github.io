<?php
require_once 'db.php';

try {
    // Mover mesa 1 y 2 a Salón Principal (Zona A)
    $stmt = $pdo->prepare("UPDATE mesa SET zona_mesa = 'Salón Principal', clase_mesa = 'Regular' WHERE num_mesa IN (1, 2)");
    $stmt->execute();
    echo "Mesas 1 y 2 movidas a Salón Principal (Zona A). Filas afectadas: " . $stmt->rowCount() . "\n";

    // Mover mesa 5 a Terraza (Zona C - Eventos)
    $stmt2 = $pdo->prepare("UPDATE mesa SET zona_mesa = 'Terraza Aire Libre', clase_mesa = 'Exterior' WHERE num_mesa = 5");
    $stmt2->execute();
    echo "Mesa 5 movida a Terraza/Eventos (Zona C). Filas afectadas: " . $stmt2->rowCount() . "\n";

    echo "\n¡Listo! Ya puedes cerrar esta página.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
