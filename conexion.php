<?php
$user = 'root';
$host = 'localhost';
$db = 'siga_milano';
$pass = '31896993';
$charset = 'utf8mb4';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
function conexion(){
    global $dsn, $user, $pass, $options, $charset;
    
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (\PDOException $e) {
      die("Error grave de conexión a la base de datos: " . $e->getMessage());
    }
}
?>