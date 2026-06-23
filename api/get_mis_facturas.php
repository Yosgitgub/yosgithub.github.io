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
    echo json_encode(['success' => false, 'message' => 'Usuario no identificado.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT f.cedula_emp, f.dia_fact, f.mes_fact, f.año_fact, f.hora_fact, f.iva, f.precio_fact AS subtotal, f.total, f.metodo_pago, f.id_pedido
        FROM factura f
        WHERE f.cedula_cli = ?
        ORDER BY f.año_fact DESC, f.mes_fact DESC, f.dia_fact DESC, f.hora_fact DESC
    ");
    $stmt->execute([$cedula_cli]);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($facturas as &$factura) {
        $stmt_det = $pdo->prepare("
            SELECT df.cod_plat, df.subtotal, pl.nom_plat, ROUND(df.subtotal / pl.precio_plat) as cantidad
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
