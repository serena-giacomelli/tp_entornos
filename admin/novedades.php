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
    $categoria_destino = $_POST['categoria_destino'];
    $dias_vigencia = intval($_POST['dias_vigencia']);
    $fecha = date("Y-m-d");
    
    // Calcular fecha de vencimiento según días de vigencia
    $fecha_vencimiento = date("Y-m-d", strtotime("+$dias_vigencia days"));
    
    $conn->query("INSERT INTO novedades (titulo, contenido, categoria_destino, fecha_publicacion, fecha_vencimiento) 
                  VALUES ('$titulo','$contenido','$categoria_destino','$fecha','$fecha_vencimiento')");
}

// --- ELIMINAR ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM novedades WHERE id=$id");
}

// --- FILTROS Y ORDENAMIENTO ---
$where_conditions = [];
$params_url = [];

// Filtro por categoría
if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $categoria_filter = $conn->real_escape_string($_GET['categoria']);
    $where_conditions[] = "categoria_destino='$categoria_filter'";
    $params_url[] = "categoria=$categoria_filter";
}

// Filtro por estado (vigente/vencida)
if (isset($_GET['estado']) && !empty($_GET['estado'])) {
    $estado_filter = $_GET['estado'];
    if ($estado_filter == 'vigente') {
        $where_conditions[] = "(fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())";
    } elseif ($estado_filter == 'vencida') {
        $where_conditions[] = "fecha_vencimiento < CURDATE()";
    }
    $params_url[] = "estado=$estado_filter";
}

// Búsqueda por título o contenido
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $buscar = $conn->real_escape_string($_GET['buscar']);
    $where_conditions[] = "(titulo LIKE '%$buscar%' OR contenido LIKE '%$buscar%')";
    $params_url[] = "buscar=$buscar";
}

// Construir WHERE clause
$where_sql = "";
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// Ordenamiento
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_desc';
$order_sql = "ORDER BY ";
switch ($orden) {
    case 'id_asc':
        $order_sql .= "id ASC";
        break;
    case 'id_desc':
        $order_sql .= "id DESC";
        break;
    case 'titulo_asc':
        $order_sql .= "titulo ASC";
        break;
    case 'titulo_desc':
        $order_sql .= "titulo DESC";
        break;
    case 'fecha_asc':
        $order_sql .= "fecha_publicacion ASC";
        break;
    case 'fecha_desc':
        $order_sql .= "fecha_publicacion DESC";
        break;
    case 'vencimiento_asc':
        $order_sql .= "fecha_vencimiento ASC";
        break;
    case 'vencimiento_desc':
        $order_sql .= "fecha_vencimiento DESC";
        break;
    default:
        $order_sql .= "fecha_publicacion DESC";
}
$params_url[] = "orden=$orden";

// Construir URL para mantener filtros
$params_url_string = implode("&", $params_url);

