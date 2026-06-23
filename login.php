<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Usuario y contraseña requeridos']);
    exit;
}

try {
    // Primero buscamos en empleados (usuario_sistema)
    $stmt = $pdo->prepare("
        SELECT u.id_usuario, u.password_hash, u.carnet_emp, e.nom_emp, e.cargo_emp, e.cedula_emp, 'restaurante' as rol 
        FROM usuario_sistema u
        JOIN empleado e ON u.carnet_emp = e.carnet_emp
        WHERE u.username = ?
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        // Si no está en empleados, buscamos en clientes (usuario_cliente)
        $stmt = $pdo->prepare("
            SELECT u.id_usuario, u.password_hash, u.cedula_cli, c.nom_cli as nom_emp, 'cliente' as rol
            FROM usuario_cliente u
            JOIN cliente c ON u.cedula_cli = c.cedula_cli
            WHERE u.username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
    }

    if ($user) {
        if (password_verify($password, $user['password_hash'])) {
            // Iniciar sesión
            session_start();
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['nombre'] = $user['nom_emp'];
            $_SESSION['cargo'] = $user['cargo_emp'] ?? 'Cliente';
            if ($user['rol'] === 'cliente') {
                $_SESSION['cedula_cli'] = $user['cedula_cli'];
            }
            if (isset($user['cedula_emp'])) {
                $_SESSION['cedula_emp'] = $user['cedula_emp'];
            }
            
            echo json_encode([
                'success' => true, 
                'message' => '¡Inicio de sesión exitoso!', 
                'data' => [
                    'nombre' => $user['nom_emp'],
                    'rol' => $user['rol'],
                    'cargo' => $user['cargo_emp'] ?? 'Cliente'
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
