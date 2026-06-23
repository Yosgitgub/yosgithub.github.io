<?php
session_start();
require_once 'db.php';

// Idealmente, esto debería estar protegido para que solo lo vea el Administrador,
// pero por propósitos de demostración lo dejaremos accesible o verificaremos rol.

try {
    $stmt = $pdo->query("SELECT num_mesa, Pos_mesa FROM mesa ORDER BY num_mesa");
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error al consultar mesas: " . $e->getMessage());
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$base_url = $protocol . "://" . $domain . "/proyecto%20milano/menu.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Códigos QR - Milano</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .qr-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .qr-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .qr-card img {
            max-width: 100%;
            height: auto;
        }
        .qr-card h3 {
            color: var(--ocean);
            margin: 10px 0;
            font-family: 'PlayfairDisplay', serif;
        }
        .btn-print {
            background: var(--ocean);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 20px;
            font-size: 1.1rem;
        }
        @media print {
            .btn-print { display: none; }
            .top-bar { display: none; }
            .qr-card { break-inside: avoid; box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <span class="top-bar-lobster">🦞</span>
        <div class="top-bar-text">
            <span>Administración</span>
            <strong>Códigos QR de Mesas</strong>
        </div>
    </div>

    <div style="text-align: center;">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir Códigos QR</button>
    </div>

    <div class="qr-container">
        <?php foreach ($mesas as $mesa): 
            $url = $base_url . "?mesa=" . $mesa['num_mesa'];
            // API de QRServer para generar QR (ya que Google Charts puede estar bloqueado o deprecado)
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($url);
        ?>
            <div class="qr-card">
                <h3>Mesa <?php echo htmlspecialchars($mesa['num_mesa']); ?></h3>
                <p><?php echo htmlspecialchars($mesa['Pos_mesa'] ?? 'Restaurante'); ?></p>
                <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="QR Mesa <?php echo $mesa['num_mesa']; ?>">
                <p style="font-size: 0.8rem; color: #666; word-break: break-all;"><?php echo htmlspecialchars($url); ?></p>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($mesas)): ?>
            <p style="text-align:center; width:100%; grid-column: 1 / -1;">No hay mesas registradas en la base de datos.</p>
        <?php endif; ?>
    </div>
</body>
</html>
