<?php
include_once("db.php");

if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
} else {
    echo "✅ Conexión exitosa a la base de datos.";
}

cerrarConexion($conn);
?>
