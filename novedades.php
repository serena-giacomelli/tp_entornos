<?php
session_start();
include_once("includes/db.php");

// Construir query con filtros
$where = ["(fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())"];
$params_filtro = [];

// FILTRO AUTOMÁTICO POR CATEGORÍA DEL USUARIO LOGUEADO
if (isset($_SESSION['usuario_categoria'])) {
    $categoria_usuario = $_SESSION['usuario_categoria'];
    // El usuario solo ve novedades de su categoría o inferiores
    if ($categoria_usuario == 'inicial') {
        $where[] = "categoria_destino = 'inicial'";
    } elseif ($categoria_usuario == 'medium') {
        $where[] = "categoria_destino IN ('inicial', 'medium')";
    } elseif ($categoria_usuario == 'premium') {
        $where[] = "categoria_destino IN ('inicial', 'medium', 'premium')";
    }
}

// Filtro por búsqueda
if (!empty($_GET['buscar'])) {
    $buscar = $conn->real_escape_string($_GET['buscar']);
    $where[] = "(titulo LIKE '%$buscar%' OR contenido LIKE '%$buscar%')";
    $params_filtro[] = "buscar=" . urlencode($_GET['buscar']);
}

$where_clause = implode(" AND ", $where);

// Ordenamiento
$order_by = "fecha_publicacion DESC"; // Por defecto, más recientes primero
if (!empty($_GET['orden'])) {
    switch ($_GET['orden']) {
        case 'fecha_pub_asc':
            $order_by = "fecha_publicacion ASC";
            break;
        case 'fecha_pub_desc':
            $order_by = "fecha_publicacion DESC";
            break;
        case 'fecha_venc_asc':
            $order_by = "CASE WHEN fecha_vencimiento IS NULL THEN 1 ELSE 0 END, fecha_vencimiento ASC";
            break;
        case 'fecha_venc_desc':
            $order_by = "CASE WHEN fecha_vencimiento IS NULL THEN 1 ELSE 0 END, fecha_vencimiento DESC";
            break;
        case 'titulo_asc':
            $order_by = "titulo ASC";
            break;
        case 'titulo_desc':
            $order_by = "titulo DESC";
            break;
        default:
            $order_by = "fecha_publicacion DESC";
            break;
    }
}

// Consulta principal
$sql = "SELECT * FROM novedades 
        WHERE $where_clause
        ORDER BY $order_by";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Todas las Novedades - OFERTÓPOLIS</title>
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
        Novedades
      </h1>
      <p class="text-muted" style="font-size: 1.1rem; max-width: 700px; margin: 0 auto;">
        Mantente informado con las últimas noticias y novedades del shopping
      </p>
    </div>

    <!-- Formulario de Filtros -->
    <form method="GET" class="mb-4">
      <div class="row g-2 mb-2">
        <div class="col-md-8">
          <input type="text" 
                 name="buscar" 
                 class="form-control" 
                 placeholder="Buscar novedad..." 
                 value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <select name="orden" class="form-select">
            <option value="">Ordenar por...</option>
            <option value="fecha_pub_desc" <?= (isset($_GET['orden']) && $_GET['orden'] == 'fecha_pub_desc') ? 'selected' : '' ?>>Más recientes</option>
            <option value="fecha_pub_asc" <?= (isset($_GET['orden']) && $_GET['orden'] == 'fecha_pub_asc') ? 'selected' : '' ?>>Más antiguos</option>
            <option value="fecha_venc_asc" <?= (isset($_GET['orden']) && $_GET['orden'] == 'fecha_venc_asc') ? 'selected' : '' ?>>Vence primero</option>
            <option value="fecha_venc_desc" <?= (isset($_GET['orden']) && $_GET['orden'] == 'fecha_venc_desc') ? 'selected' : '' ?>>Vence después</option>
            <option value="titulo_asc" <?= (isset($_GET['orden']) && $_GET['orden'] == 'titulo_asc') ? 'selected' : '' ?>>Título A-Z</option>
            <option value="titulo_desc" <?= (isset($_GET['orden']) && $_GET['orden'] == 'titulo_desc') ? 'selected' : '' ?>>Título Z-A</option>
          </select>
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <?php if (!empty($params_filtro)): ?>
        <div class="col-md-12">
          <a href="novedades.php" class="btn btn-link">Limpiar filtros</a>
        </div>
        <?php endif; ?>
      </div>
    </form>

    <!-- Grilla de Novedades -->
    <div class="row g-4">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while($novedad = $result->fetch_assoc()): ?>
          <div class="col-md-4 col-sm-6">
            <div class="card card-custom h-100">
              <div class="card-body">
                <h5 class="novedad-title"><?= htmlspecialchars($novedad['titulo']) ?></h5>
                
                <p class="text-muted mb-3">
                  <small>
                    <strong>Publicado:</strong> <?= date('d/m/Y', strtotime($novedad['fecha_publicacion'])) ?>
                  </small>
                  <?php if (!empty($novedad['fecha_vencimiento'])): ?>
                    <br>
                    <small class="text-danger">
                      <strong>Vence:</strong> <?= date('d/m/Y', strtotime($novedad['fecha_vencimiento'])) ?>
                    </small>
                  <?php endif; ?>
                </p>

                <div class="mb-2">
                  <span class="badge badge-<?= strtolower($novedad['categoria_destino']) ?>" 
                        style="background-color: <?= $novedad['categoria_destino'] == 'inicial' ? '#90CAF9' : ($novedad['categoria_destino'] == 'medium' ? '#FFB74D' : '#9575CD') ?>; color: var(--dark);">
                    <?= ucfirst($novedad['categoria_destino']) ?>
                  </span>
                </div>
                
                <p class="card-text"><?= nl2br(htmlspecialchars($novedad['contenido'])) ?></p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-warning text-center">
            <strong>No se encontraron novedades</strong> que coincidan con los filtros seleccionados.
            <?php if (!empty($params_filtro)): ?>
              <br><a href="novedades.php" class="alert-link">Ver todas las novedades</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include("includes/footer.php"); ?>

</body>
</html>
