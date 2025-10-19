<?php
require_once('../includes/db.php');
require_once('../includes/functions.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo el administrador puede acceder
if (!isAdmin()) {
    header("Location: ../auth/login.php");
    exit;
}

$mensaje = "";

// ==============================
// 1️⃣ APROBAR/RECHAZAR PROMOCIÓN
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
    $id = (int) $_POST['id'];
    $nuevo_estado = $_POST['nuevo_estado'];

    $stmt = $conn->prepare("UPDATE promociones SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id);
    $stmt->execute();

    $mensaje = "⚙️ Estado de promoción actualizado correctamente.";
    $stmt->close();
}

// ==============================
// 2️⃣ ELIMINAR PROMOCIÓN
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = (int) $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM promociones WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $mensaje = "🗑️ Promoción eliminada.";
    $stmt->close();
}

// ==============================
// 3️⃣ CREAR PROMOCIÓN (ADMIN)
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $descuento = (float) $_POST['descuento'];
    $local_id = (int) $_POST['local_id'];
    $categoria_cliente_id = (int) $_POST['categoria_cliente_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    $stmt = $conn->prepare("INSERT INTO promociones (titulo, descripcion, descuento, local_id, categoria_cliente_id, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'aprobada')");
    $stmt->bind_param("ssdiiiss", $titulo, $descripcion, $descuento, $local_id, $categoria_cliente_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();

    $mensaje = $stmt->affected_rows > 0 ? "✅ Promoción creada correctamente." : "❌ Error al crear la promoción.";

    $stmt->close();
}

// ==============================
// 4️⃣ LISTAR PROMOCIONES
// ==============================
$promociones = $conn->query("
    SELECT p.id, p.titulo, p.descripcion, p.descuento, p.fecha_inicio, p.fecha_fin, p.estado, p.fecha_creacion,
           l.nombre AS local_nombre, l.direccion AS local_direccion,
           cc.nombre AS categoria_cliente,
           u.nombre AS duenio_nombre
    FROM promociones p
    JOIN locales l ON p.local_id = l.id
    JOIN categorias_clientes cc ON p.categoria_cliente_id = cc.id
    JOIN usuarios u ON l.duenio_id = u.id
    ORDER BY p.fecha_creacion DESC
");

// ==============================
// 5️⃣ OBTENER LOCALES Y CATEGORÍAS
// ==============================
$locales = $conn->query("
    SELECT l.id, l.nombre, u.nombre AS duenio_nombre
    FROM locales l
    JOIN usuarios u ON l.duenio_id = u.id
    WHERE u.estado = 'activo'
    ORDER BY l.nombre
");

$categorias = $conn->query("SELECT id, nombre FROM categorias_clientes ORDER BY nombre");

// ==============================
// CIERRE DE CONEXIÓN
// ==============================
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Promociones - Ofertópolis</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
    body { background-color: #f8f9fa; }
    .card { border-radius: 12px; }
    .badge-pendiente { background-color: #ffc107; }
    .badge-aprobada { background-color: #198754; }
    .badge-rechazada { background-color: #dc3545; }
    .badge-expirada { background-color: #6c757d; }
</style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <span class="navbar-brand">Ofertópolis - Administración de Promociones</span>
    <div class="d-flex">
      <a href="../admin/dashboard.php" class="btn btn-outline-light btn-sm me-2">Volver al Panel</a>
      <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
    </div>
  </div>
</nav>

<div class="container">
  <h3 class="text-center mb-4">Gestión de Promociones</h3>

  <?php if ($mensaje): ?>
    <div class="alert alert-info text-center"><?= $mensaje ?></div>
  <?php endif; ?>

  <!-- Formulario Crear Promoción -->
  <div class="card mb-4 shadow">
    <div class="card-body">
      <h5 class="card-title">Crear nueva promoción</h5>
      <form method="POST">
        <input type="hidden" name="accion" value="crear">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Descuento (%)</label>
            <input type="number" name="descuento" class="form-control" min="1" max="100" step="0.01" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Local</label>
            <select name="local_id" class="form-select" required>
              <option value="">Seleccione un local...</option>
              <?php while ($local = $locales->fetch_assoc()): ?>
                <option value="<?= $local['id'] ?>"><?= htmlspecialchars($local['nombre']) ?> (<?= htmlspecialchars($local['duenio_nombre']) ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Categoría de cliente</label>
            <select name="categoria_cliente_id" class="form-select" required>
              <option value="">Seleccione categoría...</option>
              <?php while ($cat = $categorias->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Fecha de inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Fecha de fin</label>
            <input type="date" name="fecha_fin" class="form-control" required>
          </div>
        </div>
        <button type="submit" class="btn btn-success w-100">Crear Promoción</button>
      </form>
    </div>
  </div>

  <!-- Tabla de Promociones -->
  <div class="card shadow">
    <div class="card-body">
      <h5 class="card-title mb-3">Promociones existentes</h5>
      <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Título</th>
            <th>Local</th>
            <th>Dueño</th>
            <th>Descuento</th>
            <th>Categoría</th>
            <th>Vigencia</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($promociones->num_rows > 0): ?>
            <?php while ($p = $promociones->fetch_assoc()): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($p['titulo']) ?></strong><br>
                <small class="text-muted"><?= htmlspecialchars(substr($p['descripcion'], 0, 80)) ?>...</small>
              </td>
              <td><?= htmlspecialchars($p['local_nombre']) ?></td>
              <td><?= htmlspecialchars($p['duenio_nombre']) ?></td>
              <td class="text-center">
                <span class="badge bg-primary"><?= $p['descuento'] ?>%</span>
              </td>
              <td><?= htmlspecialchars($p['categoria_cliente']) ?></td>
              <td>
                <small>
                  Del: <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?><br>
                  Al: <?= date('d/m/Y', strtotime($p['fecha_fin'])) ?>
                </small>
              </td>
              <td>
                <span class="badge badge-<?= $p['estado'] ?>">
                  <?= ucfirst($p['estado']) ?>
                </span>
              </td>
              <td class="text-center">
                <?php if ($p['estado'] === 'pendiente'): ?>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <input type="hidden" name="nuevo_estado" value="aprobada">
                    <button class="btn btn-success btn-sm">Aprobar</button>
                  </form>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <input type="hidden" name="nuevo_estado" value="rechazada">
                    <button class="btn btn-warning btn-sm">Rechazar</button>
                  </form>
                <?php elseif ($p['estado'] === 'aprobada'): ?>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <input type="hidden" name="nuevo_estado" value="pendiente">
                    <button class="btn btn-secondary btn-sm">Pausar</button>
                  </form>
                <?php endif; ?>
                
                <form method="POST" class="d-inline">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <input type="hidden" name="accion" value="eliminar">
                  <button class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta promoción?')">Eliminar</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted">No hay promociones cargadas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<script>
// Validar que la fecha de fin sea posterior a la fecha de inicio
document.querySelector('form').addEventListener('submit', function(e) {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]').value;
    
    if (fechaInicio && fechaFin && fechaFin <= fechaInicio) {
        e.preventDefault();
        alert('La fecha de fin debe ser posterior a la fecha de inicio.');
    }
});
</script>

</body>
</html>
