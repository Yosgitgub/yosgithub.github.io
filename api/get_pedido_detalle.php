<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

$id_pedido = $_GET['id'] ?? null;
if (!$id_pedido) {
    echo json_encode(['success' => false, 'error' => 'ID de pedido requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.id_pedido, p.num_mesa, p.cedula_cli, p.estado FROM pedido p WHERE p.id_pedido = ?");
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo json_encode(['success' => false, 'error' => 'Pedido no encontrado']);
        exit;
    }

    $stmt_det = $pdo->prepare("
        SELECT dp.cod_plat, dp.cantidad, dp.notas, pl.nom_plat, pl.precio_plat
        FROM detalle_pedido dp
        JOIN platillo pl ON dp.cod_plat = pl.cod_plat
        WHERE dp.id_pedido = ?
    ");
    $stmt_det->execute([$id_pedido]);
    $detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);

    $subtotal = 0;
    $items = [];
    foreach ($detalles as $d) {
        $linea = $d['cantidad'] * $d['precio_plat'];
        $subtotal += $linea;
        $items[] = [
            'nom_plat' => $d['nom_plat'],
            'cantidad' => intval($d['cantidad']),
            'precio_unitario' => number_format($d['precio_plat'], 2, '.', ''),
            'subtotal_linea' => number_format($linea, 2, '.', '')
        ];
    }

    $nombre_cliente = 'Cliente General';
    if ($pedido['cedula_cli']) {
        $stmt_cli = $pdo->prepare("SELECT CONCAT(nom_cli, ' ', ap_cli) as nombre FROM cliente WHERE cedula_cli = ?");
        $stmt_cli->execute([$pedido['cedula_cli']]);
        $nombre_cliente = $stmt_cli->fetchColumn() ?: 'Cliente General';
    }

    $iva = $subtotal * 0.16;
    $total = $subtotal + $iva;

    echo json_encode([
        'success' => true,
        'pedido' => [
            'id_pedido' => $pedido['id_pedido'],
            'num_mesa' => $pedido['num_mesa'],
            'cliente' => $nombre_cliente,
            'detalles' => $items,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'iva' => number_format($iva, 2, '.', ''),
            'total' => number_format($total, 2, '.', '')
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
