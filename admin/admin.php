<?php
session_start();
include_once("../includes/db.php");
// Verificar sesión y rol
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Mensaje de aprobación
if (isset($_GET['msg']) && $_GET['msg'] == 'duenio_aprobado') {
    $msg = "Dueño aprobado correctamente.";
}

// Consultar dueños pendientes
$sql_duenios = "SELECT * FROM usuarios WHERE rol='duenio' AND estado='pendiente'";
$res_duenios = $conn->query($sql_duenios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del Administrador - Ofertópolis</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Panel del Administrador</h2>
    <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
  </div>

  <div class="alert alert-info">
    Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>.
  </div>

  <?php if(isset($msg)): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

  <!-- Tarjetas de navegación -->
  <div class="row text-center mb-4">
    <div class="col-md-3 mb-3">
      <a href="locales.php" class="btn btn-outline-primary w-100 py-3">
        🏪 Gestionar Locales
      </a>
    </div>
    <div class="col-md-3 mb-3">
      <a href="novedades.php" class="btn btn-outline-success w-100 py-3">
        📰 Novedades
      </a>
    </div>
    <div class="col-md-3 mb-3">
      <a href="promociones.php" class="btn btn-outline-warning w-100 py-3">
        🎟️ Promociones
      </a>
    </div>
    <div class="col-md-3 mb-3">
      <a href="reportes.php" class="btn btn-outline-dark w-100 py-3">
        📊 Reportes
      </a>
    </div>
  </div>

  <!-- Dueños pendientes -->
  <div class="card">
    <div class="card-header bg-secondary text-white">
      Dueños de Local Pendientes de Aprobación
    </div>
    <div class="card-body">
      <?php if($res_duenios->num_rows > 0): ?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php while($d = $res_duenios->fetch_assoc()): ?>
              <tr>
                <td><?= $d['id'] ?></td>
                <td><?= htmlspecialchars($d['nombre']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td>
                  <a href="../auth/validar.php?id=<?= $d['id'] ?>" class="btn btn-success btn-sm">Aprobar</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted">No hay dueños pendientes por aprobar.</p>
      <?php endif; ?>
    </div>
  </div>

</div>

</body>
</html>
