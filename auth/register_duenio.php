<?php
session_start();
include_once("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $nombre = trim($_POST['nombre']);

    $check = $conn->query("SELECT * FROM usuarios WHERE nombreUsuario='$email'");
    if ($check->num_rows > 0) {
        $error = "Ya existe un usuario con ese email.";
    } else {
        $sql = "INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, estadoCuenta)
                VALUES ('$email', '$password', 'dueno', 'pendiente')";
        if ($conn->query($sql)) {
            $mensajeOK = "Registro exitoso. Tu cuenta debe ser aprobada por el administrador antes de poder acceder.";
        } else {
            $error = "Error al registrar usuario: " . $conn->error;
        }
    }
}
cerrarConexion($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Dueño</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 col-md-6">
  <h3 class="mb-3 text-center">Registro de Dueño de Local</h3>
  <?php if(isset($mensajeOK)): ?><div class="alert alert-success"><?= $mensajeOK ?></div><?php endif; ?>
  <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label>Nombre completo</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Contraseña</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Registrarme</button>
  </form>
  <p class="mt-3 text-center"><a href="login.php">Ya tengo una cuenta</a></p>
</div>
</body>
</html>
