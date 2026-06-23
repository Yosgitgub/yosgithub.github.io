<?php
require_once '../db.php';

try {

    // Add stock_maximo to ingredientes
    $pdo->exec("ALTER TABLE ingredientes ADD COLUMN IF NOT EXISTS stock_maximo DECIMAL(10,2) DEFAULT 100.00 AFTER stock_actual");
    
    // Add alerta_vista to ingredientes
    $pdo->exec("ALTER TABLE ingredientes ADD COLUMN IF NOT EXISTS alerta_vista TINYINT(1) DEFAULT 0 AFTER stock_minimo");

    // Update existing records to set stock_maximo to their current stock_actual if stock_maximo is not set
    $pdo->exec("UPDATE ingredientes SET stock_maximo = stock_actual WHERE stock_maximo IS NULL OR stock_maximo = 100.00");

    echo "Migracion completada exitosamente. Se añadieron las columnas stock_maximo y alerta_vista.";
} catch (PDOException $e) {
    // 1060 is "Duplicate column name", which is fine if it already exists and IF NOT EXISTS didn't work.
    if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1060) {
        echo "Las columnas ya existen en la base de datos.\n";
        
        // Ejecutar UPDATE incluso si las columnas ya existían
        try {
            $pdo->exec("UPDATE ingredientes SET stock_maximo = stock_actual WHERE stock_maximo IS NULL OR stock_maximo = 100.00");
            echo "Se actualizaron los registros de stock_maximo.\n";
        } catch (Exception $ex) {}
    } else {
        echo "Error en migracion: " . $e->getMessage();
    }
}
?>
