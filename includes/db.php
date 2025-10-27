<?php
// Evitar múltiples inclusiones
if (defined('DB_CONNECTED')) {
    return;
}
define('DB_CONNECTED', true);

// Incluir configuración de base de datos
require_once(__DIR__ . '/db_config.php');

// Verificar que la clase mysqli exista
if (!class_exists('mysqli')) {
    die("La extensión MySQLi no está habilitada en PHP.");
}

// Crear la conexión usando las constantes de db_config.php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar errores de conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8mb4");

// Función para cerrar conexión (solo si no existe)
if (!function_exists('cerrarConexion')) {
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
}

// Opcional: cerrar la conexión automáticamente al terminar el script
register_shutdown_function('cerrarConexion', $conn);
?>
