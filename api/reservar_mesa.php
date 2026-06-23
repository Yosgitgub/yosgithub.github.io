<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Solo los clientes pueden reservar mesas.']);
    exit;
}

require_once '../db.php';

$num_mesa = $_POST['num_mesa'] ?? '';
$fecha = $_POST['fecha_reserva'] ?? '';
$hora = $_POST['hora_reserva'] ?? '';
$cant_personas = $_POST['cant_personas'] ?? 1;
$duracion_horas = $_POST['duracion_horas'] ?? 2;
$cedula_cli = $_SESSION['cedula_cli'] ?? '';

if (empty($num_mesa) || empty($fecha) || empty($hora) || empty($cedula_cli)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos de la reserva.']);
    exit;
}

// Validar duración máxima de 4 horas
$duracion_horas = min(max(intval($duracion_horas), 1), 4);

// Calcular hora de fin
$hora_inicio = new DateTime($hora);
$hora_fin_dt = clone $hora_inicio;
$hora_fin_dt->modify("+{$duracion_horas} hours");
$hora_fin = $hora_fin_dt->format('H:i:s');

try {
    $pdo->beginTransaction();

    // 1. Verificar que la mesa sigue disponible y obtener su capacidad
    $stmt_check = $pdo->prepare("SELECT estado_mesa, cap_mesa FROM mesa WHERE num_mesa = ? FOR UPDATE");
    $stmt_check->execute([$num_mesa]);
    $mesa = $stmt_check->fetch();

    if (!$mesa || $mesa['estado_mesa'] !== 'Disponible') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Lo sentimos, esta mesa ya no está disponible.']);
        exit;
    }

    if ($cant_personas > $mesa['cap_mesa']) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'La cantidad de personas ('.$cant_personas.') supera la capacidad de la mesa ('.$mesa['cap_mesa'].').']);
        exit;
    }

    // 2. Cambiar estado de la mesa
    $stmt_update = $pdo->prepare("UPDATE mesa SET estado_mesa = 'Reservada' WHERE num_mesa = ?");
    $stmt_update->execute([$num_mesa]);

    // 3. Crear el registro de la reserva con hora_fin
    $stmt_reserva = $pdo->prepare("INSERT INTO reserva (cedula_cli, num_mesa, fecha_reserva, hora_reserva, hora_fin, cant_personas) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_reserva->execute([$cedula_cli, $num_mesa, $fecha, $hora, $hora_fin, $cant_personas]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Mesa reservada exitosamente para el ' . $fecha . ' de ' . $hora . ' a ' . $hora_fin_dt->format('h:i A')]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
