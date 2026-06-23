<?php
session_start();
header('Content-Type: application/json');

// Validar acceso
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'restaurante') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

$id_pedido = $_POST['id_pedido'] ?? null;
$estado = $_POST['estado'] ?? null;

if (!$id_pedido || !$estado) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Solo permitir estados válidos
$estados_validos = ['Pendiente', 'En espera', 'Recibido', 'Listo', 'Entregado'];
if (!in_array($estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit;
}

try {
    if ($estado === 'Recibido') {
        // Calcular el tiempo de preparación máximo de los platillos pedidos
        $stmt_time = $pdo->prepare("
            SELECT MAX(p.tiempo_preparacion) AS max_time 
            FROM detalle_pedido dp 
            JOIN platillo p ON dp.cod_plat = p.cod_plat 
            WHERE dp.id_pedido = ?
        ");
        $stmt_time->execute([$id_pedido]);
        $row_time = $stmt_time->fetch(PDO::FETCH_ASSOC);
        $max_prep_time = ($row_time && $row_time['max_time']) ? intval($row_time['max_time']) : 15;

        // Calcular el total de minutos (5 minutos fijos + máximo preparación)
        $total_minutes = 5 + $max_prep_time;

        $stmt = $pdo->prepare("UPDATE pedido SET estado = ?, hora_entrega = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id_pedido = ?");
        $stmt->execute([$estado, $total_minutes, $id_pedido]);
    } else {
        $stmt = $pdo->prepare("UPDATE pedido SET estado = ? WHERE id_pedido = ?");
        $stmt->execute([$estado, $id_pedido]);
    }

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Pedido actualizado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
