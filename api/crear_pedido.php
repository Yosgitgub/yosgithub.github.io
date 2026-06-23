<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['platillos'])) {
    echo json_encode(['success' => false, 'error' => 'No se enviaron platillos']);
    exit;
}

$num_mesa = isset($data['num_mesa']) && $data['num_mesa'] !== '' ? intval($data['num_mesa']) : null;
$platillos = $data['platillos'];
$cedula_cli = isset($data['cedula_cli']) && $data['cedula_cli'] !== '' ? $data['cedula_cli'] : null;

if (!$num_mesa && !$cedula_cli) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos (mesa o cliente)']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Intentar sacar cedula_cli si tenemos mesa pero no cedula
    if ($num_mesa && !$cedula_cli) {
        $stmt_res = $pdo->prepare("SELECT cedula_cli FROM reserva WHERE num_mesa = ? AND estado_reserva = 'Activa' AND fecha_reserva = CURDATE() ORDER BY hora_reserva DESC LIMIT 1");
        $stmt_res->execute([$num_mesa]);
        if ($res_row = $stmt_res->fetch()) {
            $cedula_cli = $res_row['cedula_cli'];
        }
    }
    
    // Intentar sacar num_mesa si tenemos cedula pero no mesa
    if (!$num_mesa && $cedula_cli) {
        $stmt_res = $pdo->prepare("SELECT num_mesa FROM reserva WHERE cedula_cli = ? AND estado_reserva = 'Activa' AND fecha_reserva = CURDATE() ORDER BY hora_reserva DESC LIMIT 1");
        $stmt_res->execute([$cedula_cli]);
        if ($res_row = $stmt_res->fetch()) {
            $num_mesa = $res_row['num_mesa'];
        } else {
            throw new Exception("No tienes una mesa activa. Debes ocupar una mesa escaneando un código QR.");
        }
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

    // Obtener un mesero para asignarlo (el primero disponible)
    $stmt_mesero = $pdo->query("SELECT cedula_emp FROM empleado WHERE cargo_emp = 'Mesero' LIMIT 1");
    $mesero = $stmt_mesero->fetch();
    $cedula_emp = $mesero ? $mesero['cedula_emp'] : null;

    // 1. Insertar en la tabla pedido (hora_entrega es NULL por ahora)
    $stmt = $pdo->prepare("INSERT INTO pedido (num_mesa, cedula_cli, cedula_emp, estado, fecha_hora, hora_entrega) VALUES (?, ?, ?, 'En espera', NOW(), NULL)");
    $stmt->execute([$num_mesa, $cedula_cli, $cedula_emp]);
    
    $id_pedido = $pdo->lastInsertId();

    // 3. Insertar los detalles y descontar inventario
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

    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'id_pedido' => $id_pedido, 
        'cedula_cli' => $cedula_cli
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Error al procesar el pedido: ' . $e->getMessage()]);
}
?>
