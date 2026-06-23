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
        SELECT e.carnet_emp, e.cedula_emp, e.nom_emp, e.ap_emp, e.cargo_emp, e.tlf_emp, e.dia_ing, e.año_ing,
               u.username, u.estado_usuario
        FROM empleado e
        LEFT JOIN usuario_sistema u ON e.carnet_emp = u.carnet_emp
        ORDER BY e.cargo_emp ASC, e.nom_emp ASC
    ");
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'empleados' => $empleados]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>
