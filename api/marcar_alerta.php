<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

$cod_ingre = $_POST['cod_ingre'] ?? '';

if (empty($cod_ingre)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE ingredientes SET alerta_vista = 1 WHERE cod_ingre = ?");
    $stmt->execute([$cod_ingre]);

    echo json_encode(['success' => true, 'message' => 'Alerta marcada como vista']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
