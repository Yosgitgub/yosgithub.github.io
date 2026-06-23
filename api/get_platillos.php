<?php
session_start();
header('Content-Type: application/json');

require_once '../db.php';

try {
    // Calcular max_disponible para cada platillo basado en sus ingredientes
    // Si no tiene receta, asumimos que siempre hay disponible (999)
    $stmt = $pdo->query("
        SELECT 
            p.cod_plat, 
            p.nom_plat, 
            p.desc_plat, 
            p.precio_plat, 
            p.tipo_plat,
            IFNULL(
                (SELECT MIN(FLOOR(i.stock_actual / dr.cant_ingre))
                 FROM detalle_receta dr
                 JOIN ingredientes i ON dr.cod_ingre = i.cod_ingre
                 WHERE dr.cod_plat = p.cod_plat),
                999
            ) as max_disponible
        FROM platillo p
        ORDER BY p.tipo_plat, p.nom_plat ASC
    ");
    $platillos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener la receta para cada platillo
    $stmt_receta = $pdo->prepare("
        SELECT i.desc_ingre, i.tipo_ingre, dr.cant_ingre 
        FROM detalle_receta dr
        JOIN ingredientes i ON dr.cod_ingre = i.cod_ingre
        WHERE dr.cod_plat = ?
    ");

    foreach ($platillos as &$plat) {
        $stmt_receta->execute([$plat['cod_plat']]);
        $plat['receta'] = $stmt_receta->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'platillos' => $platillos]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>
