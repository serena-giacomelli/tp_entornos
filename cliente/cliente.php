<?php
session_start();
include_once("config/db.php");
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'cliente') {
    header("Location: auth.php");
    exit;
}

$id = $_SESSION['usuario_id'];
$result = $conn->query("SELECT * FROM usuarios WHERE id=$id");
$cliente = $result->fetch_assoc();

// --- ACTUALIZAR PERFIL ---
if (isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = !empty($_POST['password']) ? md5($_POST['password']) : $cliente['password'];

    $sql = "UPDATE usuarios SET nombre='$nombre', email='$email', password='$password' WHERE id=$id";
    if ($conn->query($sql)) {
        $_SESSION['usuario_nombre'] = $nombre;
        $mensaje = "Perfil actualizado correctamente.";
    } else {
        $error = "Error al actualizar perfil: " . $conn->error;
    }
    $result = $conn->query("SELECT * FROM usuarios WHERE id=$id");
    $cliente = $result->fetch_assoc();
}

cerrarConexion($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del Cliente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>👤 Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h3>
    <a href="auth.php?logout=true" class="btn btn-danger">Cerrar sesión</a>
  </div>

  <?php if(isset($mensaje)): ?><div class="alert alert-success"><?= $mensaje ?></div><?php endif; ?>
  <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

  <div class="card">
    <div class="card-header bg-dark text-white">Mi Perfil</div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label>Nombre:</label>
          <input type="text" name="nombre" value="<?= $cliente['nombre'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Email:</label>
          <input type="email" name="email" value="<?= $cliente['email'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Contraseña (dejar en blanco si no desea cambiarla):</label>
          <input type="password" name="password" class="form-control">
        </div>
        <button type="submit" name="guardar" class="btn btn-primary">Guardar cambios</button>
      </form>
    </div>
  </div>

  <div class="card mt-4">
    <div class="card-header bg-secondary text-white">🎁 Mis Promociones Disponibles</div>
    <div class="card-body">
      <?php
      include("config/db.php");
      $categoria = $_SESSION['usuario_categoria'];
      $sql = "SELECT p.titulo, p.descripcion, l.nombre AS local
              FROM promociones p 
              JOIN locales l ON p.id_local = l.id
              WHERE p.estado='aprobada'
              AND (p.categoria_minima='$categoria' OR p.categoria_minima='Inicial')";
      $res = $conn->query($sql);
      if ($res->num_rows > 0) {
          while ($promo = $res->fetch_assoc()) {
              echo "<p><strong>{$promo['titulo']}</strong> ({$promo['local']})<br>{$promo['descripcion']}</p><hr>";
          }
      } else {
          echo "<p>No hay promociones disponibles por el momento.</p>";
      }
      cerrarConexion($conn);
      ?>
    </div>
  </div>
</div>
</body>
</html>
