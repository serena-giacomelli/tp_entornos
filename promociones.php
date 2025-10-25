<?php
session_start();
include_once("includes/db.php");

// --- SOLICITAR PROMOCIÓN ---
if (isset($_POST['solicitar']) && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'cliente') {
    $id_promo = intval($_POST['id_promo']);
    $id_cliente = $_SESSION['usuario_id'];
    
    // Verificar si ya solicitó esta promoción
    $check = $conn->query("SELECT * FROM uso_promociones WHERE id_cliente=$id_cliente AND id_promo=$id_promo");
    
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO uso_promociones (id_cliente, id_promo, estado) VALUES ($id_cliente, $id_promo, 'enviada')");
        $mensaje = "✅ Solicitud enviada al local correctamente.";
    } else {
        $error = "⚠️ Ya solicitaste esta promoción anteriormente.";
    }
}

// Construir query con filtros
$where = ["p.estado = 'aprobada'", "p.fecha_fin >= CURDATE()"];
$params_filtro = [];

// FILTRO AUTOMÁTICO POR CATEGORÍA DEL USUARIO LOGUEADO
if (isset($_SESSION['usuario_categoria'])) {
    $categoria_usuario = $_SESSION['usuario_categoria'];
    // El usuario solo ve promociones de su categoría o inferiores
    if ($categoria_usuario == 'inicial') {
        $where[] = "p.categoria_minima = 'inicial'";
    } elseif ($categoria_usuario == 'medium') {
        $where[] = "p.categoria_minima IN ('inicial', 'medium')";
    } elseif ($categoria_usuario == 'premium') {
        $where[] = "p.categoria_minima IN ('inicial', 'medium', 'premium')";
    }
}

// Filtro por búsqueda
if (!empty($_GET['buscar'])) {
    $buscar = $conn->real_escape_string($_GET['buscar']);
    $where[] = "(p.titulo LIKE '%$buscar%' OR p.descripcion LIKE '%$buscar%' OR l.nombre LIKE '%$buscar%')";
    $params_filtro[] = "buscar=" . urlencode($_GET['buscar']);
}

// Filtro por rubro
if (!empty($_GET['rubro'])) {
    $rubro = $conn->real_escape_string($_GET['rubro']);
    $where[] = "l.rubro = '$rubro'";
    $params_filtro[] = "rubro=" . urlencode($_GET['rubro']);
}

// Filtro por local
if (!empty($_GET['local'])) {
    $local_id = intval($_GET['local']);
    $where[] = "l.id = $local_id";
    $params_filtro[] = "local=" . urlencode($_GET['local']);
}

// Filtro por código de local
if (!empty($_GET['codigo_local'])) {
    $codigo_local = intval($_GET['codigo_local']);
    $where[] = "l.id = $codigo_local";
    $params_filtro[] = "codigo_local=" . urlencode($_GET['codigo_local']);
}

$where_clause = implode(" AND ", $where);

// Ordenamiento
$order_by = "p.id DESC"; // Por defecto, más recientes primero
if (!empty($_GET['orden'])) {
    switch ($_GET['orden']) {
        case 'fecha_fin_asc':
            $order_by = "p.fecha_fin ASC";
            break;
        case 'fecha_fin_desc':
            $order_by = "p.fecha_fin DESC";
            break;
        case 'fecha_inicio_asc':
            $order_by = "p.fecha_inicio ASC";
            break;
        case 'fecha_inicio_desc':
            $order_by = "p.fecha_inicio DESC";
            break;
        case 'recientes':
            $order_by = "p.id DESC";
            break;
        case 'antiguos':
            $order_by = "p.id ASC";
            break;
    }
}

// Consulta principal
$sql = "SELECT p.*, l.id as local_id, l.nombre as local, l.rubro, l.ubicacion 
        FROM promociones p 
        INNER JOIN locales l ON p.id_local = l.id 
        WHERE $where_clause
        ORDER BY $order_by";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Promociones - OFERTÓPOLIS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/estilos.css" rel="stylesheet">
  <link href="css/header.css" rel="stylesheet">
  <link href="css/footer.css" rel="stylesheet">
  <link href="css/cards.css" rel="stylesheet">
</head>
<body>

<?php include("includes/header.php"); ?>

