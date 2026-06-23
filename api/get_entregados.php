<?php
session_start();
header('Content-Type: application/json');

// Validar acceso: solo meseros
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo'] ?? '') !== 'mesero') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

// Obtener pedidos entregados que aún no tienen factura
try {
    $stmt = $pdo->query("
        SELECT p.id_pedido, p.num_mesa, p.estado, p.fecha_hora, p.cedula_cli,
               c.nom_cli, c.ap_cli
        FROM pedido p
        LEFT JOIN cliente c ON p.cedula_cli = c.cedula_cli
        WHERE p.estado = 'Entregado'
        ORDER BY p.fecha_hora DESC
        LIMIT 20
    ");
    
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($pedidos as &$pedido) {
        $stmt_det = $pdo->prepare("
            SELECT dp.id_detalle, dp.cod_plat, dp.cantidad, dp.notas, 
                   pl.nom_plat, pl.precio_plat
            FROM detalle_pedido dp
            JOIN platillo pl ON dp.cod_plat = pl.cod_plat
            WHERE dp.id_pedido = ?
        ");
        $stmt_det->execute([$pedido['id_pedido']]);
        $pedido['detalles'] = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular subtotal del pedido
        $subtotal = 0;
        foreach ($pedido['detalles'] as $item) {
            $subtotal += $item['cantidad'] * $item['precio_plat'];
        }
        $pedido['subtotal'] = number_format($subtotal, 2, '.', '');
    }
    
    echo json_encode(['success' => true, 'pedidos' => $pedidos]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
