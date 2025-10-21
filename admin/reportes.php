<?php
session_start();
include_once("../includes/db.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Consultas para estadísticas
$total_usuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$total_clientes = $conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE rol='cliente'")->fetch_assoc()['total'];
$total_duenos = $conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE rol='dueno'")->fetch_assoc()['total'];
$total_locales = $conn->query("SELECT COUNT(*) AS total FROM locales")->fetch_assoc()['total'];
$total_promos = $conn->query("SELECT COUNT(*) AS total FROM promociones")->fetch_assoc()['total'];
$total_novedades = $conn->query("SELECT COUNT(*) AS total FROM novedades")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reportes - Panel Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>📊 Reportes Generales</h3>
    <a href="admin.php" class="btn btn-secondary">← Volver al panel</a>
  </div>

  <div class="row text-center">
    <div class="col-md-4 mb-3">
      <div class="card border-primary">
        <div class="card-body">
          <h5>Usuarios totales</h5>
          <p class="display-6"><?= $total_usuarios ?></p>
          <small>Clientes: <?= $total_clientes ?> | Dueños: <?= $total_duenos ?></small>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="card border-success">
        <div class="card-body">
          <h5>Locales registrados</h5>
          <p class="display-6"><?= $total_locales ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="card border-warning">
        <div class="card-body">
          <h5>Promociones activas</h5>
          <p class="display-6"><?= $total_promos ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-4 offset-md-4 mb-3">
      <div class="card border-secondary">
        <div class="card-body">
          <h5>Novedades publicadas</h5>
          <p class="display-6"><?= $total_novedades ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php cerrarConexion($conn); ?>