<main id="main-content" class="main-content">
  <div class="container mt-5 mb-5">
    <!-- Título y Slogan Centrados -->
    <div class="text-center mb-5">
      <h1 class="section-title mb-3" style="font-size: 2.5rem; font-weight: 700; color: var(--primary-color);">
        Promociones
      </h1>
      <p class="text-muted" style="font-size: 1.1rem; max-width: 700px; margin: 0 auto;">
        Descubre las mejores ofertas y promociones exclusivas de todos los locales del shopping
      </p>
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

    <!-- Formulario de Filtros -->
    <form method="GET" class="mb-4">
      <div class="row g-2 mb-2">
        <div class="col-md-3">
          <input type="text" 
                 name="buscar" 
                 class="form-control" 
                 placeholder="Buscar promoción..." 
                 value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <input type="number" 
                 name="codigo_local" 
                 class="form-control" 
                 placeholder="Código de local..." 
                 value="<?= htmlspecialchars($_GET['codigo_local'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <select name="rubro" class="form-select">
            <option value="">Todos los rubros</option>
            <?php
            // Obtener TODOS los rubros de la tabla locales
            $rubros = $conn->query("SELECT DISTINCT rubro FROM locales WHERE rubro IS NOT NULL AND rubro != '' ORDER BY rubro");
            while ($r = $rubros->fetch_assoc()) {
                $selected = (isset($_GET['rubro']) && $_GET['rubro'] == $r['rubro']) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($r['rubro']) . "' $selected>" . htmlspecialchars($r['rubro']) . "</option>";
            }
            ?>
          </select>
        </div>
        <div class="col-md-2">
          <select name="orden" class="form-select">
            <option value="">Ordenar por...</option>
            <option value="recientes" <?= (isset($_GET['orden']) && $_GET['orden'] == 'recientes') ? 'selected' : '' ?>>Más recientes</option>
            <option value="antiguos" <?= (isset($_GET['orden']) && $_GET['orden'] == 'antiguos') ? 'selected' : '' ?>>Más antiguos</option>
            <option value="fecha_fin_asc" <?= (isset($_GET['orden']) && $_GET['orden'] == 'fecha_fin_asc') ? 'selected' : '' ?>>Vence primero</option>
            <option value="fecha_fin_desc" <?= (isset($_GET['orden']) && $_GET['orden'] == 'fecha_fin_desc') ? 'selected' : '' ?>>Vence después</option>
          </select>
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <?php if (!empty($params_filtro)): ?>
        <div class="col-md-12">
          <a href="promociones.php" class="btn btn-link">Limpiar filtros</a>
        </div>
        <?php endif; ?>
      </div>
    </form>

    <!-- Grilla de Promociones -->
    <div class="row g-4">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while($promo = $result->fetch_assoc()): 
          // Verificar si el cliente ya solicitó esta promoción
          $ya_solicitada = false;
          if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'cliente') {
              $id_cliente = $_SESSION['usuario_id'];
              $check_solicitud = $conn->query("SELECT * FROM uso_promociones WHERE id_cliente=$id_cliente AND id_promo={$promo['id']}");
              $ya_solicitada = ($check_solicitud->num_rows > 0);
          }
        ?>
          <div class="col-md-4 col-sm-6">
            <div class="card card-custom h-100">
              <div class="card-body">
                <h5 class="card-title-promo"><?= htmlspecialchars($promo['titulo']) ?></h5>
                
                <div class="mb-2">
                  <strong class="card-info-label">Local:</strong> 
                  <span class="badge bg-secondary me-1">#<?= $promo['local_id'] ?></span>
                  <span class="card-info-value"><?= htmlspecialchars($promo['local']) ?></span>
                  <?php if (!empty($promo['rubro'])): ?>
                    <span class="badge bg-info ms-1"><?= htmlspecialchars($promo['rubro']) ?></span>
                  <?php endif; ?>
                </div>
                
                <?php if (!empty($promo['ubicacion'])): ?>
                  <div class="mb-2">
                    <strong class="card-info-label">Ubicación:</strong> 
                    <small class="text-muted"><?= htmlspecialchars($promo['ubicacion']) ?></small>
                  </div>
                <?php endif; ?>
                
                <p class="card-text mb-3"><?= htmlspecialchars($promo['descripcion']) ?></p>
                
                <div class="mb-2">
                  <strong>Categoría mínima:</strong>
                  <span class="badge bg-warning text-dark"><?= strtoupper($promo['categoria_minima']) ?></span>
                </div>
                
                <?php if (!empty($promo['fecha_inicio']) && !empty($promo['fecha_fin'])): ?>
                  <div class="mb-3">
                    <small class="text-muted">
                      Válido del <?= date('d/m/Y', strtotime($promo['fecha_inicio'])) ?> 
                      al <?= date('d/m/Y', strtotime($promo['fecha_fin'])) ?>
                    </small>
                  </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'cliente'): ?>
                  <?php if ($ya_solicitada): ?>
                    <button class="btn btn-secondary btn-sm w-100" disabled>
                      ✓ Ya solicitada
                    </button>
                  <?php else: ?>
                    <form method="POST" class="m-0">
                      <input type="hidden" name="id_promo" value="<?= $promo['id'] ?>">
                      <button type="submit" name="solicitar" class="btn btn-primary btn-sm w-100" onclick="return confirm('¿Deseas solicitar esta promoción?')">
                        Solicitar promoción
                      </button>
                    </form>
                  <?php endif; ?>
                <?php elseif (!isset($_SESSION['usuario_rol'])): ?>
                  <a href="auth/login.php" class="btn btn-outline-primary btn-sm w-100">Inicia sesión para solicitar</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-warning text-center">
            <strong>No se encontraron promociones</strong> que coincidan con los filtros seleccionados.
            <?php if (!empty($params_filtro)): ?>
              <br><a href="promociones.php" class="alert-link">Ver todas las promociones</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include("includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
