<?php
session_start();
include_once("../includes/db.php");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));

    $sql = "SELECT * FROM usuarios WHERE email='$email' AND password='$password' AND estado='activo'";
    $res = $conn->query($sql);

    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_rol'] = $user['rol'];
        $_SESSION['usuario_categoria'] = $user['categoria'];

        switch ($user['rol']) {
            case 'admin': header("Location: ../admin/admin.php"); break;
            case 'dueno': header("Location: ../dueno/dueno.php"); break;
            case 'cliente': header("Location: ../cliente/cliente.php"); break;
        }
        exit;
    } else {
        $error = "Credenciales incorrectas o cuenta inactiva.";
    }
}
cerrarConexion($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - Ofertópolis</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card">
        <div class="card-header bg-dark text-white">Iniciar Sesión</div>
        <div class="card-body">
          <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label>Email:</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Contraseña:</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Entrar</button>
          </form>
          <hr>
          <p class="text-center mb-1"><a href="register_cliente.php">Registrarse como Cliente</a></p>
          <p class="text-center"><a href="register_duenio.php">Registrarse como Dueño</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
