<?php
$host = "localhost";
$usuario = "serenita";
$contrasenia = "serenita";
$base_datos = "ofertopolis";
$puerto = 3306;

$conn = new mysqli($host, $usuario, $contrasenia, $base_datos, $puerto);


if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function cerrarConexion($conn) {
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
}
?>
