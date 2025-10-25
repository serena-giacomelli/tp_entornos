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
$sql_duenios = "SELECT * FROM usuarios WHERE rol='duenio' AND estado_cuenta='pendiente'";
$res_duenios = $conn->query($sql_duenios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel del Administrador - OFERTÓPOLIS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/estilos.css" rel="stylesheet">
<link href="../css/header.css" rel="stylesheet">
<link href="../css/footer.css" rel="stylesheet">
<link href="../css/panels.css" rel="stylesheet">
</head>
<body>

<?php include("../includes/header.php"); ?>

<main id="main-content" class="main-content">
<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color: var(--primary-color); font-weight: 700;">Panel del Administrador</h2>
    <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
  </div>

  <div class="alert alert-info">
    Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>.
  </div>

  <?php if(isset($msg)): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

  <!-- Tarjetas de navegación -->
  <div class="row text-center mb-4">
    <div class="col-md-3 mb-3">
      <a href="locales.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-4">
            <h4 style="color: var(--primary-color);">Gestionar Locales</h4>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-3 mb-3">
      <a href="novedades.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-4">
            <h4 style="color: var(--primary-color);">Novedades</h4>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-3 mb-3">
      <a href="promociones.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-4">
            <h4 style="color: var(--primary-color);">Promociones</h4>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-3 mb-3">
      <a href="reportes.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-4">
            <h4 style="color: var(--primary-color);">Reportes</h4>
          </div>
        </div>
      </a>
    </div>
  </div>

  <!-- Dueños pendientes -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Dueños de Local Pendientes de Aprobación</h5>
    </div>
    <div class="card-body">
      <?php if($res_duenios->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background-color: var(--light);">
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
                  <td><strong>#<?= $d['id'] ?></strong></td>
                  <td><?= htmlspecialchars($d['nombre']) ?></td>
                  <td><?= htmlspecialchars($d['email']) ?></td>
                  <td>
                    <a href="../auth/validar.php?id=<?= $d['id'] ?>" class="btn btn-success btn-sm">Aprobar</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted text-center mb-0">No hay dueños pendientes por aprobar.</p>
      <?php endif; ?>
    </div>
  </div>

</div>
</main>

<style>
.hover-card {
  transition: transform 0.2s, box-shadow 0.2s;
}
.hover-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(113, 0, 20, 0.2) !important;
}
</style>

<?php include("../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
