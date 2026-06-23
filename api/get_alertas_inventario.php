<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

require_once '../db.php';

try {
    // Buscar ingredientes con stock <= 10% del máximo y que la alerta no haya sido vista
    $stmt = $pdo->query("
        SELECT cod_ingre, nom_plat AS desc_ingre, stock_actual, stock_maximo 
        FROM ingredientes 
        WHERE stock_actual <= (stock_maximo * 0.10) 
        AND alerta_vista = 0
    ");
    // NOTA: La tabla ingredientes tiene desc_ingre, no nom_plat
    
    // Corrijo la query
    $stmt = $pdo->query("
        SELECT cod_ingre, desc_ingre, stock_actual, stock_maximo 
        FROM ingredientes 
        WHERE stock_actual <= (stock_maximo * 0.10) 
        AND alerta_vista = 0
    ");
    $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'alertas' => $alertas]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
