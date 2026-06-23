<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

$nom = $_POST['nom_plat'] ?? '';
$desc = $_POST['desc_plat'] ?? '';
$tipo = $_POST['tipo_plat'] ?? '';
$precio = $_POST['precio_plat'] ?? 0;
$ingredientes_json = $_POST['ingredientes'] ?? '[]';

$ingredientes = json_decode($ingredientes_json, true);

if (empty($nom) || empty($tipo) || empty($precio) || empty($ingredientes)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios o ingredientes']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Generar códigos únicos simples para Receta y Platillo
    $cod_receta = 'RC-' . substr(md5(uniqid()), 0, 6);
    $cod_plat = 'PL-' . substr(md5(uniqid()), 0, 6);

    // 2. Insertar Receta
    $stmt_r = $pdo->prepare("INSERT INTO receta (cod_receta, desc_receta) VALUES (?, ?)");
    $stmt_r->execute([$cod_receta, "Receta de $nom"]);

    // 3. Insertar Platillo
    $stmt_p = $pdo->prepare("INSERT INTO platillo (cod_plat, nom_plat, desc_plat, precio_plat, tipo_plat, cod_receta) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_p->execute([$cod_plat, $nom, $desc, $precio, $tipo, $cod_receta]);

    // 4. Insertar Detalle Receta (los ingredientes elegidos)
    $stmt_dr = $pdo->prepare("INSERT INTO detalle_receta (cod_receta, cod_ingre, cod_plat, cant_ingre) VALUES (?, ?, ?, ?)");
    foreach ($ingredientes as $ing) {
        $stmt_dr->execute([$cod_receta, $ing['cod_ingre'], $cod_plat, $ing['cant_ingre']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Platillo y receta creados exitosamente']);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
