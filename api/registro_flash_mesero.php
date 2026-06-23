<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

// Verificar autenticación (Idealmente validar si es Mesero o Administrador)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$cedula = trim($data['cedula'] ?? '');
$nombre = trim($data['nombre'] ?? '');
$telefono = trim($data['telefono'] ?? '');

if (empty($cedula) || empty($nombre) || empty($telefono)) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insertar o actualizar cliente
    $stmt_cli = $pdo->prepare("
        INSERT INTO cliente (cedula_cli, nom_cli, tlf_cli, ap_cli) 
        VALUES (?, ?, ?, '') 
        ON DUPLICATE KEY UPDATE nom_cli = VALUES(nom_cli), tlf_cli = VALUES(tlf_cli)
    ");
    $stmt_cli->execute([$cedula, $nombre, $telefono]);

    // 2. Verificar si ya tiene cuenta
    $stmt_check = $pdo->prepare("SELECT id_usuario FROM usuario_cliente WHERE cedula_cli = ?");
    $stmt_check->execute([$cedula]);
    if (!$stmt_check->fetch()) {
        // 3. Crear cuenta si no la tiene
        $password_hash = password_hash($cedula, PASSWORD_DEFAULT);
        $stmt_user = $pdo->prepare("
            INSERT INTO usuario_cliente (username, password_hash, cedula_cli, estado_usuario) 
            VALUES (?, ?, ?, 1)
        ");
        $stmt_user->execute([$cedula, $password_hash, $cedula]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    // Si hay error de duplicado (ej. username en uso), controlarlo
    if (strpos($e->getMessage(), '1062 Duplicate entry') !== false) {
        echo json_encode(['success' => false, 'error' => 'El nombre de usuario (cédula) ya está en uso por otra cuenta.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error en base de datos: ' . $e->getMessage()]);
    }
}
?>
