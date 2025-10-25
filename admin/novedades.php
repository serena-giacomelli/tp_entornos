<?php
session_start();
include_once("../includes/db.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// --- CREAR NOVEDAD ---
if (isset($_POST['agregar'])) {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $categoria_destino = $_POST['categoria_destino'];
    $dias_vigencia = intval($_POST['dias_vigencia']);
    $fecha = date("Y-m-d");
    
    // Calcular fecha de vencimiento según días de vigencia
    $fecha_vencimiento = date("Y-m-d", strtotime("+$dias_vigencia days"));
    
    $conn->query("INSERT INTO novedades (titulo, contenido, categoria_destino, fecha_publicacion, fecha_vencimiento) 
                  VALUES ('$titulo','$contenido','$categoria_destino','$fecha','$fecha_vencimiento')");
}

// --- ELIMINAR ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM novedades WHERE id=$id");
}

// Consultar todas las novedades (incluyendo las vencidas para gestión)
$novedades = $conn->query("SELECT * FROM novedades ORDER BY fecha_publicacion DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Novedades - Panel Admin</title>
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
    <h3 style="color: var(--primary-color); font-weight: 700;">Gestión de Novedades</h3>
    <div>
      <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalAgregar">Nueva Novedad</button>
      <a href="admin.php" class="btn btn-secondary">Volver al Panel</a>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Lista de Novedades</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background-color: var(--light);">
            <tr>
              <th>ID</th>
              <th>Título</th>
              <th>Contenido</th>
              <th>Categoría Destino</th>
              <th>Fecha Publicación</th>
              <th>Vence</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
      <?php while($n = $novedades->fetch_assoc()): 
        $badge_color = match($n['categoria_destino']) {
          'inicial' => 'secondary',
          'medium' => 'primary',
          'premium' => 'warning',
          default => 'secondary'
        };
        
        // Verificar si está vencida
        $esta_vencida = false;
        $fecha_venc = '';
        if (!empty($n['fecha_vencimiento'])) {
          $fecha_venc = date("d/m/Y", strtotime($n['fecha_vencimiento']));
          $esta_vencida = strtotime($n['fecha_vencimiento']) < strtotime('today');
        }
      ?>
        <tr class="<?= $esta_vencida ? 'table-secondary' : '' ?>">
          <td><strong>#<?= $n['id'] ?></strong></td>
          <td><?= htmlspecialchars($n['titulo']) ?></td>
          <td><?= htmlspecialchars($n['contenido']) ?></td>
          <td><span class="badge bg-<?= $badge_color ?>"><?= ucfirst($n['categoria_destino']) ?></span></td>
          <td><?= date("d/m/Y", strtotime($n['fecha_publicacion'])) ?></td>
          <td>
            <?php if (!empty($n['fecha_vencimiento'])): ?>
              <?= $fecha_venc ?>
            <?php else: ?>
              <span class="text-muted">Sin vencimiento</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($esta_vencida): ?>
              <span class="badge bg-danger">Vencida</span>
            <?php else: ?>
              <span class="badge bg-success">Vigente</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="?eliminar=<?= $n['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta novedad?')">Eliminar</a>
          </td>
        </tr>
      <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</main>

<!-- Modal agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
        <h5 class="modal-title">Agregar Novedad</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Título:</label>
            <input type="text" name="titulo" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Contenido:</label>
            <textarea name="contenido" class="form-control" rows="4" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Categoría Destino:</label>
            <select name="categoria_destino" class="form-select" required>
              <option value="">Seleccionar categoría...</option>
              <option value="inicial">Inicial</option>
              <option value="medium">Medium</option>
              <option value="premium">Premium</option>
            </select>
            <small class="text-muted">
              Los clientes solo verán novedades de su categoría o inferiores.
            </small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Días de Vigencia:</label>
            <input type="number" name="dias_vigencia" class="form-control" min="1" value="30" required>
            <small class="text-muted">
              La novedad expirará automáticamente después de este período. Por defecto: 30 días.
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="agregar" class="btn btn-primary">Agregar Novedad</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
