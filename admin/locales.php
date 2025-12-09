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
    $ubicacion = trim($_POST['ubicacion']);
    $rubro = trim($_POST['rubro']);
    $duenio_id = intval($_POST['duenio_id']);

    $sql = "INSERT INTO locales (nombre, ubicacion, rubro, id_duenio) VALUES ('$nombre','$ubicacion','$rubro',$duenio_id)";
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
    $ubicacion = trim($_POST['ubicacion']);
    $rubro = trim($_POST['rubro']);
    $duenio_id = intval($_POST['duenio_id']);
    // No actualizar el código (se mantiene el original)
    $conn->query("UPDATE locales SET nombre='$nombre', ubicacion='$ubicacion', rubro='$rubro', id_duenio=$duenio_id WHERE id=$id");
}

// Lista de rubros disponibles
$rubros = [
    'Indumentaria',
    'Gastronomía',
    'Tecnología',
    'Deportes',
    'Hogar y Decoración',
    'Belleza y Salud',
    'Juguetería',
    'Librería',
    'Joyería',
    'Calzado',
    'Electrónica',
    'Supermercado',
    'Otro'
];

// Traer todos los locales
$sql_locales = "SELECT l.*, u.nombre AS duenio 
                FROM locales l 
                LEFT JOIN usuarios u ON l.id_duenio = u.id";
$res_locales = $conn->query($sql_locales);

// Traer dueños para el formulario
$duenios = $conn->query("SELECT id, nombre FROM usuarios WHERE rol='duenio' AND estado_cuenta='activo'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Locales - Panel Admin</title>
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
    <h3 style="color: var(--primary-color); font-weight: 700;">Gestión de Locales</h3>
    <div>
      <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalAgregar">Nuevo Local</button>
      <a href="admin.php" class="btn btn-secondary">Volver al Panel</a>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Lista de Locales</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background-color: var(--light);">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Ubicación</th>
              <th>Rubro</th>
              <th>Dueño</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
      <?php while($l = $res_locales->fetch_assoc()): ?>
        <tr>
          <td><strong>#<?= $l['id'] ?></strong></td>
          <td><?= htmlspecialchars($l['nombre']) ?></td>
          <td><?= htmlspecialchars($l['ubicacion'] ?? '—') ?></td>
          <td><span class="badge bg-info"><?= htmlspecialchars($l['rubro']) ?></span></td>
          <td><?= htmlspecialchars($l['duenio'] ?? '—') ?></td>
          <td>
            <!-- Botón editar -->
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $l['id'] ?>">Editar</button>
            <a href="?eliminar=<?= $l['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este local?')">Eliminar</a>
          </td>
        </tr>

        <!-- Modal editar -->
        <div class="modal fade" id="modalEditar<?= $l['id'] ?>" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
                <h5 class="modal-title">Editar Local</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <form method="POST">
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $l['id'] ?>">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Nombre:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($l['nombre']) ?>" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Ubicación:</label>
                    <input type="text" name="ubicacion" value="<?= htmlspecialchars($l['ubicacion'] ?? '') ?>" class="form-control" placeholder="Ej: Planta Baja - Local 12">
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Rubro:</label>
                    <select name="rubro" class="form-select" required>
                      <?php foreach($rubros as $r): ?>
                        <option value="<?= $r ?>" <?= ($l['rubro'] == $r) ? 'selected' : '' ?>>
                          <?= $r ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-bold">Dueño:</label>
                    <select name="duenio_id" class="form-select">
                      <?php
                      $duenios->data_seek(0);
                      while($d = $duenios->fetch_assoc()):
                      ?>
                        <option value="<?= $d['id'] ?>" <?= ($l['id_duenio'] == $d['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($d['nombre']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" name="editar" class="btn btn-primary">Guardar cambios</button>
                </div>
              </form>
            </div>
          </div>
        </div>
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
        <h5 class="modal-title">Agregar Local</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Nombre:</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Ubicación:</label>
            <input type="text" name="ubicacion" class="form-control" placeholder="Ej: Planta Baja - Local 12">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Rubro:</label>
            <select name="rubro" class="form-select" required>
              <option value="">Seleccionar rubro...</option>
              <?php foreach($rubros as $r): ?>
                <option value="<?= $r ?>"><?= $r ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Dueño:</label>
            <select name="duenio_id" class="form-select" required>
              <option value="">Seleccionar...</option>
              <?php
              $duenios->data_seek(0);
              while($d = $duenios->fetch_assoc()):
              ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="agregar" class="btn btn-primary">Agregar Local</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
