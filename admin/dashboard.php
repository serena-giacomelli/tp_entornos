<?php
require_once('../includes/db.php');
require_once('../includes/functions.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo puede acceder el administrador
if (!isAdmin()) {
    header("Location: ../auth/login.php");
    exit;
}

// ==============================
// CONSULTAS PRINCIPALES
// ==============================

// Cantidad de usuarios por rol
$rolesData = $conn->query("
    SELECT rol, COUNT(id) AS cantidad
    FROM usuarios
    GROUP BY rol
");

// Dueños pendientes
$dueniosPendientes = $conn->query("
    SELECT id, nombre, email, fecha_registro 
    FROM usuarios 
    WHERE rol = 'duenio' AND estado_cuenta = 'pendiente'
");

// Cantidad de promociones activas
$promosActivas = $conn->query("
    SELECT COUNT(*) AS total FROM promociones WHERE estado = 'aprobada'
")->fetch_assoc()['total'];

// Cantidad de novedades activas
$novedadesActivas = $conn->query("
    SELECT COUNT(*) AS total FROM novedades
")->fetch_assoc()['total'];

// Acción: aprobar/rechazar dueño
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_duenio'])) {
    $accion = $_POST['accion'];
    $id_duenio = (int) $_POST['id_duenio'];

    if ($accion === 'aprobar') {
        $update = $conn->prepare("UPDATE usuarios SET estado_cuenta = 'activo' WHERE id = ?");
    } else {
        $update = $conn->prepare("UPDATE usuarios SET estado_cuenta = 'denegado' WHERE id = ?");
    }

    $update->bind_param("i", $id_duenio);
    $update->execute();
    $update->close();

    // Refrescar la página para ver cambios
    header("Location: dashboard.php");
    $conn->close();
    exit;
}

// ==============================
// CIERRE DE CONEXIÓN
// ==============================
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del Administrador - Ofertópolis</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
    body { background: #f8f9fa; }
    .card-stats { text-align: center; }
    .card-stats h3 { margin: 0; font-size: 2.2rem; }
    .table td, .table th { vertical-align: middle; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Ofertópolis - Administrador</span>
    <div class="d-flex">
      <span class="text-white me-3"><?= $_SESSION['nombre'] ?></span>
      <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h3 class="mb-4 text-center">Panel del Administrador</h3>

  <!-- Estadísticas rápidas -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card card-stats shadow">
        <div class="card-body">
          <h5>Promociones Activas</h5>
          <h3><?= $promosActivas ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-stats shadow">
        <div class="card-body">
          <h5>Novedades Activas</h5>
          <h3><?= $novedadesActivas ?></h3>
        </div>
      </div>
    </div>
    <?php while ($rol = $rolesData->fetch_assoc()): ?>
    <div class="col-md-3">
      <div class="card card-stats shadow">
        <div class="card-body">
          <h5><?= ucfirst($rol['rol']) . "s" ?></h5>
          <h3><?= $rol['cantidad'] ?></h3>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <!-- Dueños pendientes -->
  <div class="card shadow">
    <div class="card-body">
      <h5 class="card-title mb-3">Dueños Pendientes de Aprobación</h5>
      <?php if ($dueniosPendientes->num_rows > 0): ?>
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Fecha de Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($d = $dueniosPendientes->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($d['nombre']) ?></td>
            <td><?= htmlspecialchars($d['email']) ?></td>
            <td><?= date('d/m/Y', strtotime($d['fecha_registro'])) ?></td>
            <td class="text-center">
              <form method="POST" class="d-inline">
                <input type="hidden" name="id_duenio" value="<?= $d['id'] ?>">
                <button name="accion" value="aprobar" class="btn btn-success btn-sm">Aprobar</button>
              </form>
              <form method="POST" class="d-inline">
                <input type="hidden" name="id_duenio" value="<?= $d['id'] ?>">
                <button name="accion" value="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted text-center">No hay dueños pendientes.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="novedades.php" class="btn btn-primary">Gestionar Novedades</a>
    <a href="../promociones/gestionar.php" class="btn btn-secondary">Ver Promociones</a>
  </div>
</div>

</body>
</html>
