<?php
session_start();
include_once("../includes/db.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reportes - Administración</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2 class="mb-4">📈 Reportes Gerenciales</h2>
  <a href="admin.php" class="btn btn-secondary mb-3">⬅ Volver al panel</a>

  <!-- Reporte 1: Promociones por local -->
  <div class="card mb-4">
    <div class="card-header bg-dark text-white">Promociones por Local</div>
    <div class="card-body">
      <?php
      $sql1 = "SELECT l.nombre, 
                      COUNT(p.id) AS total, 
                      SUM(p.estado='aprobada') AS aprobadas, 
                      SUM(p.estado='pendiente') AS pendientes, 
                      SUM(p.estado='denegada') AS denegadas
               FROM locales l
               LEFT JOIN promociones p ON l.id = p.id_local
               GROUP BY l.id";
      $res1 = $conn->query($sql1);
      ?>
      <table class="table table-bordered table-striped">
        <thead class="table-secondary">
          <tr>
            <th>Local</th>
            <th>Total</th>
            <th>Aprobadas</th>
            <th>Pendientes</th>
            <th>Denegadas</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $res1->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= $row['total'] ?></td>
            <td><?= $row['aprobadas'] ?></td>
            <td><?= $row['pendientes'] ?></td>
            <td><?= $row['denegadas'] ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Reporte 2: Uso de promociones por cliente -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">Uso de Promociones por Cliente</div>
    <div class="card-body">
      <?php
      $sql2 = "SELECT u.nombre, u.categoria, 
                      COUNT(up.id) AS totalUsos, 
                      SUM(up.estado='aceptada') AS aceptadas, 
                      SUM(up.estado='rechazada') AS rechazadas
               FROM usuarios u
               LEFT JOIN uso_promociones up ON u.id = up.id_cliente
               WHERE u.rol='cliente'
               GROUP BY u.id
               ORDER BY totalUsos DESC";
      $res2 = $conn->query($sql2);
      ?>
      <table class="table table-bordered table-hover">
        <thead class="table-primary">
          <tr>
            <th>Cliente</th>
            <th>Categoría</th>
            <th>Solicitudes Totales</th>
            <th>Aceptadas</th>
            <th>Rechazadas</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $res2->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= $row['categoria'] ?></td>
            <td><?= $row['totalUsos'] ?></td>
            <td><?= $row['aceptadas'] ?></td>
            <td><?= $row['rechazadas'] ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Reporte 3: Categorías de clientes -->
  <div class="card mb-4">
    <div class="card-header bg-success text-white">Distribución de Categorías de Clientes</div>
    <div class="card-body">
      <?php
      $sql3 = "SELECT categoria, COUNT(*) AS cantidad
               FROM usuarios
               WHERE rol='cliente'
               GROUP BY categoria";
      $res3 = $conn->query($sql3);
      ?>
      <table class="table table-bordered table-striped">
        <thead class="table-success">
          <tr>
            <th>Categoría</th>
            <th>Cantidad de Clientes</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $res3->fetch_assoc()): ?>
          <tr>
            <td><?= $row['categoria'] ?></td>
            <td><?= $row['cantidad'] ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Reporte 4: Locales más activos -->
  <div class="card mb-5">
    <div class="card-header bg-info text-white">Locales Más Activos (uso de promociones)</div>
    <div class="card-body">
      <?php
      $sql4 = "SELECT l.nombre, COUNT(up.id) AS totalUsos
               FROM uso_promociones up
               JOIN promociones p ON up.id_promo = p.id
               JOIN locales l ON p.id_local = l.id
               WHERE up.estado='aceptada'
               GROUP BY l.id
               ORDER BY totalUsos DESC
               LIMIT 10";
      $res4 = $conn->query($sql4);
      ?>
      <table class="table table-bordered table-hover">
        <thead class="table-info">
          <tr>
            <th>Local</th>
            <th>Promociones Usadas</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $res4->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= $row['totalUsos'] ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
