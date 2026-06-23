<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

require_once '../db.php';
$cedula_cli = $_SESSION['cedula_cli'] ?? '';

if (empty($cedula_cli)) {
    echo json_encode(['success' => false, 'message' => 'Usuario no identificado correctamente.']);
    exit;
}

try {
    // 1. Obtener Reservas Activas
    $stmt_res = $pdo->prepare("SELECT id_reserva, num_mesa, fecha_reserva, hora_reserva, estado_reserva, cant_personas FROM reserva WHERE cedula_cli = ? ORDER BY fecha_reserva DESC");
    $stmt_res->execute([$cedula_cli]);
    $reservas = $stmt_res->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener Pedidos
    $stmt_ped = $pdo->prepare("SELECT id_pedido, num_mesa, estado, fecha_hora, hora_entrega FROM pedido WHERE cedula_cli = ? ORDER BY fecha_hora DESC");
    $stmt_ped->execute([$cedula_cli]);
    $pedidos = $stmt_ped->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pedidos as &$pedido) {
        $stmt_det = $pdo->prepare("
            SELECT dp.cod_plat, dp.cantidad, pl.nom_plat, pl.precio_plat
            FROM detalle_pedido dp
            JOIN platillo pl ON dp.cod_plat = pl.cod_plat
            WHERE dp.id_pedido = ?
        ");
        $stmt_det->execute([$pedido['id_pedido']]);
        $detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
        
        $subtotal = 0;
        foreach ($detalles as $det) {
            $subtotal += $det['cantidad'] * $det['precio_plat'];
        }

        $pedido['detalles'] = $detalles;
        $pedido['subtotal'] = number_format($subtotal, 2, '.', '');
    }

    echo json_encode([
        'success' => true,
        'reservas' => $reservas,
        'pedidos' => $pedidos
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
