<?php
session_start();
echo "<h2>Información de Sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['usuario_rol'])) {
    echo "<h3>Rol actual: " . $_SESSION['usuario_rol'] . "</h3>";
    if ($_SESSION['usuario_rol'] == 'admin') {
        echo "<p style='color: green;'>Tienes permisos de administrador</p>";
        echo "<p><a href='admin/validar_duenios.php'>Ir a Validar Dueños</a></p>";
    } else {
        echo "<p style='color: orange;'>No eres administrador. Eres: " . $_SESSION['usuario_rol'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>No has iniciado sesión</p>";
    echo "<p><a href='auth/login.php'>Iniciar sesión</a></p>";
}
?>
