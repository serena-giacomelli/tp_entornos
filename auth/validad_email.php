<?php
include_once("../includes/db.php");

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $res = $conn->query("SELECT * FROM usuarios WHERE tokenValidacion='$token' AND tipoUsuario='cliente'");
    if ($res->num_rows > 0) {
        $conn->query("UPDATE usuarios SET estadoCuenta='activo', tokenValidacion=NULL WHERE tokenValidacion='$token'");
        $mensaje = "✅ Tu cuenta fue activada correctamente. Ya podés iniciar sesión.";
    } else {
        $mensaje = "❌ Token inválido o cuenta ya validada.";
    }
}
cerrarConexion($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Validación de Email</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light text-center mt-5">
  <div class="container">
    <div class="alert alert-info"><?= $mensaje ?></div>
    <a href="login.php" class="btn btn-primary mt-3">Ir al login</a>
  </div>
</body>
</html>
