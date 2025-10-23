<h3 class="text-primary mb-3">🎁 Promociones destacadas</h3>

<!-- Formulario de Filtros -->
<form method="GET" class="mb-4">
  <div class="row g-2">
    <div class="col-md-5">
      <input type="text" 
             name="buscar" 
             class="form-control" 
             placeholder="🔍 Buscar promoción..." 
             value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
    </div>
    <div class="col-md-4">
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
    <div class="col-md-3">
      <div class="btn-group w-100" role="group">
        <button type="submit" class="btn btn-primary">
          <span class="d-none d-md-inline">Filtrar</span>
          <span class="d-md-none">🔍</span>
        </button>
        <?php if (!empty($_GET['buscar']) || !empty($_GET['rubro'])): ?>
          <a href="index.php" class="btn btn-secondary">
            <span class="d-none d-md-inline">Limpiar</span>
            <span class="d-md-none">✖</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</form>

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

// Mostrar filtros activos
if (!empty($params_filtro)): ?>
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    <strong>Filtros activos:</strong> <?= implode(' | ', $params_filtro) ?>
    <a href="index.php" class="btn btn-sm btn-outline-dark ms-2">Limpiar filtros</a>
  </div>
<?php endif; ?>

<?php
// Consulta con filtros
$sql = "SELECT p.titulo, p.descripcion, l.nombre AS local, l.rubro
        FROM promociones p 
        JOIN locales l ON p.id_local = l.id
        WHERE p.estado='aprobada' $filtro
        ORDER BY p.id DESC 
        LIMIT 10";

$promos = $conn->query($sql);

if ($promos && $promos->num_rows > 0):
  while($p = $promos->fetch_assoc()):
?>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <h5 class="fw-bold text-dark"><?= htmlspecialchars($p['titulo']) ?></h5>
      <p class="text-muted mb-1">
        <strong>🏪 Local:</strong> <?= htmlspecialchars($p['local']) ?>
        <?php if (!empty($p['rubro'])): ?>
          <span class="badge bg-info ms-2"><?= htmlspecialchars($p['rubro']) ?></span>
        <?php endif; ?>
      </p>
      <p><?= htmlspecialchars($p['descripcion']) ?></p>
    </div>
  </div>
<?php endwhile; else: ?>
  <div class="alert alert-warning" role="alert">
    <strong>No se encontraron promociones</strong> que coincidan con los filtros seleccionados.
    <?php if (!empty($params_filtro)): ?>
      <br><a href="index.php" class="alert-link">Ver todas las promociones</a>
    <?php endif; ?>
  </div>
<?php endif; ?>
