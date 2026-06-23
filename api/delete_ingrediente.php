<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

$cod = $_POST['cod_ingre'] ?? '';

if (empty($cod)) {
    echo json_encode(['success' => false, 'message' => 'ID del ingrediente faltante']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM ingredientes WHERE cod_ingre = ?");
    $stmt->execute([$cod]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Ingrediente eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'El ingrediente no existe']);
    }
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar porque este ingrediente es parte de uno o más platillos/recetas.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
    }
}
?>
