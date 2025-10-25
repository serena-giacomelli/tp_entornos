<?php
session_start();
include_once("includes/db.php");

// Consultar todos los locales con información del dueño
$sql = "SELECT l.*, u.nombre AS duenio_nombre, u.email AS duenio_email 
        FROM locales l 
        LEFT JOIN usuarios u ON l.id_duenio = u.id 
        ORDER BY l.nombre ASC";
$locales = $conn->query($sql);

// Obtener rubros únicos para el filtro
$rubros_sql = "SELECT DISTINCT rubro FROM locales WHERE rubro IS NOT NULL AND rubro != '' ORDER BY rubro ASC";
$rubros_result = $conn->query($rubros_sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Locales del Shopping - Ofertópolis</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/estilos.css" rel="stylesheet">
<link href="css/header.css" rel="stylesheet">
<link href="css/footer.css" rel="stylesheet">
<link href="css/cards.css" rel="stylesheet">
<style>
  .local-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    border: none;
    border-radius: 12px;
    overflow: hidden;
  }
  
  .local-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(113, 0, 20, 0.2) !important;
  }
  
  .local-card-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 1.5rem;
    border: none;
  }
  
  .local-card-body {
    padding: 1.5rem;
  }
  
  .local-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
  }
  
  .local-badge {
    background-color: var(--secondary-color);
    color: var(--dark);
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
  }
  
  .local-info-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 0.75rem;
  }
  
  .filter-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
  }
  
  .page-title {
    color: var(--primary-color);
    font-weight: 700;
    margin-bottom: 0.5rem;
  }
  
  .page-subtitle {
    color: var(--text-muted);
    font-size: 1rem;
  }
</style>
</head>
<body>

<?php include("includes/header.php"); ?>

<main id="main-content" class="main-content">
  <div class="container mt-4 mb-5">
    <div class="text-center mb-4">
      <h1 class="page-title">Locales del Shopping</h1>
      <p class="page-subtitle">Descubre todos los comercios que forman parte de Ofertópolis</p>
    </div>

    <!-- Filtros -->
    <div class="filter-section">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label for="buscar" class="form-label fw-bold">Buscar por nombre:</label>
          <input type="text" 
                 class="form-control" 
                 id="buscar" 
                 name="buscar" 
                 placeholder="Nombre del local..." 
                 value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
        </div>
        
        <div class="col-md-4">
          <label for="rubro" class="form-label fw-bold">Filtrar por rubro:</label>
          <select class="form-select" id="rubro" name="rubro">
            <option value="">Todos los rubros</option>
            <?php while($rubro = $rubros_result->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($rubro['rubro']) ?>" 
                      <?= (isset($_GET['rubro']) && $_GET['rubro'] == $rubro['rubro']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($rubro['rubro']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <div class="col-md-4">
          <button type="submit" class="btn btn-primary me-2">Filtrar</button>
          <a href="locales.php" class="btn btn-secondary">Limpiar</a>
        </div>
      </form>
    </div>

    <?php
    // Aplicar filtros
    $where = [];
    $params = [];
    
    if (!empty($_GET['buscar'])) {
      $where[] = "l.nombre LIKE ?";
      $params[] = "%" . $_GET['buscar'] . "%";
    }
    
    if (!empty($_GET['rubro'])) {
      $where[] = "l.rubro = ?";
      $params[] = $_GET['rubro'];
    }
    
    // Construir query con filtros
    $sql_filtrado = "SELECT l.*, u.nombre AS duenio_nombre, u.email AS duenio_email 
                     FROM locales l 
                     LEFT JOIN usuarios u ON l.id_duenio = u.id";
    
    if (count($where) > 0) {
      $sql_filtrado .= " WHERE " . implode(" AND ", $where);
    }
    
    $sql_filtrado .= " ORDER BY l.nombre ASC";
    
    // Ejecutar query
    if (count($params) > 0) {
      $stmt = $conn->prepare($sql_filtrado);
      $types = str_repeat('s', count($params));
      $stmt->bind_param($types, ...$params);
      $stmt->execute();
      $locales_filtrados = $stmt->get_result();
    } else {
      $locales_filtrados = $conn->query($sql_filtrado);
    }
    ?>

    <!-- Grilla de locales -->
    <div class="row g-4">
      <?php if ($locales_filtrados->num_rows > 0): ?>
        <?php while($local = $locales_filtrados->fetch_assoc()): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card local-card shadow-sm">
              <div class="local-card-header">
                <div class="d-flex justify-content-between align-items-start">
                  <h5 class="local-name"><?= htmlspecialchars($local['nombre']) ?></h5>
                  <span class="badge bg-light text-dark">#<?= $local['id'] ?></span>
                </div>
              </div>
              
              <div class="local-card-body">
                <?php if (!empty($local['rubro'])): ?>
                  <div class="mb-3">
                    <span class="local-badge"><?= htmlspecialchars($local['rubro']) ?></span>
                  </div>
                <?php endif; ?>
                
                <?php if (!empty($local['ubicacion'])): ?>
                  <div class="local-info-item">
                    <span><?= htmlspecialchars($local['ubicacion']) ?></span>
                  </div>
                <?php endif; ?>
                
                <?php if (!empty($local['duenio_nombre'])): ?>
                  <div class="local-info-item">
                    <span><strong>Propietario:</strong> <?= htmlspecialchars($local['duenio_nombre']) ?></span>
                  </div>
                <?php endif; ?>
                
                <?php if (!empty($local['duenio_email'])): ?>
                  <div class="local-info-item">
                    <span><a href="mailto:<?= htmlspecialchars($local['duenio_email']) ?>" class="text-decoration-none">
                      <?= htmlspecialchars($local['duenio_email']) ?>
                    </a></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-info text-center">
            <h5>No se encontraron locales</h5>
            <p class="mb-0">Intenta ajustar los filtros de búsqueda.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php
    // Mostrar total de resultados
    $total_locales = $locales_filtrados->num_rows;
    ?>
    <div class="text-center mt-4">
      <p class="text-muted">
        Mostrando <strong><?= $total_locales ?></strong> 
        <?= $total_locales == 1 ? 'local' : 'locales' ?>
      </p>
    </div>
  </div>
</main>

<?php include("includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
