<?php
session_start();
include_once("../includes/db.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'duenio') {
    header("Location: ../auth/login.php");
    exit;
}

$duenio_id = $_SESSION['usuario_id'];

// Obtener datos del dueño
$result_duenio = $conn->query("SELECT * FROM usuarios WHERE id=$duenio_id");
$duenio = $result_duenio->fetch_assoc();

// Reporte: Cantidad de clientes que usaron cada descuento
$sql_reportes = "SELECT 
    p.id AS promo_id,
    p.titulo,
    p.descripcion,
    l.nombre AS local,
    COUNT(CASE WHEN up.estado='aceptada' THEN 1 END) AS usos_aceptados,
    COUNT(CASE WHEN up.estado='enviada' THEN 1 END) AS solicitudes_pendientes,
    COUNT(CASE WHEN up.estado='rechazada' THEN 1 END) AS solicitudes_rechazadas,
    COUNT(up.id) AS total_solicitudes
FROM promociones p
JOIN locales l ON p.id_local = l.id
LEFT JOIN uso_promociones up ON up.id_promo = p.id
WHERE l.id_duenio = $duenio_id
GROUP BY p.id, p.titulo, p.descripcion, l.nombre
ORDER BY usos_aceptados DESC";

$reportes = $conn->query($sql_reportes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reportes - Panel Dueño</title>
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
    <h3 style="color: var(--primary-color); font-weight: 700;">Reportes de Uso de Promociones</h3>
    <div>
      <a href="duenio.php" class="btn btn-secondary">Volver al Panel</a>
      <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
  </div>

  <!-- Información del Dueño -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Información del Dueño</h5>
    </div>
    <div class="card-body">
      <p><strong>Nombre:</strong> <?= htmlspecialchars($duenio['nombre']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($duenio['email']) ?></p>
    </div>
  </div>

  <!-- Tabla de Reportes -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Uso de Promociones por Local</h5>
    </div>
    <div class="card-body p-0">
      <?php if ($reportes && $reportes->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background-color: var(--light);">
              <tr>
                <th>Promoción</th>
                <th>Local</th>
                <th class="text-center">Usos Aceptados</th>
                <th class="text-center">Pendientes</th>
                <th class="text-center">Rechazadas</th>
                <th class="text-center">Total Solicitudes</th>
              </tr>
            </thead>
            <tbody>
              <?php while($r = $reportes->fetch_assoc()): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($r['titulo']) ?></strong>
                    <br>
                    <small class="text-muted"><?= htmlspecialchars($r['descripcion']) ?></small>
                  </td>
                  <td><?= htmlspecialchars($r['local']) ?></td>
                  <td class="text-center">
                    <span class="badge bg-success fs-6"><?= $r['usos_aceptados'] ?></span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-warning fs-6"><?= $r['solicitudes_pendientes'] ?></span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-danger fs-6"><?= $r['solicitudes_rechazadas'] ?></span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-secondary fs-6"><?= $r['total_solicitudes'] ?></span>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

        <!-- Resumen Total -->
        <?php
        $reportes->data_seek(0);
        $total_aceptados = 0;
        $total_pendientes = 0;
        $total_rechazados = 0;
        $total_general = 0;
        
        while($r = $reportes->fetch_assoc()) {
            $total_aceptados += $r['usos_aceptados'];
            $total_pendientes += $r['solicitudes_pendientes'];
            $total_rechazados += $r['solicitudes_rechazadas'];
            $total_general += $r['total_solicitudes'];
        }
        ?>
        <div class="card mt-4 shadow-sm">
          <div class="card-header text-white" style="background: linear-gradient(135deg, var(--secondary-color), var(--accent-color)); color: var(--dark) !important;">
            <h5 class="mb-0" style="color: var(--dark);">Resumen Total</h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-md-3">
                <h3 style="color: var(--primary-color);"><?= $total_aceptados ?></h3>
                <p class="text-muted">Usos Aceptados</p>
              </div>
              <div class="col-md-3">
                <h3 style="color: var(--primary-color);"><?= $total_pendientes ?></h3>
                <p class="text-muted">Pendientes</p>
              </div>
              <div class="col-md-3">
                <h3 style="color: var(--primary-color);"><?= $total_rechazados ?></h3>
                <p class="text-muted">Rechazadas</p>
              </div>
              <div class="col-md-3">
                <h3 style="color: var(--primary-color);"><?= $total_general ?></h3>
                <p class="text-muted">Total</p>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="card-body">
          <p class="text-muted text-center">No hay datos de uso de promociones aún.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>
</main>

<?php include("../includes/footer.php"); ?>

</body>
</html>
