<?php
session_start();
include_once("../includes/db.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// --- CREAR PROMOCIÓN ---
if (isset($_POST['agregar'])) {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $id_local = intval($_POST['id_local']);
    $categoria = trim($_POST['categoria_minima']);
    $estado = "aprobada";

    $conn->query("INSERT INTO promociones (titulo, descripcion, id_local, categoria_minima, estado)
                  VALUES ('$titulo','$descripcion',$id_local,'$categoria','$estado')");
}

// --- ELIMINAR PROMOCIÓN ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM promociones WHERE id=$id");
}

// Traer todas las promociones
$promos = $conn->query("SELECT p.*, l.nombre AS local FROM promociones p LEFT JOIN locales l ON p.id_local = l.id");
$locales = $conn->query("SELECT id, nombre FROM locales");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Promociones - Panel Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🎟️ Gestión de Promociones</h3>
    <a href="admin.php" class="btn btn-secondary">← Volver al panel</a>
  </div>

  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">➕ Nueva Promoción</button>

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Descripción</th>
        <th>Local</th>
        <th>Categoría Mínima</th>
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
          <td><?= htmlspecialchars($p['categoria_minima']) ?></td>
          <td><?= htmlspecialchars($p['estado']) ?></td>
          <td>
            <a href="?eliminar=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar promoción?')">🗑️</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Modal agregar -->
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
            <select name="id_local" class="form-select" required>
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
            <label>Categoría mínima:</label>
            <input type="text" name="categoria_minima" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="agregar" class="btn btn-primary">Agregar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php cerrarConexion($conn); ?>
