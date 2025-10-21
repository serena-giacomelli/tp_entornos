<?php
session_start();
include_once("../includes/db.php");

// Verificar sesión
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// --- CREAR LOCAL ---
if (isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $dueno_id = intval($_POST['dueno_id']);

    $sql = "INSERT INTO locales (nombre, direccion, id_dueno) VALUES ('$nombre','$direccion',$dueno_id)";
    $conn->query($sql);
}

// --- ELIMINAR LOCAL ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM locales WHERE id=$id");
}

// --- EDITAR LOCAL ---
if (isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $dueno_id = intval($_POST['dueno_id']);
    $conn->query("UPDATE locales SET nombre='$nombre', direccion='$direccion', id_dueno=$dueno_id WHERE id=$id");
}

// Traer todos los locales
$sql_locales = "SELECT l.*, u.nombre AS dueno 
                FROM locales l 
                LEFT JOIN usuarios u ON l.id_dueno = u.id";
$res_locales = $conn->query($sql_locales);

// Traer dueños para el formulario
$duenos = $conn->query("SELECT id, nombre FROM usuarios WHERE rol='dueno' AND estado='activo'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Locales - Panel Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🏪 Gestión de Locales</h3>
    <a href="admin.php" class="btn btn-secondary">← Volver al panel</a>
  </div>

  <!-- Botón abrir modal -->
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">➕ Nuevo Local</button>

  <!-- Tabla -->
  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Dirección</th>
        <th>Dueño</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while($l = $res_locales->fetch_assoc()): ?>
        <tr>
          <td><?= $l['id'] ?></td>
          <td><?= htmlspecialchars($l['nombre']) ?></td>
          <td><?= htmlspecialchars($l['direccion']) ?></td>
          <td><?= htmlspecialchars($l['dueno'] ?? '—') ?></td>
          <td>
            <!-- Botón editar -->
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $l['id'] ?>">✏️</button>
            <a href="?eliminar=<?= $l['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este local?')">🗑️</a>
          </td>
        </tr>

        <!-- Modal editar -->
        <div class="modal fade" id="modalEditar<?= $l['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header">
                  <h5 class="modal-title">Editar Local</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $l['id'] ?>">
                  <div class="mb-3">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($l['nombre']) ?>" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label>Dirección:</label>
                    <input type="text" name="direccion" value="<?= htmlspecialchars($l['direccion']) ?>" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label>Dueño:</label>
                    <select name="dueno_id" class="form-select">
                      <?php
                      $duenos->data_seek(0);
                      while($d = $duenos->fetch_assoc()):
                      ?>
                        <option value="<?= $d['id'] ?>" <?= ($l['id_dueno'] == $d['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($d['nombre']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="editar" class="btn btn-success">Guardar cambios</button>
                </div>
              </form>
            </div>
          </div>
        </div>
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
          <h5 class="modal-title">Agregar Local</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nombre:</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Dirección:</label>
            <input type="text" name="direccion" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Dueño:</label>
            <select name="dueno_id" class="form-select" required>
              <option value="">Seleccionar...</option>
              <?php
              $duenos->data_seek(0);
              while($d = $duenos->fetch_assoc()):
              ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
              <?php endwhile; ?>
            </select>
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
