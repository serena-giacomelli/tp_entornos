<?php
session_start();
include_once("../includes/db.php");

// Verificación de sesión y rol
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'cliente') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_SESSION['usuario_id'];

// Obtener datos del cliente
$result = $conn->query("SELECT * FROM usuarios WHERE id=$id");
$cliente = $result->fetch_assoc();

// Contar promociones solicitadas
$count_promos = $conn->query("SELECT COUNT(*) as total FROM uso_promociones WHERE id_cliente=$id")->fetch_assoc();

// --- ACTUALIZAR PERFIL ---
if (isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = !empty($_POST['password']) ? md5($_POST['password']) : $cliente['password'];

    $sql = "UPDATE usuarios SET nombre='$nombre', email='$email', password='$password' WHERE id=$id";
    if ($conn->query($sql)) {
        $_SESSION['usuario_nombre'] = $nombre;
        $mensaje = "Perfil actualizado correctamente.";
    } else {
        $error = "Error al actualizar perfil: " . $conn->error;
    }

    // Refrescar datos actualizados
    $result = $conn->query("SELECT * FROM usuarios WHERE id=$id");
    $cliente = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del Cliente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>👤 Panel del Cliente</h3>
    <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
  </div>

  <?php if(isset($mensaje)): ?><div class="alert alert-success"><?= $mensaje ?></div><?php endif; ?>
  <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

  <!-- INFORMACIÓN DEL CLIENTE -->
  <div class="card mb-4">
    <div class="card-header bg-info text-white">
      <h5 class="mb-0">👤 Información de Usuario</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <p><strong>Nombre:</strong> <?= htmlspecialchars($cliente['nombre']) ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($cliente['email']) ?></p>
        </div>
        <div class="col-md-6">
          <p><strong>Categoría:</strong> <span class="badge bg-warning text-dark"><?= htmlspecialchars($cliente['categoria'] ?? 'Inicial') ?></span></p>
          <p><strong>Promociones solicitadas:</strong> <?= $count_promos['total'] ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- PERFIL -->
  <div class="card">
    <div class="card-header bg-dark text-white">Mi Perfil</div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label>Nombre:</label>
          <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Email:</label>
          <input type="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Contraseña (dejar en blanco si no desea cambiarla):</label>
          <input type="password" name="password" class="form-control">
        </div>
        <button type="submit" name="guardar" class="btn btn-primary">Guardar cambios</button>
      </form>
    </div>
  </div>

<!-- PROMOCIONES -->
<div class="card mt-4">
  <div class="card-header bg-secondary text-white">🎁 Promociones Disponibles</div>
  <div class="card-body">
    <?php
    include("config/db.php");
    $categoria = $_SESSION['usuario_categoria'];
    $idCliente = $_SESSION['usuario_id'];

    if (isset($_POST['solicitar'])) {
        $id_promo = intval($_POST['id_promo']);
        $check = $conn->query("SELECT * FROM uso_promociones WHERE id_cliente=$idCliente AND id_promo=$id_promo");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO uso_promociones (id_cliente, id_promo) VALUES ($idCliente, $id_promo)");
            echo "<div class='alert alert-success'>Solicitud enviada al local.</div>";
        } else {
            echo "<div class='alert alert-warning'>Ya solicitaste esta promoción.</div>";
        }
    }

    $sql = "SELECT p.id AS id_promo, p.descripcion, l.nombre 
            FROM promociones p 
            JOIN locales l ON p.id = l.id
            WHERE p.estado='aprobada'
              AND (
                '$categoria' = p.categoria
                OR ('$categoria'='Medium' AND p.categoria='Inicial')
                OR ('$categoria'='Premium' AND p.categoria IN ('Inicial','Medium'))
              )";

    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        while ($promo = $res->fetch_assoc()) {
            echo "
            <form method='POST' class='border rounded p-3 mb-3 bg-white'>
              <h5>{$promo['descripcion']}</h5>
              <p><strong>Local:</strong> {$promo['nombre']}</p>
              <button type='submit' name='solicitar' value='1' class='btn btn-primary btn-sm'>Solicitar Promoción</button>
              <input type='hidden' name='id_promo' value='{$promo['id_promo']}'>
            </form>";
        }
    } else {
        echo "<p>No hay promociones disponibles por el momento.</p>";
    }
    ?>
  </div>
</div>

  <!-- NOVEDADES -->
  <div class="card mt-4 mb-4">
    <div class="card-header bg-info text-white">📰 Novedades Recientes</div>
    <div class="card-body">
      <?php
      $novedades = $conn->query("SELECT titulo, contenido, fecha_publicacion FROM novedades ORDER BY fecha_publicacion DESC LIMIT 5");
      if ($novedades && $novedades->num_rows > 0) {
          while ($n = $novedades->fetch_assoc()) {
              echo "
                <div class='mb-3'>
                  <h6 class='text-dark fw-bold'>{$n['titulo']}</h6>
                  <small class='text-muted'>".date('d/m/Y', strtotime($n['fecha_publicacion']))."</small>
                  <p>{$n['contenido']}</p>
                  <hr>
                </div>
              ";
          }
      } else {
          echo "<p>No hay novedades disponibles.</p>";
      }
      ?>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
