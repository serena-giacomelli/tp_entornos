<?php
session_start();
include_once("../includes/db.php");

// Verificación de sesión y rol
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'cliente') {
    header("Location: ../auth/login.php");
    exit;
}

$id_cliente = $_SESSION['usuario_id'];

// Obtener categoría del cliente
$result_cliente = $conn->query("SELECT categoria FROM usuarios WHERE id=$id_cliente");
$cliente = $result_cliente->fetch_assoc();
$categoria = $cliente['categoria'];

// --- SOLICITAR PROMOCIÓN ---
if (isset($_POST['solicitar'])) {
    $id_promo = intval($_POST['id_promo']);
    $check = $conn->query("SELECT * FROM uso_promociones WHERE id_cliente=$id_cliente AND id_promo=$id_promo");
    
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO uso_promociones (id_cliente, id_promo, estado) VALUES ($id_cliente, $id_promo, 'enviada')");
        $mensaje = "✅ Solicitud enviada al local correctamente.";
    } else {
        $error = "⚠️ Ya solicitaste esta promoción anteriormente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Promociones Disponibles</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .promo-card {
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .promo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
  }
</style>
</head>
<body class="bg-light">
<div class="container mt-4 mb-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>🎁 Promociones Disponibles</h3>
    <div>
      <a href="cliente.php" class="btn btn-secondary">← Volver</a>
      <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
  </div>

  <?php if(isset($mensaje)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $mensaje ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if(isset($error)): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <?= $error ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Información de categoría -->
  <div class="alert alert-info mb-4">
    <strong>Tu categoría:</strong> 
    <span class="badge bg-warning text-dark"><?= strtoupper($categoria) ?></span>
    <br>
    <small>Solo puedes ver promociones para tu categoría y las inferiores.</small>
  </div>

  <!-- Formulario de Filtros -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">🔍 Filtrar Promociones</h5>
    </div>
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-5">
          <label class="form-label">Buscar por título o descripción:</label>
          <input type="text" 
                 name="buscar" 
                 class="form-control" 
                 placeholder="Ej: descuento, 2x1, buffet..." 
                 value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Rubro del local:</label>
          <select name="rubro" class="form-select">
            <option value="">Todos los rubros</option>
            <?php
            // Obtener rubros únicos
            $rubros = $conn->query("SELECT DISTINCT rubro FROM locales WHERE rubro IS NOT NULL AND rubro != '' ORDER BY rubro");
            while ($r = $rubros->fetch_assoc()):
              $selected = (isset($_GET['rubro']) && $_GET['rubro'] == $r['rubro']) ? 'selected' : '';
            ?>
              <option value="<?= htmlspecialchars($r['rubro']) ?>" <?= $selected ?>>
                <?= htmlspecialchars($r['rubro']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Categoría:</label>
          <select name="categoria" class="form-select">
            <option value="">Todas</option>
            <option value="inicial" <?= (isset($_GET['categoria']) && $_GET['categoria'] == 'inicial') ? 'selected' : '' ?>>Inicial</option>
            <option value="medium" <?= (isset($_GET['categoria']) && $_GET['categoria'] == 'medium') ? 'selected' : '' ?>>Medium</option>
            <option value="premium" <?= (isset($_GET['categoria']) && $_GET['categoria'] == 'premium') ? 'selected' : '' ?>>Premium</option>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <div class="btn-group w-100">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <?php if (!empty($_GET['buscar']) || !empty($_GET['rubro']) || !empty($_GET['categoria'])): ?>
              <a href="promociones.php" class="btn btn-secondary">Limpiar</a>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php
  // Construcción de filtros
  $filtro = "";
  $params_filtro = [];

  if (!empty($_GET['buscar'])) {
      $texto = $conn->real_escape_string($_GET['buscar']);
      $filtro .= " AND (p.titulo LIKE '%$texto%' OR p.descripcion LIKE '%$texto%')";
      $params_filtro[] = "búsqueda: '<strong>$texto</strong>'";
  }

  if (!empty($_GET['rubro'])) {
      $rubro = $conn->real_escape_string($_GET['rubro']);
      $filtro .= " AND l.rubro='$rubro'";
      $params_filtro[] = "rubro: '<strong>$rubro</strong>'";
  }

  if (!empty($_GET['categoria'])) {
      $cat_filtro = $conn->real_escape_string($_GET['categoria']);
      $filtro .= " AND p.categoria_minima='$cat_filtro'";
      $params_filtro[] = "categoría: '<strong>$cat_filtro</strong>'";
  }

  // Mostrar filtros activos
  if (!empty($params_filtro)): ?>
    <div class="alert alert-success" role="alert">
      <strong>✓ Filtros activos:</strong> <?= implode(' | ', $params_filtro) ?>
    </div>
  <?php endif; ?>

  <?php
  // Consulta con filtros y validación de categoría
  $sql = "SELECT p.id, p.titulo, p.descripcion, p.fecha_inicio, p.fecha_fin, 
                 p.categoria_minima, l.nombre AS local, l.rubro
          FROM promociones p 
          JOIN locales l ON p.id_local = l.id
          WHERE p.estado='aprobada' $filtro
            AND (
              '$categoria' = p.categoria_minima
              OR ('$categoria'='medium' AND p.categoria_minima='inicial')
              OR ('$categoria'='premium' AND p.categoria_minima IN ('inicial','medium'))
            )
          ORDER BY p.fecha_inicio DESC";

  $promos = $conn->query($sql);
  ?>

  <!-- Lista de promociones -->
  <div class="row">
    <?php if ($promos && $promos->num_rows > 0): ?>
      <?php while($p = $promos->fetch_assoc()): 
        // Verificar si ya solicitó esta promoción
        $ya_solicitada = $conn->query("SELECT * FROM uso_promociones WHERE id_cliente=$id_cliente AND id_promo={$p['id']}")->num_rows > 0;
      ?>
        <div class="col-md-6 mb-4">
          <div class="card promo-card h-100">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
              <h5 class="mb-0"><?= htmlspecialchars($p['titulo']) ?></h5>
            </div>
            <div class="card-body">
              <p class="mb-2">
                <strong>🏪 Local:</strong> <?= htmlspecialchars($p['local']) ?>
                <?php if (!empty($p['rubro'])): ?>
                  <span class="badge bg-info"><?= htmlspecialchars($p['rubro']) ?></span>
                <?php endif; ?>
              </p>
              <p class="mb-2">
                <strong>📝 Descripción:</strong><br>
                <?= htmlspecialchars($p['descripcion']) ?>
              </p>
              <?php if (!empty($p['fecha_inicio']) && !empty($p['fecha_fin'])): ?>
                <p class="mb-2">
                  <strong>📅 Vigencia:</strong><br>
                  <small class="text-muted">
                    Del <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?> 
                    al <?= date('d/m/Y', strtotime($p['fecha_fin'])) ?>
                  </small>
                </p>
              <?php endif; ?>
              <p class="mb-3">
                <strong>🏆 Categoría mínima:</strong>
                <span class="badge bg-warning text-dark"><?= strtoupper($p['categoria_minima']) ?></span>
              </p>
            </div>
            <div class="card-footer bg-light">
              <?php if ($ya_solicitada): ?>
                <button class="btn btn-secondary w-100" disabled>
                  ✓ Ya solicitada
                </button>
              <?php else: ?>
                <form method="POST" class="m-0">
                  <input type="hidden" name="id_promo" value="<?= $p['id'] ?>">
                  <button type="submit" name="solicitar" class="btn btn-success w-100" onclick="return confirm('¿Deseas solicitar esta promoción?')">
                    🎁 Solicitar Promoción
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="alert alert-warning text-center" role="alert">
          <h5>😕 No se encontraron promociones</h5>
          <p>No hay promociones que coincidan con los filtros seleccionados o tu categoría actual.</p>
          <?php if (!empty($params_filtro)): ?>
            <a href="promociones.php" class="btn btn-primary">Ver todas las promociones disponibles</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Resumen -->
  <div class="alert alert-secondary text-center">
    <strong>Total de promociones encontradas:</strong> <?= $promos ? $promos->num_rows : 0 ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>