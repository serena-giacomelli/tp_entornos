<?php
include_once("includes/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nombre = trim($_POST['nombre']);
  $email = trim($_POST['email']);
  $mensaje = trim($_POST['mensaje']);

  if ($nombre && $email && $mensaje) {
    $sql = "INSERT INTO contactos (nombre, email, mensaje) VALUES ('$nombre', '$email', '$mensaje')";
    if ($conn->query($sql)) {
      $ok = "Mensaje enviado correctamente. El administrador responderá a la brevedad.";
    } else {
      $error = "Error al guardar mensaje: " . $conn->error;
    }
  } else {
    $error = "Todos los campos son obligatorios.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Contacto - Shopping Promociones</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 col-md-6">
  <h3 class="text-center mb-3">Formulario de Contacto</h3>

  <?php if(isset($ok)): ?><div class="alert alert-success"><?= $ok ?></div><?php endif; ?>
  <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label>Nombre</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Mensaje</label>
      <textarea name="mensaje" class="form-control" rows="5" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary w-100">Enviar</button>
  </form>
</div>
</body>
</html>
