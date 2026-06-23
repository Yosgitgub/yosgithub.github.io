<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo'] ?? '') !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

try {
    $stmt = $pdo->query("
        SELECT c.cedula_cli, c.nom_cli, c.ap_cli, c.tlf_cli, c.correo_cli, 
               u.username 
        FROM cliente c
        LEFT JOIN usuario_cliente u ON c.cedula_cli = u.cedula_cli
        ORDER BY c.nom_cli ASC
    ");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'clientes' => $clientes]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>
