<?php
session_start();
header('Content-Type: application/json');

// Validar acceso: solo personal del restaurante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'restaurante') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

try {
    // Obtener todos los pedidos con estado En espera, Recibido o Listo
    $stmt = $pdo->query("
        SELECT p.id_pedido, p.num_mesa, p.estado, p.fecha_hora, p.hora_entrega, p.cedula_cli, p.cedula_emp,
               c.nom_cli, c.ap_cli,
               e.nom_emp AS nom_mesero, e.ap_emp AS ap_mesero
        FROM pedido p
        LEFT JOIN cliente c ON p.cedula_cli = c.cedula_cli
        LEFT JOIN empleado e ON p.cedula_emp = e.cedula_emp
        WHERE p.estado IN ('En espera', 'Recibido', 'Listo') 
        ORDER BY p.fecha_hora ASC
    ");
    
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada pedido, obtener sus detalles (los platillos con precios)
    foreach ($pedidos as &$pedido) {
        $stmt_det = $pdo->prepare("
            SELECT dp.id_detalle, dp.cod_plat, dp.cantidad, dp.notas, 
                   p.nom_plat, p.precio_plat
            FROM detalle_pedido dp
            JOIN platillo p ON dp.cod_plat = p.cod_plat
            WHERE dp.id_pedido = ?
        ");
        $stmt_det->execute([$pedido['id_pedido']]);
        $pedido['detalles'] = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular subtotal del pedido
        $subtotal = 0;
        foreach ($pedido['detalles'] as $det) {
            $subtotal += floatval($det['precio_plat']) * intval($det['cantidad']);
        }
        $pedido['subtotal'] = number_format($subtotal, 2, '.', '');
    }
    
    echo json_encode(['success' => true, 'pedidos' => $pedidos]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
