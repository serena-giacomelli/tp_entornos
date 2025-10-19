<?php
require_once('../includes/db.php');
require_once('../includes/functions.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if ($user['estado'] !== 'activo') {
                $error = 'Tu cuenta aún no está activa o fue deshabilitada.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre'] = $user['nombre'];

                // Obtener rol por ID
                $rolQuery = $conn->prepare("SELECT nombre FROM roles WHERE id = ?");
                $rolQuery->bind_param("i", $user['rol_id']);
                $rolQuery->execute();
                $rolResult = $rolQuery->get_result();
                $rol = $rolResult->fetch_assoc()['nombre'] ?? '';

                $_SESSION['rol'] = $rol;

                // Cerrar conexiones antes de redirigir
                $rolQuery->close();
                $stmt->close();
                $conn->close();

                // Redirección según el rol
                if ($rol === 'admin') {
                    header('Location: ../admin/dashboard.php');
                } elseif ($rol === 'duenio') {
                    header('Location: ../duenio/dashboard.php');
                } else {
                    header('Location: ../cliente/dashboard.php');
                }
                exit;
            }
        } else {
            $error = 'Contraseña incorrecta.';
        }
    } else {
        $error = 'Usuario no encontrado.';
    }

    $stmt->close();
}

// Cierre de conexión al final si no hubo redirección
if (isset($conn)) $conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Iniciar sesión - Ofertópolis</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow mx-auto" style="max-width: 400px;">
    <div class="card-body">
      <h4 class="text-center mb-3">Iniciar sesión</h4>

      <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Contraseña</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
      </form>

      <div class="text-center mt-3">
        <a href="register_cliente.php">Registrarse como Cliente</a><br>
        <a href="register_duenio.php">Registrarse como Dueño de Local</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
