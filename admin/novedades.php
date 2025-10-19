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
// 1️⃣ CREAR NOVEDAD
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $titulo = trim($_POST['titulo']);
    $texto = trim($_POST['texto']);
    $categoria = (int) $_POST['categoria_destino_id'];
    $fecha_expiracion = $_POST['fecha_expiracion'];

    $stmt = $conn->prepare("INSERT INTO novedades (titulo, texto, categoria_destino_id, fecha_expiracion, estado) VALUES (?, ?, ?, ?, 'activa')");
    $stmt->bind_param("ssis", $titulo, $texto, $categoria, $fecha_expiracion);
    $stmt->execute();

    $mensaje = $stmt->affected_rows > 0 ? "✅ Novedad creada correctamente." : "❌ Error al crear la novedad.";

    $stmt->close();
}

// ==============================
// 2️⃣ ACTUALIZAR ESTADO
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
    $id = (int) $_POST['id'];
    $nuevo_estado = $_POST['nuevo_estado'];

    $stmt = $conn->prepare("UPDATE novedades SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id);
    $stmt->execute();

    $mensaje = "⚙️ Estado actualizado correctamente.";
    $stmt->close();
}

// ==============================
// 3️⃣ ELIMINAR NOVEDAD
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = (int) $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM novedades WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $mensaje = "🗑️ Novedad eliminada.";
    $stmt->close();
}

// ==============================
// 4️⃣ LISTAR NOVEDADES EXISTENTES
// ==============================
$novedades = $conn->query("
    SELECT n.id, n.titulo, n.texto, n.fecha_publicacion, n.fecha_expiracion, n.estado, c.nombre AS categoria
    FROM novedades n
    JOIN categorias_clientes c ON n.categoria_destino_id = c.id
    ORDER BY n.fecha_publicacion DESC
");

$categorias = $conn->query("SELECT id, nombre FROM categorias_clientes");

// ==============================
// CIERRE DE CONEXIÓN
// ==============================
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Novedades - Ofertópolis</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
    body { background-color: #f8f9fa; }
    .card { border-radius: 12px; }
</style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <span class="navbar-brand">Ofertópolis - Administración de Novedades</span>
    <div class="d-flex">
      <a href="../admin/dashboard.php" class="btn btn-outline-light btn-sm me-2">Volver al Panel</a>
      <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
    </div>
  </div>
</nav>

<div class="container">
  <h3 class="text-center mb-4">Gestión de Novedades</h3>

  <?php if ($mensaje): ?>
    <div class="alert alert-info text-center"><?= $mensaje ?></div>
  <?php endif; ?>

  <!-- Formulario Crear Novedad -->
  <div class="card mb-4 shadow">
    <div class="card-body">
      <h5 class="card-title">Crear nueva novedad</h5>
      <form method="POST">
        <input type="hidden" name="accion" value="crear">
        <div class="mb-3">
          <label class="form-label">Título</label>
          <input type="text" name="titulo" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Texto</label>
          <textarea name="texto" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Categoría destino</label>
          <select name="categoria_destino_id" class="form-select" required>
            <option value="">Seleccione...</option>
            <?php while ($cat = $categorias->fetch_assoc()): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Fecha de expiración</label>
          <input type="date" name="fecha_expiracion" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Crear Novedad</button>
      </form>
    </div>
  </div>

  <!-- Tabla de Novedades -->
  <div class="card shadow">
    <div class="card-body">
      <h5 class="card-title mb-3">Novedades existentes</h5>
      <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Título</th>
            <th>Categoría</th>
            <th>Publicación</th>
            <th>Expiración</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($novedades->num_rows > 0): ?>
            <?php while ($n = $novedades->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($n['titulo']) ?></td>
              <td><?= htmlspecialchars($n['categoria']) ?></td>
              <td><?= date('d/m/Y', strtotime($n['fecha_publicacion'])) ?></td>
              <td><?= date('d/m/Y', strtotime($n['fecha_expiracion'])) ?></td>
              <td>
                <span class="badge bg-<?= $n['estado'] === 'activa' ? 'success' : 'secondary' ?>">
                  <?= ucfirst($n['estado']) ?>
                </span>
              </td>
              <td class="text-center">
                <form method="POST" class="d-inline">
                  <input type="hidden" name="id" value="<?= $n['id'] ?>">
                  <input type="hidden" name="accion" value="cambiar_estado">
                  <input type="hidden" name="nuevo_estado" value="<?= $n['estado'] === 'activa' ? 'inactiva' : 'activa' ?>">
                  <button class="btn btn-warning btn-sm">
                    <?= $n['estado'] === 'activa' ? 'Inactivar' : 'Activar' ?>
                  </button>
                </form>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="id" value="<?= $n['id'] ?>">
                  <input type="hidden" name="accion" value="eliminar">
                  <button class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta novedad?')">Eliminar</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center text-muted">No hay novedades cargadas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
