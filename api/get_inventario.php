<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

try {
    $stmt = $pdo->query("SELECT cod_ingre, tipo_ingre, desc_ingre, stock_actual, stock_minimo FROM ingredientes ORDER BY desc_ingre ASC");
    $ingredientes = $stmt->fetchAll();
    echo json_encode(['success' => true, 'ingredientes' => $ingredientes]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