// Consultar novedades con filtros
$novedades = $conn->query("SELECT * FROM novedades $where_sql $order_sql");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Novedades - Panel Admin</title>
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
    <h3 style="color: var(--primary-color); font-weight: 700;">Gestión de Novedades</h3>
    <div>
      <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalAgregar">Nueva Novedad</button>
      <a href="admin.php" class="btn btn-secondary">Volver al Panel</a>
    </div>
  </div>

  <!-- Filtros y Ordenamiento -->
  <div class="card shadow-sm mb-4">
    <div class="card-header" style="background-color: var(--light);">
      <h5 class="mb-0">Filtros y Ordenamiento</h5>
    </div>
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-bold">Buscar:</label>
          <input type="text" name="buscar" class="form-control" 
                 placeholder="Título o contenido..." 
                 value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-bold">Categoría:</label>
          <select name="categoria" class="form-select">
            <option value="">Todas</option>
            <option value="inicial" <?= (isset($_GET['categoria']) && $_GET['categoria'] == 'inicial') ? 'selected' : '' ?>>Inicial</option>
            <option value="medium" <?= (isset($_GET['categoria']) && $_GET['categoria'] == 'medium') ? 'selected' : '' ?>>Medium</option>
            <option value="premium" <?= (isset($_GET['categoria']) && $_GET['categoria'] == 'premium') ? 'selected' : '' ?>>Premium</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-bold">Estado:</label>
          <select name="estado" class="form-select">
            <option value="">Todos</option>
            <option value="vigente" <?= (isset($_GET['estado']) && $_GET['estado'] == 'vigente') ? 'selected' : '' ?>>Vigente</option>
            <option value="vencida" <?= (isset($_GET['estado']) && $_GET['estado'] == 'vencida') ? 'selected' : '' ?>>Vencida</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold">Ordenar por:</label>
          <select name="orden" class="form-select">
            <option value="fecha_desc" <?= ($orden == 'fecha_desc') ? 'selected' : '' ?>>Fecha publicación (Más reciente)</option>
            <option value="fecha_asc" <?= ($orden == 'fecha_asc') ? 'selected' : '' ?>>Fecha publicación (Más antigua)</option>
            <option value="vencimiento_asc" <?= ($orden == 'vencimiento_asc') ? 'selected' : '' ?>>Vencimiento (Próximo a vencer)</option>
            <option value="vencimiento_desc" <?= ($orden == 'vencimiento_desc') ? 'selected' : '' ?>>Vencimiento (Más lejano)</option>
            <option value="titulo_asc" <?= ($orden == 'titulo_asc') ? 'selected' : '' ?>>Título (A-Z)</option>
            <option value="titulo_desc" <?= ($orden == 'titulo_desc') ? 'selected' : '' ?>>Título (Z-A)</option>
            <option value="id_desc" <?= ($orden == 'id_desc') ? 'selected' : '' ?>>ID (Más reciente)</option>
            <option value="id_asc" <?= ($orden == 'id_asc') ? 'selected' : '' ?>>ID (Más antiguo)</option>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
          <a href="novedades.php" class="btn btn-secondary">Limpiar</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Lista de Novedades</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background-color: var(--light);">
            <tr>
              <th>ID</th>
              <th>Título</th>
              <th>Contenido</th>
              <th>Categoría Destino</th>
              <th>Fecha Publicación</th>
              <th>Vence</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
      <?php while($n = $novedades->fetch_assoc()): 
        $badge_color = match($n['categoria_destino']) {
          'inicial' => 'secondary',
          'medium' => 'primary',
          'premium' => 'warning',
          default => 'secondary'
        };
        
        // Verificar si está vencida
        $esta_vencida = false;
        $fecha_venc = '';
        if (!empty($n['fecha_vencimiento'])) {
          $fecha_venc = date("d/m/Y", strtotime($n['fecha_vencimiento']));
          $esta_vencida = strtotime($n['fecha_vencimiento']) < strtotime('today');
        }
      ?>
        <tr class="<?= $esta_vencida ? 'table-secondary' : '' ?>">
          <td><strong>#<?= $n['id'] ?></strong></td>
          <td><?= htmlspecialchars($n['titulo']) ?></td>
          <td><?= htmlspecialchars(substr($n['contenido'], 0, 80)) ?>...</td>
          <td><span class="badge bg-<?= $badge_color ?>"><?= ucfirst($n['categoria_destino']) ?></span></td>
          <td><?= date("d/m/Y", strtotime($n['fecha_publicacion'])) ?></td>
          <td>
            <?php if (!empty($n['fecha_vencimiento'])): ?>
              <?= $fecha_venc ?>
            <?php else: ?>
              <span class="text-muted">Sin vencimiento</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($esta_vencida): ?>
              <span class="badge bg-danger">Vencida</span>
            <?php else: ?>
              <span class="badge bg-success">Vigente</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="?eliminar=<?= $n['id'] ?>&<?= $params_url_string ?>" 
               class="btn btn-danger btn-sm" 
               onclick="return confirm('¿Eliminar esta novedad?')">
              Eliminar
            </a>
          </td>
        </tr>
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
        <h5 class="modal-title">Agregar Novedad</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Título:</label>
            <input type="text" name="titulo" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Contenido:</label>
            <textarea name="contenido" class="form-control" rows="4" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Categoría Destino:</label>
            <select name="categoria_destino" class="form-select" required>
              <option value="">Seleccionar categoría...</option>
              <option value="inicial">Inicial</option>
              <option value="medium">Medium</option>
              <option value="premium">Premium</option>
            </select>
            <small class="text-muted">
              Los clientes solo verán novedades de su categoría o inferiores.
            </small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Días de Vigencia:</label>
            <input type="number" name="dias_vigencia" class="form-control" min="1" value="30" required>
            <small class="text-muted">
              La novedad expirará automáticamente después de este período. Por defecto: 30 días.
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="agregar" class="btn btn-primary">Agregar Novedad</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
