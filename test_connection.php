<?php
// Archivo de prueba de conexión
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Conexión - Ofertópolis</h1>";
echo "<p>Servidor: " . $_SERVER['SERVER_NAME'] . "</p>";

// Incluir configuración
if (file_exists('includes/db_config.php')) {
    include_once('includes/db_config.php');
    echo "<p>✅ Archivo db_config.php cargado correctamente</p>";
    
    echo "<p>DB_HOST: " . DB_HOST . "</p>";
    echo "<p>DB_USER: " . DB_USER . "</p>";
    echo "<p>DB_NAME: " . DB_NAME . "</p>";
} else {
    echo "<p>❌ ERROR: No se encuentra includes/db_config.php</p>";
    exit;
}

// Probar conexión
echo "<hr><h2>Probando conexión...</h2>";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "<p>❌ ERROR de conexión: " . $conn->connect_error . "</p>";
    } else {
        echo "<p>✅ Conexión exitosa a la base de datos!</p>";
        echo "<p>Versión MySQL: " . $conn->server_info . "</p>";
        
        // Verificar tablas
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            echo "<h3>Tablas en la base de datos:</h3><ul>";
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p>❌ Excepción: " . $e->getMessage() . "</p>";
}

echo "<hr><h2>Archivos en el servidor:</h2>";
echo "<pre>";
if (file_exists('index.php')) echo "✅ index.php\n";
if (file_exists('includes/db.php')) echo "✅ includes/db.php\n";
if (file_exists('includes/header.php')) echo "✅ includes/header.php\n";
if (file_exists('css/estilos.css')) echo "✅ css/estilos.css\n";
echo "</pre>";

phpinfo();
?>
