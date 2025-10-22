<?php
$host = "localhost";
$usuario = "serenita";
$contrasenia = "serenita";
$base_datos = "ofertopolis";
$puerto = 3306;

// Verificar que la clase mysqli exista
if (!class_exists('mysqli')) {
    die("La extensión MySQLi no está habilitada en PHP.");
}

// Crear la conexión
$conn = new mysqli($host, $usuario, $contrasenia, $base_datos, $puerto);

// Verificar errores de conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8mb4");

// Función para cerrar conexión
function cerrarConexion($conn) {
    if ($conn && $conn instanceof mysqli && !$conn->connect_error) {
        // Verificar si la conexión aún está activa antes de cerrar
        try {
            if ($conn->ping()) {
                $conn->close();
            }
        } catch (Exception $e) {
            // La conexión ya está cerrada, no hacer nada
        }
    }
}

// Opcional: cerrar la conexión automáticamente al terminar el script
register_shutdown_function('cerrarConexion', $conn);
?>
