<?php
session_start();
header('Content-Type: application/json');

require_once '../db.php';

try {
    // 1. Verificar si hay mesas, si no, crearlas (Seeding)
    $stmt_count = $pdo->query("SELECT COUNT(*) FROM mesa");
    if ($stmt_count->fetchColumn() == 0) {
        $pdo->beginTransaction();
        
        // Insertar 5 mesas VIP
        for ($i=1; $i<=5; $i++) {
            $stmt = $pdo->prepare("INSERT INTO mesa (num_mesa, cap_mesa, zona_mesa, clase_mesa, estado_mesa) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$i, 2, 'Salón VIP', 'VIP', 'Disponible']);
        }
        // Insertar 8 mesas Salón Principal
        for ($i=6; $i<=13; $i++) {
            $stmt = $pdo->prepare("INSERT INTO mesa (num_mesa, cap_mesa, zona_mesa, clase_mesa, estado_mesa) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$i, 4, 'Salón Principal', 'Regular', 'Disponible']);
        }
        // Insertar 6 mesas Aire Libre
        for ($i=14; $i<=19; $i++) {
            $stmt = $pdo->prepare("INSERT INTO mesa (num_mesa, cap_mesa, zona_mesa, clase_mesa, estado_mesa) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$i, 6, 'Terraza Aire Libre', 'Exterior', 'Disponible']);
        }
        
        $pdo->commit();
    }

    // 2. Auto-liberar mesas cuya reserva ya expiró (fecha + hora_fin ya pasaron)
    $pdo->exec("
        UPDATE mesa m
        INNER JOIN reserva r ON m.num_mesa = r.num_mesa
        SET m.estado_mesa = 'Disponible'
        WHERE r.estado_reserva = 'Activa'
          AND r.hora_fin IS NOT NULL
          AND CONCAT(r.fecha_reserva, ' ', r.hora_fin) <= NOW()
    ");
    
    // Marcar esas reservas como 'Completada'
    $pdo->exec("
        UPDATE reserva
        SET estado_reserva = 'Completada'
        WHERE estado_reserva = 'Activa'
          AND hora_fin IS NOT NULL
          AND CONCAT(fecha_reserva, ' ', hora_fin) <= NOW()
    ");

    // 3. Obtener todas las mesas
    $stmt = $pdo->query("SELECT * FROM mesa ORDER BY num_mesa ASC");
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'mesas' => $mesas]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
