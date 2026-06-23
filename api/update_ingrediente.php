<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

$cod = $_POST['cod_ingre'] ?? '';
$desc = $_POST['desc_ingre'] ?? '';
$tipo = $_POST['tipo_ingre'] ?? '';
$stock = $_POST['stock_actual'] ?? '';

if (empty($cod) || empty($desc) || empty($tipo) || $stock === '') {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

if (floatval($stock) < 0) {
    echo json_encode(['success' => false, 'message' => 'El stock no puede ser negativo']);
    exit;
}

try {
    // Si el nuevo stock supera al stock máximo registrado, se actualiza el máximo.
    // Además, al reabastecer (o modificar stock) reiniciamos la alerta para que vuelva a avisar si cae.
    $stmt = $pdo->prepare("
        UPDATE ingredientes 
        SET desc_ingre = ?, 
            tipo_ingre = ?, 
            stock_actual = ?,
            stock_maximo = GREATEST(COALESCE(stock_maximo, 0), ?),
            alerta_vista = 0
        WHERE cod_ingre = ?
    ");
    $stmt->execute([$desc, $tipo, $stock, $stock, $cod]);

    echo json_encode(['success' => true, 'message' => 'Ingrediente actualizado exitosamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
