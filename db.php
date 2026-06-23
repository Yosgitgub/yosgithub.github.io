<?php
$user = 'ur6fdgktxs9wgvsu';
$host = 'brtlxysmnhzldqaolnvy-mysql.services.clever-cloud.com';
$db = 'brtlxysmnhzldqaolnvy';
$pass = 'nrOfFuUc8I4r0gEAzVeA';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit;
}
?>
