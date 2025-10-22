<?php
session_start();
include_once("../includes/db.php");
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_GET['accion']) && isset($_GET['id'])) {
    $accion = $_GET['accion'];
    $id = intval($_GET['id']);
    if (in_array($accion, ['activo', 'denegado'])) {
        $conn->query("UPDATE usuarios SET estado_cuenta='$accion' WHERE id=$id AND rol='duenio'");
    }
}

$res = $conn->query("SELECT * FROM usuarios WHERE rol='duenio'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Validar Dueños</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3 class="mb-3">Aprobación de Dueños de Local</h3>
  <a href="admin.php" class="btn btn-secondary mb-3">Volver al panel</a>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($u = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['nombre']) ?></td>
          <td><?= ucfirst($u['estado_cuenta']) ?></td>
          <td>
            <?php if ($u['estado_cuenta'] == 'pendiente'): ?>
              <a href="?accion=activo&id=<?= $u['id'] ?>" class="btn btn-success btn-sm">Aprobar</a>
              <a href="?accion=denegado&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm">Rechazar</a>
            <?php else: ?>
              <span class="text-muted">Ya procesado</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
