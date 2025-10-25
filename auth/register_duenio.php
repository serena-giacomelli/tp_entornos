<?php
session_start();
include_once("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $nombre = trim($_POST['nombre']);

    $check = $conn->query("SELECT * FROM usuarios WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Ya existe un usuario con ese email.";
    } else {
        $sql = "INSERT INTO usuarios (nombre, email, password, rol, estado_cuenta)
                VALUES ('$nombre', '$email', '$password', 'duenio', 'pendiente')";
        if ($conn->query($sql)) {
            $mensajeOK = "Registro exitoso. Tu cuenta debe ser aprobada por el administrador antes de poder acceder.";
        } else {
            $error = "Error al registrar usuario: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Dueño - Ofertópolis</title>
<link rel="stylesheet" href="../css/estilos.css">
<link rel="stylesheet" href="../css/forms.css">
<link rel="stylesheet" href="../css/utilities.css">
</head>
<body>
<div class="form-container">
  <div class="form-card">
    <div class="form-header">
      <h1 class="form-title">Registro de Dueño</h1>
      <p class="form-subtitle">Registrá tu local y publicá tus promociones</p>
    </div>

    <?php if(isset($mensajeOK)): ?>
      <div class="form-alert form-alert-success"><?= $mensajeOK ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
      <div class="form-alert form-alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label-custom">Nombre completo</label>
        <input type="text" name="nombre" class="form-control-custom" placeholder="Ingresá tu nombre completo" required>
      </div>
      
      <div class="form-group">
        <label class="form-label-custom">Email</label>
        <input type="email" name="email" class="form-control-custom" placeholder="tu@email.com" required>
      </div>
      
      <div class="form-group">
        <label class="form-label-custom">Contraseña</label>
        <input type="password" name="password" class="form-control-custom" placeholder="Mínimo 6 caracteres" required>
      </div>
      
      <div class="form-alert form-alert-warning" style="margin-bottom: var(--spacing-md);">
        Tu cuenta deberá ser aprobada por un administrador antes de poder acceder.
      </div>
      
      <div class="form-button-group">
        <button type="submit" class="btn-primary-custom" style="width: 100%;">Registrarme</button>
        <a href="../index.php" class="btn-outline-custom" style="width: 100%; text-align: center; text-decoration: none; display: inline-block;">← Volver al Inicio</a>
      </div>
    </form>

    <div class="form-footer">
      <p class="form-footer-text">¿Ya tenés una cuenta? <a href="login.php" class="form-footer-link">Iniciá sesión</a></p>
    </div>
  </div>
</div>
</body>
</html>
