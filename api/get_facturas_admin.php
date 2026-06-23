<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo'] ?? '') !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

try {
    // Obtener todas las facturas
    $stmt = $pdo->query("
        SELECT f.cedula_emp, f.dia_fact, f.mes_fact, f.año_fact, f.hora_fact, f.iva, 
               f.precio_fact AS subtotal, f.total, f.metodo_pago, f.id_pedido,
               p.num_mesa,
               CONCAT(c.nom_cli, ' ', c.ap_cli) AS nombre_cliente,
               CONCAT(e.nom_emp, ' ', e.ap_emp) AS nombre_mesero
        FROM factura f
        LEFT JOIN pedido p ON f.id_pedido = p.id_pedido
        LEFT JOIN cliente c ON f.cedula_cli = c.cedula_cli
        LEFT JOIN empleado e ON f.cedula_emp = e.cedula_emp
        ORDER BY f.año_fact DESC, f.mes_fact DESC, f.dia_fact DESC, f.hora_fact DESC
    ");
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Para cada factura, obtener los detalles
    foreach ($facturas as &$factura) {
        $stmt_det = $pdo->prepare("
            SELECT df.cod_plat, df.subtotal, pl.nom_plat, 
                   ROUND(df.subtotal / pl.precio_plat) as cantidad,
                   pl.precio_plat
            FROM detalle_fact df
            JOIN platillo pl ON df.cod_plat = pl.cod_plat
            WHERE df.cedula_emp = ? AND df.dia_fact = ? AND df.mes_fact = ? AND df.año_fact = ? AND df.hora_fact = ?
        ");
        $stmt_det->execute([
            $factura['cedula_emp'], $factura['dia_fact'], $factura['mes_fact'], 
            $factura['año_fact'], $factura['hora_fact']
        ]);
        $factura['detalles'] = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'facturas' => $facturas]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>
