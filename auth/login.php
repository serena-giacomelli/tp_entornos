<?php
session_start();
include_once("../includes/db.php");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));

    $sql = "SELECT * FROM usuarios WHERE email='$email' AND password='$password' AND estado_cuenta='activo'";
    $res = $conn->query($sql);

    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_rol'] = $user['rol'];
        $_SESSION['usuario_categoria'] = $user['categoria'];

        switch ($user['rol']) {
            case 'admin': header("Location: ../admin/admin.php"); break;
            case 'duenio': header("Location: ../duenio/duenio.php"); break;
            case 'cliente': header("Location: ../cliente/cliente.php"); break;
        }
        exit;
    } else {
        $error = "Credenciales incorrectas o cuenta inactiva.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión - Ofertópolis</title>
  <link rel="stylesheet" href="../css/estilos.css">
  <link rel="stylesheet" href="../css/forms.css">
  <link rel="stylesheet" href="../css/utilities.css">
</head>
<body>
<div class="form-container">
  <div class="form-card">
    <div class="form-header">
      <h1 class="form-title">Iniciar Sesión</h1>
      <p class="form-subtitle">Bienvenido a Ofertópolis</p>
    </div>

    <?php if(isset($error)): ?>
      <div class="form-alert form-alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label-custom">Email</label>
        <input type="email" name="email" class="form-control-custom" placeholder="tu@email.com" required>
      </div>
      
      <div class="form-group">
        <label class="form-label-custom">Contraseña</label>
        <input type="password" name="password" class="form-control-custom" placeholder="Tu contraseña" required>
      </div>
      
      <div class="form-button-group">
        <button type="submit" class="btn-primary-custom" style="width: 100%;">Entrar</button>
        <a href="../index.php" class="btn-outline-custom" style="width: 100%; text-align: center; text-decoration: none; display: inline-block;">← Volver al Inicio</a>
      </div>
    </form>

    <div class="form-divider">
      <span>¿No tenés cuenta?</span>
    </div>

    <div class="form-footer">
      <p class="form-footer-text">
        <a href="register_cliente.php" class="form-footer-link">Registrate como Cliente</a>
      </p>
      <p class="form-footer-text">
        <a href="register_duenio.php" class="form-footer-link">Registrate como Dueño de Local</a>
      </p>
    </div>
  </div>
</div>
</body>
</html>
