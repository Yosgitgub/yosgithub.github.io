<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$cedula = $_GET['cedula'] ?? '';

if (empty($cedula)) {
    echo json_encode(['success' => false, 'error' => 'Cédula no proporcionada']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT cedula_cli, nom_cli, ap_cli, tlf_cli FROM cliente WHERE cedula_cli = ?");
    $stmt->execute([$cedula]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        echo json_encode(['success' => true, 'cliente' => $cliente]);
    } else {
        echo json_encode(['success' => true, 'cliente' => null]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
