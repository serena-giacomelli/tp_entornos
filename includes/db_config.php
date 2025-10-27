<?php
/**
 * Configuración de base de datos
 * Ofertópolis - Conexión MySQL
 */

if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
    // DESARROLLO LOCAL
    define('DB_HOST', 'localhost');
    define('DB_USER', 'serenita');
    define('DB_PASS', 'serenita');
    define('DB_NAME', 'ofertopolis');
} else {
    // PRODUCCIÓN: InfinityFree
    define('DB_HOST', 'sql100.infinityfree.com');
    define('DB_USER', 'if0_40267504');
    define('DB_PASS', 'TU_CONTRASEÑA_VPANEL_AQUI'); // Reemplazar con tu contraseña de vPanel
    define('DB_NAME', 'if0_40267504_ofertopolis');
}
?>