<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

$num_mesa = $_POST['num_mesa'] ?? '';
$cap_mesa = $_POST['cap_mesa'] ?? '';
$zona_mesa = $_POST['zona_mesa'] ?? '';

if (empty($num_mesa) || empty($cap_mesa) || empty($zona_mesa)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios para crear la mesa']);
    exit;
}

try {
    // Validar si la mesa ya existe
    $stmt_check = $pdo->prepare("SELECT num_mesa FROM mesa WHERE num_mesa = ?");
    $stmt_check->execute([$num_mesa]);
    if ($stmt_check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'El número de mesa ya existe en el sistema']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO mesa (num_mesa, cap_mesa, zona_mesa, Pos_mesa, clase_mesa, estado_mesa) VALUES (?, ?, ?, ?, ?, 'Disponible')");
    $stmt->execute([$num_mesa, $cap_mesa, $zona_mesa, $zona_mesa, 'General']);

    echo json_encode(['success' => true, 'message' => 'Mesa añadida exitosamente']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
