<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$rol = $_POST['role'] ?? '';
$cedula = $_POST['cedula'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son obligatorios']);
    exit;
}

if ($rol === 'cliente' && (empty($cedula) || empty($nombre) || empty($apellido))) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos básicos son obligatorios para el cliente']);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    if ($rol === 'restaurante') {
        $carnet = $_POST['carnet'] ?? '';
        $cargo = $_POST['cargo'] ?? '';

        if (empty($carnet) || empty($cargo)) {
            throw new Exception('El número de carnet y el cargo son obligatorios para el personal del restaurante');
        }

        // Validar que el carnet comience con la letra correcta según el cargo
        $primeraLetra = strtolower(substr($carnet, 0, 1));
        if ($cargo === 'Mesero' && $primeraLetra !== 'm') {
            throw new Exception('El carnet de un Mesero debe comenzar con la letra "M". Ejemplo: M001');
        }
        if ($cargo === 'Chef' && $primeraLetra !== 'c') {
            throw new Exception('El carnet de un Chef debe comenzar con la letra "C". Ejemplo: C001');
        }

        // Verify carnet and cargo exist in empleado table
        $stmt = $pdo->prepare("SELECT nom_emp FROM empleado WHERE carnet_emp = ? AND cargo_emp = ?");
        $stmt->execute([$carnet, $cargo]);
        $emp = $stmt->fetch();
        if (!$emp) {
            // El empleado no existe, lo creamos automáticamente usando los datos proporcionados
            if (empty($cedula) || empty($nombre) || empty($apellido)) {
                throw new Exception('Para registrar un nuevo empleado, la cédula, nombre y apellido son obligatorios.');
            }
            
            $stmt = $pdo->prepare("INSERT INTO empleado (carnet_emp, cedula_emp, nom_emp, ap_emp, cargo_emp, dia_ing, año_ing) VALUES (?, ?, ?, ?, ?, CURDATE(), ?)");
            $stmt->execute([$carnet, $cedula, $nombre, $apellido, $cargo, date('Y')]);
            $nombre_emp = $nombre;
        } else {
            $nombre_emp = $emp['nom_emp'];
        }

        // Check if username exists
        $stmt = $pdo->prepare("SELECT username FROM usuario_sistema WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            throw new Exception('El nombre de usuario ya está en uso.');
        }

        // Insert into usuario_sistema
        $stmt = $pdo->prepare("INSERT INTO usuario_sistema (username, password_hash, carnet_emp) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password_hash, $carnet]);

    } else if ($rol === 'cliente') {
        // Check if cedula exists
        $stmt = $pdo->prepare("SELECT cedula_cli FROM cliente WHERE cedula_cli = ?");
        $stmt->execute([$cedula]);
        if (!$stmt->fetch()) {
            // Insert into cliente
            $stmt = $pdo->prepare("INSERT INTO cliente (cedula_cli, nom_cli, ap_cli) VALUES (?, ?, ?)");
            $stmt->execute([$cedula, $nombre, $apellido]);
        }

        // Check if username exists
        $stmt = $pdo->prepare("SELECT username FROM usuario_cliente WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            throw new Exception('El nombre de usuario ya está en uso.');
        }

        // Insert into usuario_cliente
        $stmt = $pdo->prepare("INSERT INTO usuario_cliente (username, password_hash, cedula_cli) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password_hash, $cedula]);

    } else {
        throw new Exception('Rol no válido');
    }

    $pdo->commit();
    $nombre_mostrar = ($rol === 'restaurante') ? $nombre_emp : $nombre;
    echo json_encode(['success' => true, 'message' => 'Registro exitoso. ¡Bienvenido, ' . $nombre_mostrar . '!']);

} catch (Exception $e) {
    $pdo->rollBack();
    // Handling duplicate entry errors gracefully
    if (strpos($e->getMessage(), '1062 Duplicate entry') !== false) {
        if (strpos($e->getMessage(), 'cedula_emp') !== false) {
             echo json_encode(['success' => false, 'message' => 'Esta cédula ya está registrada como empleado.']);
        } else {
             echo json_encode(['success' => false, 'message' => 'Error de duplicado. Posiblemente el usuario, cédula o carnet ya existen.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
