<?php
include_once("../includes/db.php");if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));

    $sql = "INSERT INTO usuarios (nombre, email, password, rol, categoria, estado)
            VALUES ('$nombre', '$email', '$password', 'dueno', 'Premium', 'pendiente')";
    if ($conn->query($sql)) {
        $msg = "Registro enviado. Espere aprobación del administrador.";
    } else {
        $error = "Error al registrarse: " . $conn->error;
    }
}
cerrarConexion($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro Dueño de Local</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card">
        <div class="card-header bg-warning text-white">Registro de Dueño</div>
        <div class="card-body">
          <?php if(isset($msg)): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
          <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label>Nombre:</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Email:</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Contraseña:</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-warning w-100">Enviar solicitud</button>
          </form>
          <hr>
          <a href="login.php" class="btn btn-outline-dark w-100">Volver al login</a>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
