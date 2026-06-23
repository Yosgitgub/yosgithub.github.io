<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || (strtolower($_SESSION['cargo']) !== 'mesero' && strtolower($_SESSION['cargo']) !== 'administrador')) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

require_once '../db.php';

try {
    // Obtener todas las reservas con los datos de la mesa y el cliente
    $sql = "
        SELECT 
            r.id_reserva, 
            r.num_mesa, 
            r.fecha_reserva, 
            r.hora_reserva, 
            r.cant_personas, 
            r.estado_reserva,
            c.nom_cli, 
            c.ap_cli,
            c.tel_cli,
            m.zona_mesa
        FROM reserva r
        JOIN cliente c ON r.cedula_cli = c.cedula_cli
        JOIN mesa m ON r.num_mesa = m.num_mesa
        ORDER BY r.fecha_reserva ASC, r.hora_reserva ASC
    ";
    
    $stmt = $pdo->query($sql);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'reservas' => $reservas
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
