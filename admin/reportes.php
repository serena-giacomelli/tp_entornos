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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reportes - Administración</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/estilos.css" rel="stylesheet">
<link href="../css/header.css" rel="stylesheet">
<link href="../css/footer.css" rel="stylesheet">
<link href="../css/panels.css" rel="stylesheet">
<style>
  .progress {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 10px;
    overflow: hidden;
  }
  
  .progress-bar {
    font-weight: 600;
    font-size: 0.9rem;
    transition: width 0.6s ease;
  }
  
  .card {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: none;
    border-radius: 12px;
    overflow: hidden;
  }
  
  .card-header {
    border: none;
    padding: 1.25rem;
  }
  
  .badge {
    padding: 0.5rem 0.8rem;
    font-weight: 600;
  }
  
  h6 {
    color: #333;
  }
  
  .text-muted {
    font-size: 0.875rem;
  }
</style>
</head>
<body>

<?php include("../includes/header.php"); ?>

<main id="main-content" class="main-content">
<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color: var(--primary-color); font-weight: 700;">Reportes Gerenciales</h2>
    <a href="admin.php" class="btn btn-secondary">Volver al Panel</a>
  </div>

  <!-- Reporte 1: Promociones por local -->
  <div class="card mt-4 shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Promociones por Local</h5>
    </div>
    <div class="card-body">
      <?php
      $sql1 = "SELECT l.nombre, 
                      COUNT(p.id) AS total, 
                      SUM(p.estado='aprobada') AS aprobadas, 
                      SUM(p.estado='pendiente') AS pendientes, 
                      SUM(p.estado='rechazada') AS rechazadas
               FROM locales l
               LEFT JOIN promociones p ON l.id = p.id_local
               GROUP BY l.id
               ORDER BY total DESC";
      $res1 = $conn->query($sql1);
      ?>
      <?php while ($row = $res1->fetch_assoc()): 
        $total = $row['total'] ?: 0;
        $aprobadas = $row['aprobadas'] ?: 0;
        $pendientes = $row['pendientes'] ?: 0;
        $rechazadas = $row['rechazadas'] ?: 0;
        
        $porcentaje_aprobadas = ($total > 0) ? round(($aprobadas / $total) * 100) : 0;
        $porcentaje_pendientes = ($total > 0) ? round(($pendientes / $total) * 100) : 0;
        $porcentaje_rechazadas = ($total > 0) ? round(($rechazadas / $total) * 100) : 0;
      ?>
        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0"><strong><?= htmlspecialchars($row['nombre']) ?></strong></h6>
            <span class="badge bg-secondary"><?= $total ?> total</span>
          </div>
          
          <div class="progress mb-2" style="height: 30px;">
            <?php if ($aprobadas > 0): ?>
              <div class="progress-bar bg-success" style="width: <?= $porcentaje_aprobadas ?>%;" 
                   title="<?= $aprobadas ?> aprobadas">
                <?php if ($porcentaje_aprobadas > 10): ?>
                  <?= $aprobadas ?> aprobadas (<?= $porcentaje_aprobadas ?>%)
                <?php endif; ?>
              </div>
            <?php endif; ?>
            
            <?php if ($pendientes > 0): ?>
              <div class="progress-bar bg-warning" style="width: <?= $porcentaje_pendientes ?>%;" 
                   title="<?= $pendientes ?> pendientes">
                <?php if ($porcentaje_pendientes > 10): ?>
                  <?= $pendientes ?> pendientes (<?= $porcentaje_pendientes ?>%)
                <?php endif; ?>
              </div>
            <?php endif; ?>
            
            <?php if ($rechazadas > 0): ?>
              <div class="progress-bar bg-danger" style="width: <?= $porcentaje_rechazadas ?>%;" 
                   title="<?= $rechazadas ?> rechazadas">
                <?php if ($porcentaje_rechazadas > 10): ?>
                  <?= $rechazadas ?> rechazadas (<?= $porcentaje_rechazadas ?>%)
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          
          <small class="text-muted">
            <span class="badge bg-success"><?= $aprobadas ?> aprobadas</span>
            <span class="badge bg-warning"><?= $pendientes ?> pendientes</span>
            <span class="badge bg-danger"><?= $rechazadas ?> rechazadas</span>
          </small>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Reporte 2: Uso de promociones por cliente -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Uso de Promociones por Cliente</h5>
    </div>
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
               HAVING totalUsos > 0
               ORDER BY totalUsos DESC
               LIMIT 15";
      $res2 = $conn->query($sql2);
      ?>
      <?php while ($row = $res2->fetch_assoc()): 
        $total = $row['totalUsos'] ?: 0;
        $aceptadas = $row['aceptadas'] ?: 0;
        $rechazadas = $row['rechazadas'] ?: 0;
        $porcentaje = ($total > 0) ? round(($aceptadas / $total) * 100) : 0;
        
        // Color según categoría
        $badge_color = match($row['categoria']) {
            'premium' => 'bg-warning text-dark',
            'medium' => 'bg-info',
            default => 'bg-secondary'
        };
      ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <strong><?= htmlspecialchars($row['nombre']) ?></strong>
              <span class="badge <?= $badge_color ?> ms-2"><?= strtoupper($row['categoria']) ?></span>
            </div>
            <small class="text-muted"><?= $total ?> solicitudes</small>
          </div>
          
          <div class="progress" style="height: 25px;">
            <div class="progress-bar bg-success" 
                 style="width: <?= $porcentaje ?>%;" 
                 role="progressbar"
                 aria-valuenow="<?= $porcentaje ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
              <?= $porcentaje ?>% aceptadas (<?= $aceptadas ?>/<?= $total ?>)
            </div>
          </div>
          
          <?php if ($rechazadas > 0): ?>
            <small class="text-danger"><?= $rechazadas ?> rechazadas</small>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
      
      <?php if ($res2->num_rows == 0): ?>
        <p class="text-muted text-center">No hay datos de uso de promociones aún.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Reporte 3: Categorías de clientes -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
      <h5 class="mb-0">Distribución de Categorías de Clientes</h5>
    </div>
    <div class="card-body">
      <?php
      $sql3 = "SELECT categoria, COUNT(*) AS cantidad
               FROM usuarios
               WHERE rol='cliente'
               GROUP BY categoria
               ORDER BY cantidad DESC";
      $res3 = $conn->query($sql3);
      
      // Calcular total para porcentajes
      $total_clientes_result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='cliente'");
      $total_clientes = $total_clientes_result->fetch_assoc()['total'];
      ?>
      
      <?php while ($row = $res3->fetch_assoc()): 
        $cantidad = $row['cantidad'];
        $porcentaje = ($total_clientes > 0) ? round(($cantidad / $total_clientes) * 100) : 0;
        
        // Color según categoría
        $datos_categoria = match($row['categoria']) {
            'premium' => ['color' => 'warning', 'texto' => 'Premium'],
            'medium' => ['color' => 'info', 'texto' => 'Medium'],
            default => ['color' => 'secondary', 'texto' => 'Inicial']
        };
      ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">
              <strong><?= $datos_categoria['texto'] ?></strong>
            </h6>
            <span class="badge bg-<?= $datos_categoria['color'] ?> text-dark">
              <?= $cantidad ?> clientes (<?= $porcentaje ?>%)
            </span>
          </div>
          
          <div class="progress" style="height: 30px;">
            <div class="progress-bar bg-<?= $datos_categoria['color'] ?>" 
                 style="width: <?= $porcentaje ?>%;"
                 role="progressbar">
              <?= $porcentaje ?>%
            </div>
          </div>
        </div>
      <?php endwhile; ?>
      
      <hr>
      <p class="text-center mb-0">
        <strong>Total de clientes registrados:</strong> 
        <span class="badge bg-success"><?= $total_clientes ?></span>
      </p>
    </div>
  </div>

  <!-- Reporte 4: Locales más activos -->
  <div class="card mb-5 shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, #17a2b8, #117a8b);">
      <h5 class="mb-0">Locales Más Activos (Top 10)</h5>
      <small>Promociones aceptadas por clientes</small>
    </div>
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
      
      // Obtener el máximo para escalar las barras
      $res4_temp = $conn->query($sql4);
      $max_usos = 0;
      if ($row_max = $res4_temp->fetch_assoc()) {
        $max_usos = $row_max['totalUsos'];
      }
      ?>
      
      <?php 
      $posicion = 1;
      while ($row = $res4->fetch_assoc()): 
        $usos = $row['totalUsos'];
        $porcentaje = ($max_usos > 0) ? round(($usos / $max_usos) * 100) : 0;
        
        // Posición
        $posicion_texto = "#{$posicion}";
        
        // Color degradado según posición
        $color = match(true) {
            $posicion <= 3 => 'success',
            $posicion <= 6 => 'primary',
            default => 'info'
        };
      ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <span class="me-2 fw-bold"><?= $posicion_texto ?></span>
              <strong><?= htmlspecialchars($row['nombre']) ?></strong>
            </div>
            <span class="badge bg-<?= $color ?>"><?= $usos ?> usos</span>
          </div>
          
          <div class="progress" style="height: 25px;">
            <div class="progress-bar bg-<?= $color ?>" 
                 style="width: <?= $porcentaje ?>%;"
                 role="progressbar">
              <?php if ($porcentaje > 15): ?>
                <?= $usos ?> promociones utilizadas
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php 
        $posicion++;
      endwhile; 
      ?>
      
      <?php if ($res4->num_rows == 0): ?>
        <p class="text-muted text-center">No hay datos de uso de promociones aún.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</main>

<?php include("../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
