<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

$desc = $_POST['desc_ingre'] ?? '';
$tipo = $_POST['tipo_ingre'] ?? '';
$stock = $_POST['stock_actual'] ?? 0;

if (empty($desc) || empty($tipo)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

if (floatval($stock) < 0) {
    echo json_encode(['success' => false, 'message' => 'El stock no puede ser negativo']);
    exit;
}

try {
    // Generar un código único simple
    $stmt_count = $pdo->query("SELECT COUNT(*) FROM ingredientes");
    $count = $stmt_count->fetchColumn() + 1;
    $cod_ingre = 'ING-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    
    // Si casualmente ya existe, añadir un sufijo
    $stmt_check = $pdo->prepare("SELECT cod_ingre FROM ingredientes WHERE cod_ingre = ?");
    $stmt_check->execute([$cod_ingre]);
    if ($stmt_check->rowCount() > 0) {
        $cod_ingre = 'ING-' . substr(md5(time()), 0, 4);
    }

    $stmt = $pdo->prepare("INSERT INTO ingredientes (cod_ingre, tipo_ingre, desc_ingre, stock_actual, stock_maximo, alerta_vista) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$cod_ingre, $tipo, $desc, $stock, $stock]);

    echo json_encode(['success' => true, 'message' => 'Ingrediente añadido exitosamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
