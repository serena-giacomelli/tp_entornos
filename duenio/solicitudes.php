<?php
session_start();
include_once("../includes/db.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'duenio') {
    header("Location: ../auth/login.php");
    exit;
}

$idduenio = $_SESSION['usuario_id'];

// --- Actualizar estado ---
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $accion = $_GET['accion'];
    $id = intval($_GET['id']);
    if (in_array($accion, ['aceptada', 'rechazada'])) {
        $conn->query("UPDATE uso_promociones SET estado='$accion' WHERE id=$id");

        if ($accion == 'aceptada') {
            // Obtener cliente
            $cliente = $conn->query("SELECT id_cliente FROM uso_promociones WHERE id=$id")->fetch_assoc()['id_cliente'];

            // Contar promociones aceptadas
            $count = $conn->query("SELECT COUNT(*) AS total FROM uso_promociones WHERE id_cliente=$cliente AND estado='aceptada'")
                        ->fetch_assoc()['total'];

            // Actualizar categoría según cantidad
            if ($count >= 15) $categoria = 'Premium';
            elseif ($count >= 5) $categoria = 'Medium';
            else $categoria = 'Inicial';

            $conn->query("UPDATE usuarios SET categoria='$categoria' WHERE id=$cliente");
        }

    }
}

// --- Listar solicitudes ---
$sql = "SELECT u.id, c.nombre AS cliente, p.descripcion, l.nombre, u.estado 
        FROM uso_promociones u
        JOIN usuarios c ON u.id_cliente = c.id
        JOIN promociones p ON u.id_promo = p.id
        JOIN locales l ON p.id = l.id
        WHERE l.id_duenio = $idduenio
        ORDER BY u.id DESC";

$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Solicitudes de Promociones</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3 class="mb-3">📩 Solicitudes Recibidas</h3>
  <a href="duenio.php" class="btn btn-secondary mb-3">Volver al panel</a>

  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>Cliente</th>
        <th>Promoción</th>
        <th>Local</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $res->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['cliente']) ?></td>
        <td><?= htmlspecialchars($row['descripcion']) ?></td>
        <td><?= htmlspecialchars($row['nombre']) ?></td>
        <td><?= ucfirst($row['estado']) ?></td>
        <td>
          <?php if ($row['estado'] == 'enviada'): ?>
            <a href="?accion=aceptada&id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Aceptar</a>
            <a href="?accion=rechazada&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Rechazar</a>
          <?php else: ?>
            <span class="text-muted">Finalizada</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
