<?php
require_once 'db.php';

// Desactivar llaves foráneas para poder vaciar todo
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE detalle_receta;");
$pdo->exec("TRUNCATE TABLE receta;");
$pdo->exec("TRUNCATE TABLE platillo;");
$pdo->exec("TRUNCATE TABLE ingredientes;");
$pdo->exec("TRUNCATE TABLE detalle_pedido;");
$pdo->exec("TRUNCATE TABLE pedido;");
$pdo->exec("TRUNCATE TABLE detalle_fact;");
$pdo->exec("TRUNCATE TABLE factura;");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

$ingredientes_list = [
    // Mariscos y Pescados
    ['Camarón', 'Gramos (g)', 50000, 1000],
    ['Róbalo', 'Kilos (Kg)', 50, 5],
    ['Ostiones', 'Unidades (uds)', 500, 50],
    ['Calamar', 'Gramos (g)', 20000, 1000],
    ['Mariscos mezclados', 'Gramos (g)', 15000, 1000],
    ['Filete de pescado', 'Unidades (uds)', 200, 20],
    ['Pulpo', 'Unidades (uds)', 50, 5],
    ['Mejillones', 'Gramos (g)', 10000, 1000],
    ['Almejas', 'Gramos (g)', 10000, 1000],
    ['Langostinos', 'Gramos (g)', 10000, 1000],
    ['Pescado en trozos', 'Gramos (g)', 15000, 1000],
    ['Jaiba', 'Unidades (uds)', 100, 10],
    ['Cabeza de pescado', 'Unidades (uds)', 50, 5],
    ['Vieiras', 'Gramos (g)', 5000, 500],
    ['Chipi chipi', 'Gramos (g)', 5000, 500],
    ['Cangrejo', 'Gramos (g)', 5000, 500],
    ['Merluza', 'Gramos (g)', 10000, 1000],
    ['Rape', 'Gramos (g)', 5000, 500],
    
    // Verduras y Frutas
    ['Tomate', 'Unidades (uds)', 500, 50],
    ['Cebolla blanca', 'Unidades (uds)', 300, 30],
    ['Cebolla morada', 'Unidades (uds)', 300, 30],
    ['Limón', 'Unidades (uds)', 1000, 100],
    ['Jugo de limón', 'Mililitros (ml)', 10000, 1000],
    ['Aguacate', 'Unidades (uds)', 200, 20],
    ['Chile serrano', 'Unidades (uds)', 200, 20],
    ['Cilantro', 'Gramos (g)', 2000, 200],
    ['Pimentón verde', 'Unidades (uds)', 200, 20],
    ['Pimentón rojo', 'Unidades (uds)', 200, 20],
    ['Ají dulce rojo', 'Unidades (uds)', 300, 30],
    ['Naranja', 'Unidades (uds)', 300, 30],
    ['Jugo de naranja', 'Mililitros (ml)', 5000, 500],
    ['Ajo', 'Unidades (uds)', 1000, 100],
    ['Jalapeño', 'Unidades (uds)', 200, 20],
    ['Pepino', 'Unidades (uds)', 150, 15],
    ['Perejil', 'Gramos (g)', 1000, 100],
    ['Guisantes', 'Gramos (g)', 5000, 500],
    ['Aceitunas verdes', 'Unidades (uds)', 1000, 100],
    ['Aceitunas moradas', 'Unidades (uds)', 1000, 100],
    ['Arvejas', 'Gramos (g)', 5000, 500],
    ['Chile guajillo seco', 'Unidades (uds)', 500, 50],
    ['Papa', 'Unidades (uds)', 500, 50],
    ['Zanahoria', 'Unidades (uds)', 500, 50],
    ['Ajoporro', 'Unidades (uds)', 100, 10],
    ['Maíz', 'Gramos (g)', 5000, 500],
    ['Apio', 'Unidades (uds)', 100, 10],
    ['Lima', 'Unidades (uds)', 200, 20],
    ['Hierbabuena', 'Unidades (uds)', 1000, 100],
    ['Ají', 'Unidades (uds)', 200, 20],
    
    // Despensa y Lácteos
    ['Ketchup', 'Mililitros (ml)', 10000, 1000],
    ['Sal', 'Gramos (g)', 10000, 1000],
    ['Pimienta', 'Gramos (g)', 5000, 500],
    ['Mantequilla', 'Gramos (g)', 10000, 1000],
    ['Queso parmesano', 'Gramos (g)', 5000, 500],
    ['Huevo', 'Unidades (uds)', 500, 50],
    ['Mostaza', 'Gramos (g)', 5000, 500],
    ['Pasta de ajo', 'Gramos (g)', 2000, 200],
    ['Salsa inglesa', 'Mililitros (ml)', 5000, 500],
    ['Aceite vegetal', 'Mililitros (ml)', 20000, 2000],
    ['Aceite de oliva', 'Mililitros (ml)', 15000, 1500],
    ['Harina de trigo', 'Gramos (g)', 10000, 1000],
    ['Maicena', 'Gramos (g)', 5000, 500],
    ['Arroz amarillo', 'Gramos (g)', 10000, 1000],
    ['Arroz', 'Gramos (g)', 20000, 2000],
    ['Orégano', 'Gramos (g)', 1000, 100],
    ['Vinagre de vino rojo', 'Mililitros (ml)', 5000, 500],
    ['Caldo de pescado', 'Mililitros (ml)', 50000, 5000],
    ['Azafrán', 'Gramos (g)', 500, 50],
    ['Jerez', 'Mililitros (ml)', 3000, 300],
    ['Vino blanco', 'Mililitros (ml)', 10000, 1000],
    ['Hoja de laurel', 'Unidades (uds)', 1000, 100],
    ['Agua', 'Mililitros (ml)', 100000, 10000],
    ['Tomillo', 'Gramos (g)', 1000, 100],
    ['Romero', 'Gramos (g)', 1000, 100],
    ['Aliño', 'Gramos (g)', 2000, 200],
    ['Leche evaporada', 'Mililitros (ml)', 10000, 1000],
    ['Queso', 'Gramos (g)', 5000, 500],
    ['Almendras', 'Gramos (g)', 3000, 300],
    ['Azúcar', 'Gramos (g)', 10000, 1000],
    ['Salsa Tabasco', 'Mililitros (ml)', 2000, 200],
    
    // Licores y Bebidas
    ['Ginebra', 'Mililitros (ml)', 10000, 1000],
    ['Agua tónica', 'Mililitros (ml)', 20000, 2000],
    ['Baya de enebro', 'Unidades (uds)', 500, 50],
    ['Ron', 'Mililitros (ml)', 10000, 1000],
    ['Soda', 'Mililitros (ml)', 20000, 2000],
    ['Jugo de tomate', 'Mililitros (ml)', 10000, 1000],
    ['Vodka', 'Mililitros (ml)', 10000, 1000],
    ['Hielo', 'Unidades (uds)', 10000, 1000]
];

