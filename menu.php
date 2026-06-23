<?php
session_start();
require_once 'conexion.php';
$pdo = conexion();

if(isset($_GET["mesa"])){
    $_SESSION['mesa'] = intval($_GET['mesa']);
}
if(!isset($_SESSION['mesa'])){
    die("Por favor, escanee el código QR de su mesa para poder ordenar.");
}
$numero_mesa=$_SESSION['mesa'];

$menu_db = [];

try {
    $sql = "
        SELECT p.cod_plat, p.nom_plat, p.desc_plat, p.precio_plat, p.tipo_plat, p.img_platillo,
               (SELECT COUNT(*) 
                FROM detalle_receta dr 
                JOIN ingredientes i ON dr.cod_ingre = i.cod_ingre 
                WHERE dr.cod_receta = p.cod_receta AND i.stock_actual < dr.cant_ingre) AS insumos_agotados
        FROM platillo p 
        ORDER BY p.tipo_plat, p.cod_plat
    ";
    
    $stmt = $pdo->query($sql);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tipo = $row['tipo_plat'];
        if (!isset($menu_db[$tipo])) {
            $menu_db[$tipo] = [];
        }
        $menu_db[$tipo][] = $row;
    }
} catch (PDOException $e) {
    die("Error en la consulta de la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Digital - Restaurante Milano</title>
    <link rel="stylesheet" href="menu.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="page-wrapper">
<aside class="menu-images">
    <div class="image-box">
        <img src="img/categorias/entrada.jpg" alt="Entradas Milano" onerror="this.src='parrillada-de-mariscos.jpg';">
    </div>
    
    <div class="image-box">
        <img src="img/categorias/plato_fuerte.jpg" alt="Platos Fuertes Milano" onerror="this.src='mariscos-01.jpg';">
    </div>

    <div class="image-box">
        <img src="img/categorias/sopa.jpg" alt="Sopas y Caldos Milano" onerror="this.src='platillo-gourmet.jpg';">
    </div>

    <div class="image-box">
        <img src="img/categorias/bebida.jpg" alt="Coctelería y Bebidas Milano" onerror="this.src='img/platillos/default_plato.jpg';">
    </div>
</aside>

        <div class="menu-container">
            
            <header class="menu-header">
                <p class="subtitle">DESDE 1987</p>
                <h1 class="main-title">Restaurante Milano</h1>
                <p class="tagline">Marisquería</p>
                <p class="main-title">Mesa N°<?php echo $numero_mesa; ?></p>
            </header>

           <main class="menu-grid">
    <?php foreach ($menu_db as $categoria => $platos): ?>
        <section class="menu-section">
            <h2><?php echo htmlspecialchars(strtoupper($categoria)) . "S"; ?></h2>
            
            <?php foreach ($platos as $plato): ?>
                <div class="menu-item" title="<?php echo htmlspecialchars($plato['desc_plat']); ?>">
                    <span class="item-name"><?php echo htmlspecialchars($plato['nom_plat']); ?></span>
                    <span class="item-price">$<?php echo number_format($plato['precio_plat'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
    </main> <footer class="menu-footer">
            <p>www.restaurantemilano.com</p>
        </footer>

    </div> </div> </body>
</html>
