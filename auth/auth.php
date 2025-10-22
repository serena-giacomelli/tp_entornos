<?php
session_start();
include_once("config/db.php");

// --- LOGIN ---
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $sql = "SELECT * FROM usuarios WHERE email='$email' AND password='$password' AND estado='activo'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_rol'] = $user['rol'];
        $_SESSION['usuario_categoria'] = $user['categoria'];

        switch ($user['rol']) {
            case 'admin': header("Location: admin.php"); break;
            case 'duenio': header("Location: duenio.php"); break;
            default: header("Location: cliente.php");
        }
        exit;
    } else {
        $error = "Credenciales incorrectas o cuenta inactiva.";
    }
}

// --- REGISTRO ---
if (isset($_POST['register'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $rol = $_POST['rol'];
    $estado = ($rol == 'duenio') ? 'pendiente' : 'activo';

    $sql = "INSERT INTO usuarios (nombre, email, password, rol, categoria, estado)
            VALUES ('$nombre','$email','$password','$rol','Inicial','$estado')";
    if ($conn->query($sql)) {
        $mensaje = ($rol == 'duenio')
            ? "Registro exitoso. Espere aprobación del administrador."
            : "Cuenta creada correctamente. Ya puede iniciar sesión.";
    } else {
        $error = "Error al registrarse: " . $conn->error;
    }
}

// --- LOGOUT ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: auth.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ofertópolis - Autenticación</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2 class="text-center mb-4">Ofertópolis</h2>
  <div class="row justify-content-center">
    <div class="col-md-5">
      <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
      <?php if(isset($mensaje)): ?><div class="alert alert-success"><?= $mensaje ?></div><?php endif; ?>

      <!-- FORM LOGIN -->
      <div class="card mb-3">
        <div class="card-header bg-dark text-white">Iniciar Sesión</div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label>Email:</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Contraseña:</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Entrar</button>
          </form>
        </div>
      </div>

      <!-- FORM REGISTRO -->
      <div class="card">
        <div class="card-header bg-secondary text-white">Registrarse</div>
        <div class="card-body">
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
            <div class="mb-3">
              <label>Tipo de usuario:</label>
              <select name="rol" class="form-select">
                <option value="cliente">Cliente</option>
                <option value="duenio">Dueño de local</option>
              </select>
            </div>
            <button type="submit" name="register" class="btn btn-success w-100">Crear cuenta</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