$ing_map = [];
foreach ($ingredientes_list as $ing) {
    $stmt = $pdo->prepare("INSERT INTO ingredientes (cod_ingre, desc_ingre, tipo_ingre, stock_actual, stock_minimo) VALUES (?, ?, ?, ?, ?)");
    $cod = 'ING' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    // ensure unique
    while(isset($ing_map[$ing[0]])) { $cod = 'ING' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT); }
    $stmt->execute([$cod, $ing[0], $ing[1], $ing[2], $ing[3]]);
    $ing_map[$ing[0]] = $cod;
}

$platillos_list = [
    [
        'nom' => 'Cóctel de Camarones', 'tipo' => 'Entrada', 'precio' => 12.00,
        'receta' => [
            ['Camarón', 750], ['Tomate', 5], ['Cebolla blanca', 1], ['Jugo de limón', 200],
            ['Ketchup', 250], ['Aguacate', 2], ['Chile serrano', 1], ['Cilantro', 10], ['Sal', 5]
        ]
    ],
    [
        'nom' => 'Ceviche', 'tipo' => 'Entrada', 'precio' => 15.00,
        'receta' => [
            ['Róbalo', 2], ['Pimentón verde', 3], ['Cebolla morada', 2], ['Ají dulce rojo', 8],
            ['Cilantro', 10], ['Jugo de limón', 1000], ['Jugo de naranja', 200], ['Sal', 5], ['Pimienta', 5]
        ]
    ],
    [
        'nom' => 'Ostiones Frescos', 'tipo' => 'Entrada', 'precio' => 18.00,
        'receta' => [
            ['Ostiones', 16], ['Mantequilla', 100], ['Ajo', 4], ['Vino blanco', 50],
            ['Queso parmesano', 150], ['Pimienta', 5], ['Perejil', 10]
        ]
    ],
    [
        'nom' => 'Aguachile', 'tipo' => 'Entrada', 'precio' => 14.00,
        'receta' => [
            ['Camarón', 500], ['Jugo de limón', 250], ['Jalapeño', 2], ['Cilantro', 10],
            ['Pepino', 1], ['Cebolla morada', 0.5], ['Sal', 5], ['Pimienta', 5], ['Aguacate', 1]
        ]
    ],
    [
        'nom' => 'Chicharrón de Calamar', 'tipo' => 'Entrada', 'precio' => 13.00,
        'receta' => [
            ['Calamar', 500], ['Huevo', 1], ['Mostaza', 15], ['Pasta de ajo', 5],
            ['Jugo de limón', 30], ['Salsa inglesa', 15], ['Sal', 5], ['Pimienta', 5],
            ['Aceite vegetal', 100], ['Harina de trigo', 150], ['Maicena', 60]
        ]
    ],
    [
        'nom' => 'Arroz con Mariscos', 'tipo' => 'Plato Fuerte', 'precio' => 20.00,
        'receta' => [
            ['Aceite de oliva', 15], ['Cebolla blanca', 0.5], ['Pasta de ajo', 15], ['Ajo', 4],
            ['Arroz amarillo', 200], ['Mariscos mezclados', 450], ['Guisantes', 150], ['Pimentón rojo', 1]
        ]
    ],
    [
        'nom' => 'Filetes de Pescado al Ajillo', 'tipo' => 'Plato Fuerte', 'precio' => 18.00,
        'receta' => [
            ['Filete de pescado', 4], ['Ajo', 6], ['Mantequilla', 30], ['Aceite de oliva', 30],
            ['Perejil', 10], ['Sal', 5], ['Pimienta', 5]
        ]
    ],
    [
        'nom' => 'Pulpo a la Parrilla', 'tipo' => 'Plato Fuerte', 'precio' => 24.00,
        'receta' => [
            ['Pulpo', 1], ['Aceite de oliva', 45], ['Ajo', 2], ['Sal', 5], ['Pimienta', 5],
            ['Ají', 1], ['Aceitunas verdes', 10], ['Aceitunas moradas', 10], ['Orégano', 5],
            ['Vinagre de vino rojo', 15], ['Cebolla morada', 0.5]
        ]
    ],
    [
        'nom' => 'Paella de mariscos', 'tipo' => 'Plato Fuerte', 'precio' => 28.00,
        'receta' => [
            ['Arroz', 400], ['Caldo de pescado', 1200], ['Camarón', 300], ['Calamar', 300],
            ['Mejillones', 250], ['Almejas', 150], ['Pimentón rojo', 1], ['Tomate', 2],
            ['Ajo', 3], ['Azafrán', 2], ['Aceite de oliva', 30], ['Sal', 5]
        ]
    ],
    [
        'nom' => 'Camarones al ajillo', 'tipo' => 'Plato Fuerte', 'precio' => 16.00,
        'receta' => [
            ['Camarón', 300], ['Ajo', 5], ['Aceite de oliva', 60], ['Mantequilla', 25],
            ['Jerez', 25], ['Perejil', 5], ['Sal', 5]
        ]
    ],
    [
        'nom' => 'Cazuela de Mariscos', 'tipo' => 'Sopa', 'precio' => 22.00,
        'receta' => [
            ['Calamar', 200], ['Camarón', 200], ['Almejas', 300], ['Mejillones', 300],
            ['Langostinos', 200], ['Cebolla blanca', 2], ['Pimentón rojo', 1], ['Ajo', 2],
            ['Hoja de laurel', 2], ['Vino blanco', 125], ['Tomate', 300], ['Arvejas', 200],
            ['Limón', 2], ['Agua', 500], ['Sal', 5], ['Pimienta', 5], ['Perejil', 15],
            ['Tomillo', 5], ['Azafrán', 2], ['Romero', 5], ['Aceite de oliva', 30]
        ]
    ],
    [
        'nom' => 'Caldo Siete Mares', 'tipo' => 'Sopa', 'precio' => 25.00,
        'receta' => [
            ['Caldo de pescado', 3500], ['Tomate', 3], ['Chile guajillo seco', 3], ['Cebolla blanca', 0.5],
            ['Ajo', 2], ['Zanahoria', 2], ['Papa', 2], ['Cilantro', 15], ['Aceite vegetal', 30],
            ['Sal', 5], ['Pimienta', 5], ['Pescado en trozos', 400], ['Camarón', 400],
            ['Calamar', 200], ['Pulpo', 1], ['Jaiba', 2], ['Mejillones', 12], ['Almejas', 10]
        ]
    ],
    [
        'nom' => 'Fosforera', 'tipo' => 'Sopa', 'precio' => 26.00,
        'receta' => [
            ['Cabeza de pescado', 1], ['Agua', 3000], ['Cebolla blanca', 0.5], ['Ajo', 4],
            ['Pimentón rojo', 0.5], ['Ají dulce rojo', 2], ['Ajoporro', 0.5], ['Tomate', 4],
            ['Aliño', 5], ['Sal', 5], ['Pimienta', 5], ['Cilantro', 20], ['Camarón', 1000],
            ['Calamar', 1000], ['Vieiras', 500], ['Chipi chipi', 500], ['Cangrejo', 500]
        ]
    ],
    [
        'nom' => 'Chupe de Camarones', 'tipo' => 'Sopa', 'precio' => 18.00,
        'receta' => [
            ['Camarón', 500], ['Caldo de pescado', 500], ['Cebolla blanca', 1], ['Ajo', 3],
            ['Zanahoria', 2], ['Papa', 2], ['Maíz', 150], ['Arvejas', 150], ['Leche evaporada', 250],
            ['Queso', 150], ['Huevo', 2], ['Arroz', 60], ['Aceite de oliva', 30],
            ['Sal', 5], ['Pimienta', 5], ['Perejil', 10]
        ]
    ],
    [
        'nom' => 'Zarzuela de Mariscos', 'tipo' => 'Sopa', 'precio' => 30.00,
        'receta' => [
            ['Merluza', 450], ['Rape', 450], ['Calamar', 500], ['Almejas', 500],
            ['Mejillones', 500], ['Camarón', 500], ['Caldo de pescado', 2000], ['Cebolla blanca', 1],
            ['Pimentón verde', 1], ['Pimentón rojo', 1], ['Tomate', 1], ['Sal', 5],
            ['Azafrán', 2], ['Harina de trigo', 50], ['Almendras', 12], ['Hoja de laurel', 1], ['Perejil', 10]
        ]
    ],
    [
        'nom' => 'Gin Tonic', 'tipo' => 'Bebida', 'precio' => 8.00,
        'receta' => [
            ['Ginebra', 50], ['Agua tónica', 200], ['Lima', 1], ['Hielo', 5], ['Baya de enebro', 2]
        ]
    ],
    [
        'nom' => 'Mojito', 'tipo' => 'Bebida', 'precio' => 7.00,
        'receta' => [
            ['Azúcar', 10], ['Hierbabuena', 8], ['Jugo de limón', 30], ['Ron', 60],
            ['Lima', 0.5], ['Soda', 120], ['Hielo', 5]
        ]
    ],
    [
        'nom' => 'Bloody Mary', 'tipo' => 'Bebida', 'precio' => 9.00,
        'receta' => [
            ['Jugo de tomate', 250], ['Salsa inglesa', 5], ['Salsa Tabasco', 5], ['Jugo de limón', 15],
            ['Vodka', 80], ['Pimienta', 2], ['Sal', 2], ['Hielo', 4], ['Apio', 1]
        ]
    ],
    [
        'nom' => 'Limonada', 'tipo' => 'Bebida', 'precio' => 4.00,
        'receta' => [
            ['Limón', 2], ['Agua', 2000], ['Azúcar', 150], ['Hielo', 10]
        ]
    ],
    [
        'nom' => 'Naranjada', 'tipo' => 'Bebida', 'precio' => 5.00,
        'receta' => [
            ['Naranja', 5], ['Agua', 1000], ['Hielo', 10], ['Azúcar', 100]
        ]
    ]
];

foreach ($platillos_list as $plat) {
    $cod_plat = 'PLT' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $cod_receta = 'REC' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt_receta = $pdo->prepare("INSERT INTO receta (cod_receta, desc_receta) VALUES (?, ?)");
    $stmt_receta->execute([$cod_receta, 'Receta de ' . $plat['nom']]);

    $stmt = $pdo->prepare("INSERT INTO platillo (cod_plat, nom_plat, desc_plat, precio_plat, tipo_plat, cod_receta) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$cod_plat, $plat['nom'], $plat['nom'], $plat['precio'], $plat['tipo'], $cod_receta]);
    
    foreach ($plat['receta'] as $item) {
        $nom_ingre = $item[0];
        $cant = $item[1];
        if(isset($ing_map[$nom_ingre])) {
            $cod_ingre = $ing_map[$nom_ingre];
            $stmt = $pdo->prepare("INSERT INTO detalle_receta (cod_receta, cod_ingre, cod_plat, cant_ingre) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cod_receta, $cod_ingre, $cod_plat, $cant]);
        }
    }
}

echo "OK!";
?>
