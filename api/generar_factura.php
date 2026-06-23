<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo'] ?? '') !== 'mesero') {
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_pedido = $data['id_pedido'] ?? null;
$metodo_pago = $data['metodo_pago'] ?? null;

if (!$id_pedido || !$metodo_pago) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos (id_pedido, metodo_pago)']);
    exit;
}

$cedula_emp = $_SESSION['cedula_emp'] ?? null;

try {
    // Si no hay cedula_emp en sesión, buscar un mesero
    if (!$cedula_emp) {
        $stmt_emp = $pdo->query("SELECT cedula_emp FROM empleado WHERE cargo_emp = 'Mesero' LIMIT 1");
        $cedula_emp = $stmt_emp->fetchColumn() ?: null;
    }

    // Obtener datos del pedido
    $stmt = $pdo->prepare("SELECT p.id_pedido, p.num_mesa, p.cedula_cli, p.fecha_hora FROM pedido p WHERE p.id_pedido = ? AND p.estado = 'Entregado'");
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo json_encode(['success' => false, 'error' => 'Pedido no encontrado o no está en estado Entregado']);
        exit;
    }

    // Obtener detalles con precios
    $stmt_det = $pdo->prepare("
        SELECT dp.cod_plat, dp.cantidad, pl.precio_plat, pl.nom_plat
        FROM detalle_pedido dp
        JOIN platillo pl ON dp.cod_plat = pl.cod_plat
        WHERE dp.id_pedido = ?
    ");
    $stmt_det->execute([$id_pedido]);
    $detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales
    $subtotal = 0;
    $detalles_factura = [];
    foreach ($detalles as $item) {
        $linea = $item['cantidad'] * $item['precio_plat'];
        $subtotal += $linea;
        $detalles_factura[] = [
            'nom_plat' => $item['nom_plat'],
            'cantidad' => intval($item['cantidad']),
            'precio_unitario' => number_format($item['precio_plat'], 2, '.', ''),
            'subtotal_linea' => number_format($linea, 2, '.', '')
        ];
    }

    $iva = $subtotal * 0.16;
    $total = $subtotal + $iva;

    $dia = intval(date('j'));
    $mes = intval(date('n'));
    $anio = intval(date('Y'));
    $hora = date('H:i:s');

    // Obtener RIF del restaurante
    $stmt_rif = $pdo->query("SELECT rif_rest FROM restaurante LIMIT 1");
    $rif_rest = $stmt_rif->fetchColumn() ?: null;

    $pdo->beginTransaction();

    // Insertar factura
    $stmt_factura = $pdo->prepare("
        INSERT INTO factura (cedula_emp, dia_fact, mes_fact, año_fact, hora_fact, cedula_cli, rif_rest, descuento, iva, precio_fact, costo_fact, total, metodo_pago, id_pedido) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 0.00, ?, ?, 0.00, ?, ?, ?)
    ");
    $stmt_factura->execute([
        $cedula_emp, $dia, $mes, $anio, $hora,
        $pedido['cedula_cli'], $rif_rest,
        $iva, $subtotal, $total,
        $metodo_pago, $id_pedido
    ]);

    // Insertar detalle_fact
    $stmt_det_factura = $pdo->prepare("
        INSERT INTO detalle_fact (cedula_emp, dia_fact, mes_fact, año_fact, hora_fact, num_mesa, cod_plat, subtotal)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($detalles as $item) {
        $subtotal_item = $item['cantidad'] * $item['precio_plat'];
        $stmt_det_factura->execute([
            $cedula_emp, $dia, $mes, $anio, $hora,
            $pedido['num_mesa'],
            $item['cod_plat'],
            $subtotal_item
        ]);
    }

    // Marcar pedido como Facturado
    $stmt_update = $pdo->prepare("UPDATE pedido SET estado = 'Facturado' WHERE id_pedido = ?");
    $stmt_update->execute([$id_pedido]);

    // Obtener nombre del cliente
    $nombre_cliente = 'Cliente General';
    if ($pedido['cedula_cli']) {
        $stmt_cli = $pdo->prepare("SELECT CONCAT(nom_cli, ' ', ap_cli) as nombre FROM cliente WHERE cedula_cli = ?");
        $stmt_cli->execute([$pedido['cedula_cli']]);
        $nombre_cliente = $stmt_cli->fetchColumn() ?: 'Cliente General';
    }

    $nombre_mesero = $_SESSION['nombre'] ?? 'Mesero';

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'factura' => [
            'fecha' => date('d/m/Y'),
            'hora' => $hora,
            'mesa' => $pedido['num_mesa'],
            'mesero' => $nombre_mesero,
            'cliente' => $nombre_cliente,
            'cedula_cli' => $pedido['cedula_cli'] ?? 'N/A',
            'metodo_pago' => $metodo_pago,
            'detalles' => $detalles_factura,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'iva' => number_format($iva, 2, '.', ''),
            'total' => number_format($total, 2, '.', '')
        ]
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Error al procesar: ' . $e->getMessage()]);
}
?>
