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

// --- CONTAR USOS ACEPTADOS PARA SISTEMA DE PUNTOS ---
$usos_aceptados = $conn->query("SELECT COUNT(*) as total FROM uso_promociones WHERE id_cliente=$id AND estado='aceptada'")->fetch_assoc()['total'];

// Calcular progreso hacia siguiente categoría
$categoria_actual = $cliente['categoria'];
$siguiente_categoria = '';
$usos_necesarios = 0;
$porcentaje_progreso = 0;

if ($categoria_actual == 'inicial') {
    $siguiente_categoria = 'Medium';
    $usos_necesarios = 5;
    $porcentaje_progreso = ($usos_aceptados / $usos_necesarios) * 100;
} elseif ($categoria_actual == 'medium') {
    $siguiente_categoria = 'Premium';
    $usos_necesarios = 10;
    $porcentaje_progreso = ($usos_aceptados / $usos_necesarios) * 100;
} else {
    $siguiente_categoria = 'Máxima';
    $porcentaje_progreso = 100;
}

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
    <div>
      <a href="promociones.php" class="btn btn-primary me-2">🎁 Ver Promociones</a>
      <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
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
          <p>
            <strong>Categoría:</strong> 
            <?php
            $badge_color = match($categoria_actual) {
                'premium' => 'bg-warning text-dark',
                'medium' => 'bg-info',
                default => 'bg-secondary'
            };
            $emoji = match($categoria_actual) {
                'premium' => '👑',
                'medium' => '⭐',
                default => '🎫'
            };
            ?>
            <span class="badge <?= $badge_color ?>">
              <?= $emoji ?> <?= strtoupper($categoria_actual) ?>
            </span>
          </p>
          <p><strong>Promociones solicitadas:</strong> <?= $count_promos['total'] ?></p>
        </div>
      </div>
      
      <!-- SISTEMA DE PUNTOS Y PROGRESO -->
      <hr>
      <div class="mt-3">
        <h6 class="mb-3">
          <strong>🏆 Sistema de Puntos</strong>
        </h6>
        
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="mb-1">
              <strong>Promociones utilizadas:</strong> 
              <span class="badge bg-success"><?= $usos_aceptados ?> aceptadas</span>
            </p>
          </div>
          <?php if ($categoria_actual != 'premium'): ?>
            <div class="col-md-6">
              <p class="mb-1">
                <strong>Para siguiente nivel:</strong>
                <span class="badge bg-primary">
                  <?= max(0, $usos_necesarios - $usos_aceptados) ?> usos restantes
                </span>
              </p>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($categoria_actual != 'premium'): ?>
          <!-- Barra de Progreso -->
          <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <small class="text-muted">
                <strong><?= strtoupper($categoria_actual) ?></strong>
              </small>
              <small class="text-muted">
                <strong><?= $siguiente_categoria ?></strong>
              </small>
            </div>
            
            <div class="progress" style="height: 30px;">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                   role="progressbar" 
                   style="width: <?= min(100, $porcentaje_progreso) ?>%;"
                   aria-valuenow="<?= $usos_aceptados ?>" 
                   aria-valuemin="0" 
                   aria-valuemax="<?= $usos_necesarios ?>">
                <strong><?= $usos_aceptados ?> / <?= $usos_necesarios ?> usos</strong>
              </div>
            </div>
            
            <small class="text-muted">
              <?= round(min(100, $porcentaje_progreso), 1) ?>% completado
            </small>
          </div>
        <?php else: ?>
          <!-- Ya es Premium -->
          <div class="alert alert-warning text-center mb-0">
            <strong>👑 ¡Felicitaciones!</strong><br>
            Has alcanzado la categoría máxima: <strong>PREMIUM</strong><br>
            <small>Disfruta de todos los beneficios exclusivos</small>
          </div>
        <?php endif; ?>

        <!-- Tabla de Niveles -->
        <div class="mt-3">
          <small class="text-muted">
            <strong>📊 Niveles de categoría:</strong>
          </small>
          <table class="table table-sm table-bordered mt-2">
            <thead class="table-light">
              <tr>
                <th>Categoría</th>
                <th>Usos Necesarios</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr class="<?= $categoria_actual == 'inicial' ? 'table-secondary' : '' ?>">
                <td>🎫 Inicial</td>
                <td>0 usos</td>
                <td>
                  <?php if ($categoria_actual == 'inicial'): ?>
                    <span class="badge bg-secondary">Actual</span>
                  <?php else: ?>
                    <span class="badge bg-success">✓ Completado</span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr class="<?= $categoria_actual == 'medium' ? 'table-info' : '' ?>">
                <td>⭐ Medium</td>
                <td>5 usos</td>
                <td>
                  <?php if ($categoria_actual == 'medium'): ?>
                    <span class="badge bg-info">Actual</span>
                  <?php elseif ($usos_aceptados >= 5): ?>
                    <span class="badge bg-success">✓ Completado</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">Bloqueado</span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr class="<?= $categoria_actual == 'premium' ? 'table-warning' : '' ?>">
                <td>👑 Premium</td>
                <td>10 usos</td>
                <td>
                  <?php if ($categoria_actual == 'premium'): ?>
                    <span class="badge bg-warning text-dark">Actual</span>
                  <?php elseif ($usos_aceptados >= 10): ?>
                    <span class="badge bg-success">✓ Completado</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Bloqueado</span>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>
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
    include("../includes/db.php");
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

    $sql = "SELECT p.id AS id_promo, p.titulo, p.descripcion, l.nombre 
            FROM promociones p 
            JOIN locales l ON p.id_local = l.id
            WHERE p.estado='aprobada'
              AND (
                p.categoria_minima = 'inicial'
                OR ('$categoria'='medium' AND p.categoria_minima IN ('inicial','medium'))
                OR ('$categoria'='premium' AND p.categoria_minima IN ('inicial','medium','premium'))
              )";

    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        while ($promo = $res->fetch_assoc()) {
            echo "
            <form method='POST' class='border rounded p-3 mb-3 bg-white'>
              <h5>{$promo['titulo']}</h5>
              <p>{$promo['descripcion']}</p>
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
