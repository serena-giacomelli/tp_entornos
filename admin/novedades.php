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
    $fecha = date("Y-m-d");
    $conn->query("INSERT INTO novedades (titulo, contenido, fecha_publicacion) VALUES ('$titulo','$contenido','$fecha')");
}

// --- ELIMINAR ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM novedades WHERE id=$id");
}

// Consultar todas las novedades
$novedades = $conn->query("SELECT * FROM novedades ORDER BY fecha_publicacion DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Novedades - Panel Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>📰 Gestión de Novedades</h3>
    <a href="admin.php" class="btn btn-secondary">← Volver al panel</a>
  </div>

  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">➕ Nueva Novedad</button>

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Contenido</th>
        <th>Fecha</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while($n = $novedades->fetch_assoc()): ?>
        <tr>
          <td><?= $n['id'] ?></td>
          <td><?= htmlspecialchars($n['titulo']) ?></td>
          <td><?= htmlspecialchars($n['contenido']) ?></td>
          <td><?= date("d/m/Y", strtotime($n['fecha_publicacion'])) ?></td>
          <td>
            <a href="?eliminar=<?= $n['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta novedad?')">🗑️</a>
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
          <h5 class="modal-title">Agregar Novedad</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Título:</label>
            <input type="text" name="titulo" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Contenido:</label>
            <textarea name="contenido" class="form-control" required></textarea>
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
