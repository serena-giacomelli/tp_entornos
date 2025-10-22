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

// Contar locales del dueño
$count_locales = $conn->query("SELECT COUNT(*) as total FROM locales WHERE id=$duenio_id")->fetch_assoc();

// --- CREAR PROMOCIÓN ---
if (isset($_POST['agregar_promo'])) {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $id = intval($_POST['id']);
    $dias = trim($_POST['dias_vigencia']);
    $categoria = trim($_POST['categoria_minima']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    $conn->query("INSERT INTO promociones 
        (id, titulo, descripcion, fecha_inicio, fecha_fin, dias_vigencia, categoria_minima, estado)
        VALUES ($id,'$titulo','$descripcion','$fecha_inicio','$fecha_fin','$dias','$categoria','pendiente')");
}

// --- ELIMINAR PROMOCIÓN ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM promociones WHERE id=$id AND id IN (SELECT id FROM locales WHERE id=$duenio_id)");
}

// Consultar locales del dueño
$locales = $conn->query("SELECT * FROM locales WHERE id=$duenio_id");
// Consultar promociones del dueño
$promos = $conn->query("SELECT p.*, l.nombre AS local 
                        FROM promociones p 
                        JOIN locales l ON p.id=l.id 
                        WHERE l.id=$duenio_id ORDER BY p.fecha_inicio DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Dueño - Promociones</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🎟️ Panel de Dueño</h3>
    <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
  </div>

  <!-- INFORMACIÓN DEL DUEÑO -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">👤 Información del Dueño</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <p><strong>Nombre:</strong> <?= htmlspecialchars($duenio['nombre']) ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($duenio['email']) ?></p>
        </div>
        <div class="col-md-6">
          <p><strong>Rol:</strong> <span class="badge bg-success">Dueño de Local</span></p>
          <p><strong>Total de Locales:</strong> <?= $count_locales['total'] ?></p>
        </div>
      </div>
    </div>
  </div>

  <h4 class="mb-3">🎟️ Mis Promociones</h4>
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">➕ Nueva Promoción</button>

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Descripción</th>
        <th>Local</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while($p = $promos->fetch_assoc()): ?>
      <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['titulo']) ?></td>
        <td><?= htmlspecialchars($p['descripcion']) ?></td>
        <td><?= htmlspecialchars($p['local']) ?></td>
        <td><?= $p['estado'] ?></td>
        <td>
          <a href="?eliminar=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta promoción?')">🗑️</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <!-- Modal agregar promoción -->
  <div class="modal fade" id="modalAgregar" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Nueva Promoción</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label>Título:</label>
              <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Descripción:</label>
              <textarea name="descripcion" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
              <label>Local:</label>
              <select name="id" class="form-select" required>
                <option value="">Seleccionar...</option>
                <?php
                $locales->data_seek(0);
                while($l = $locales->fetch_assoc()):
                ?>
                  <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nombre']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="mb-3">
              <label>Días de vigencia:</label>
              <input type="text" name="dias_vigencia" class="form-control">
            </div>
            <div class="mb-3">
              <label>Categoría mínima:</label>
              <input type="text" name="categoria_minima" class="form-control">
            </div>
            <div class="mb-3">
              <label>Fecha inicio:</label>
              <input type="date" name="fecha_inicio" class="form-control">
            </div>
            <div class="mb-3">
              <label>Fecha fin:</label>
              <input type="date" name="fecha_fin" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="agregar_promo" class="btn btn-primary">Agregar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
