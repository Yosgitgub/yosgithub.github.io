<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['num_mesa']) || empty($data['platillos']) || empty($data['metodo_pago'])) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos (mesa, platillos o método de pago)']);
    exit;
}

$num_mesa = intval($data['num_mesa']);
$platillos = $data['platillos'];
$metodo_pago = $data['metodo_pago'];
$cedula_cli = isset($data['cedula_cli']) ? $data['cedula_cli'] : null;
$cedula_emp = $_SESSION['cedula_emp'] ?? null;

// Obtener RIF del restaurante
$stmt_rif = $pdo->query("SELECT rif_rest FROM restaurante LIMIT 1");
$rif_rest = $stmt_rif->fetchColumn() ?: null;

try {
    $pdo->beginTransaction();

    // Intentar sacar cedula_cli de la reserva activa de esta mesa
    if (!$cedula_cli) {
        $stmt_res = $pdo->prepare("SELECT cedula_cli FROM reserva WHERE num_mesa = ? AND estado_reserva = 'Activa' AND fecha_reserva = CURDATE() ORDER BY hora_reserva DESC LIMIT 1");
        $stmt_res->execute([$num_mesa]);
        if ($res_row = $stmt_res->fetch()) {
            $cedula_cli = $res_row['cedula_cli'];
        }
    }

    // Si aún no hay cedula_emp, obtenerla de algún empleado mesero
    if (!$cedula_emp) {
        $stmt_emp = $pdo->query("SELECT cedula_emp FROM empleado WHERE cargo_emp = 'Mesero' LIMIT 1");
        $cedula_emp = $stmt_emp->fetchColumn() ?: null;
    }

    // 0. Validar disponibilidad de inventario ANTES de crear el pedido
    foreach ($platillos as $item) {
        $cantidad = intval($item['cantidad']);
        $cod_plat = $item['cod_plat'];
        
        $stmt_check = $pdo->prepare("
            SELECT i.desc_ingre, i.stock_actual, dr.cant_ingre, p.nom_plat
            FROM detalle_receta dr
            JOIN ingredientes i ON dr.cod_ingre = i.cod_ingre
            JOIN platillo p ON dr.cod_plat = p.cod_plat
            WHERE dr.cod_plat = ?
        ");
        $stmt_check->execute([$cod_plat]);
        $receta = $stmt_check->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($receta as $ingrediente) {
            $stock_necesario = $ingrediente['cant_ingre'] * $cantidad;
            if ($ingrediente['stock_actual'] < $stock_necesario) {
                // Calcular máximo de platos posibles
                $max_posible = floor($ingrediente['stock_actual'] / $ingrediente['cant_ingre']);
                if ($max_posible < 0) $max_posible = 0;
                throw new Exception("Sin stock suficiente de '{$ingrediente['desc_ingre']}' para {$cantidad}x '{$ingrediente['nom_plat']}'. Solo puedes pedir máximo {$max_posible}.");
            }
        }
    }

    // 1. Crear el Pedido (para cocina)
    $cod_plats = array_map(function($p) { return $p['cod_plat']; }, $platillos);
    $in_placeholders = implode(',', array_fill(0, count($cod_plats), '?'));

    $stmt_time = $pdo->prepare("SELECT MAX(tiempo_preparacion) AS max_time FROM platillo WHERE cod_plat IN ($in_placeholders)");
    $stmt_time->execute($cod_plats);
    $row_time = $stmt_time->fetch(PDO::FETCH_ASSOC);
    $max_prep_time = ($row_time && $row_time['max_time']) ? intval($row_time['max_time']) : 15;
    $total_minutes = 5 + $max_prep_time;

    $stmt = $pdo->prepare("INSERT INTO pedido (num_mesa, cedula_cli, cedula_emp, estado, fecha_hora, hora_entrega) VALUES (?, ?, ?, 'Pendiente', NOW(), DATE_ADD(NOW(), INTERVAL ? MINUTE))");
    $stmt->execute([$num_mesa, $cedula_cli, $cedula_emp, $total_minutes]);
    $id_pedido = $pdo->lastInsertId();

    $stmt_detalle = $pdo->prepare("INSERT INTO detalle_pedido (id_pedido, cod_plat, cantidad, notas) VALUES (?, ?, ?, ?)");
    
    // Preparar query para descontar inventario
    $stmt_ingre = $pdo->prepare("
        UPDATE ingredientes i
        JOIN detalle_receta dr ON i.cod_ingre = dr.cod_ingre
        SET i.stock_actual = i.stock_actual - (dr.cant_ingre * ?)
        WHERE dr.cod_plat = ?
    ");

    foreach ($platillos as $item) {
        $cantidad = intval($item['cantidad']);
        $cod_plat = $item['cod_plat'];

        $stmt_detalle->execute([
            $id_pedido,
            $cod_plat,
            $cantidad,
            $item['notas'] ?? ''
        ]);

        // Descontar inventario por cada plato
        $stmt_ingre->execute([$cantidad, $cod_plat]);
    }

    // 2. Obtener precios para calcular total
    $stmt_precios = $pdo->prepare("SELECT cod_plat, precio_plat, nom_plat FROM platillo WHERE cod_plat IN ($in_placeholders)");
    $stmt_precios->execute($cod_plats);
    $precios = [];
    $nombres = [];
    while ($row = $stmt_precios->fetch(PDO::FETCH_ASSOC)) {
        $precios[$row['cod_plat']] = floatval($row['precio_plat']);
        $nombres[$row['cod_plat']] = $row['nom_plat'];
    }

    $subtotal = 0;
    $detalles_factura = [];
    foreach ($platillos as $item) {
        $precio = $precios[$item['cod_plat']] ?? 0;
        $linea = $precio * intval($item['cantidad']);
        $subtotal += $linea;
        $detalles_factura[] = [
            'nom_plat' => $nombres[$item['cod_plat']] ?? $item['cod_plat'],
            'cantidad' => intval($item['cantidad']),
            'precio_unitario' => number_format($precio, 2, '.', ''),
            'subtotal_linea' => number_format($linea, 2, '.', '')
        ];
    }

    $iva = $subtotal * 0.16;
    $total = $subtotal + $iva;

    $dia = intval(date('j'));
    $mes = intval(date('n'));
    $anio = intval(date('Y'));
    $hora = date('H:i:s');

    // 3. Crear la Factura
    $stmt_factura = $pdo->prepare("
        INSERT INTO factura (cedula_emp, dia_fact, mes_fact, año_fact, hora_fact, cedula_cli, rif_rest, descuento, iva, precio_fact, costo_fact, total, metodo_pago, id_pedido) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 0.00, ?, ?, 0.00, ?, ?, ?)
    ");
    $stmt_factura->execute([
        $cedula_emp, $dia, $mes, $anio, $hora, 
        $cedula_cli, $rif_rest, 
        $iva, $subtotal, $total, 
        $metodo_pago, $id_pedido
    ]);

    // 4. Insertar detalle_fact
    $stmt_det_factura = $pdo->prepare("
        INSERT INTO detalle_fact (cedula_emp, dia_fact, mes_fact, año_fact, hora_fact, num_mesa, cod_plat, subtotal)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($platillos as $item) {
        $precio = $precios[$item['cod_plat']] ?? 0;
        $subtotal_item = $precio * intval($item['cantidad']);
        $stmt_det_factura->execute([
            $cedula_emp, $dia, $mes, $anio, $hora,
            $num_mesa,
            $item['cod_plat'],
            $subtotal_item
        ]);
    }

    // Obtener nombre del cliente si existe
    $nombre_cliente = 'Cliente General';
    if ($cedula_cli) {
        $stmt_cli = $pdo->prepare("SELECT CONCAT(nom_cli, ' ', ap_cli) as nombre FROM cliente WHERE cedula_cli = ?");
        $stmt_cli->execute([$cedula_cli]);
        $nombre_cliente = $stmt_cli->fetchColumn() ?: 'Cliente General';
    }

    // Obtener nombre del mesero
    $nombre_mesero = $_SESSION['nombre'] ?? 'Mesero';

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'id_pedido' => $id_pedido, 
        'minutos_estimados' => $total_minutes,
        'factura' => [
            'fecha' => date('d/m/Y'),
            'hora' => $hora,
            'mesa' => $num_mesa,
            'mesero' => $nombre_mesero,
            'cliente' => $nombre_cliente,
            'cedula_cli' => $cedula_cli ?? 'N/A',
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
