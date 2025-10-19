<?php
require_once('../includes/db.php');
require_once('../includes/functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($password !== $confirm) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $mensaje = "El email ya está registrado.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $rol_id = 2; // dueño
            $categoria_id = 1;

            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol_id, categoria_id, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')");
            $stmt->bind_param("sssii", $nombre, $email, $hash, $rol_id, $categoria_id);
            $stmt->execute();

            $mensaje = $stmt->affected_rows > 0 
                ? "Registro exitoso. Esperá la aprobación del administrador."
                : "Error al registrarse.";

            $stmt->close();
        }
        $check->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Dueño - Ofertópolis</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow mx-auto" style="max-width: 450px;">
    <div class="card-body">
      <h4 class="text-center mb-3">Registro de Dueño de Local</h4>

      <?php if (isset($mensaje)): ?>
        <div class="alert alert-info text-center"><?= $mensaje ?></div>
      <?php endif; ?>

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
          <label>Contraseña</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Confirmar Contraseña</label>
          <input type="password" name="confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Registrarse</button>
        <div class="text-center mt-3">
          <a href="login.php">Volver al Login</a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
