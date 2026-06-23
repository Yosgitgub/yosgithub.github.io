<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

$cod_plat = $_POST['cod_plat'] ?? '';

if (empty($cod_plat)) {
    echo json_encode(['success' => false, 'message' => 'Código de platillo no proporcionado']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Obtener la receta asociada antes de borrar
    $stmt = $pdo->prepare("SELECT cod_receta FROM platillo WHERE cod_plat = ?");
    $stmt->execute([$cod_plat]);
    $cod_receta = $stmt->fetchColumn();

    // 1. Borrar de detalle_receta
    $stmt = $pdo->prepare("DELETE FROM detalle_receta WHERE cod_plat = ?");
    $stmt->execute([$cod_plat]);

    // 2. Borrar platillo
    $stmt = $pdo->prepare("DELETE FROM platillo WHERE cod_plat = ?");
    $stmt->execute([$cod_plat]);

    // 3. Borrar la receta (si existía)
    if ($cod_receta) {
        $stmt = $pdo->prepare("DELETE FROM receta WHERE cod_receta = ?");
        $stmt->execute([$cod_receta]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Platillo eliminado exitosamente']);
} catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar el platillo porque ya tiene pedidos o facturas asociadas.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
    }
}
?>
