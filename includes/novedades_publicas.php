<div class="d-flex justify-content-between align-items-center mb-2">
  <h3 class="section-title">
    Últimas Novedades
  </h3>
</div>
<?php
// Si el usuario está logueado como cliente, filtrar por su categoría
if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'cliente' && isset($_SESSION['usuario_categoria'])) {
    $categoria = strtolower($_SESSION['usuario_categoria']);
    
    // Construir el filtro según la categoría del cliente
    if ($categoria == 'inicial') {
        $filtro = "categoria_destino='inicial'";
    } elseif ($categoria == 'medium') {
        $filtro = "categoria_destino IN ('inicial','medium')";
    } else { // premium
        $filtro = "categoria_destino IN ('inicial','medium','premium')";
    }
    
    $novedades = $conn->query("SELECT titulo, contenido, categoria_destino, fecha_publicacion, fecha_vencimiento 
                               FROM novedades 
                               WHERE $filtro
                               AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
                               ORDER BY fecha_publicacion DESC LIMIT 5");
} else {
    // Para usuarios no logueados o no clientes, mostrar todas las vigentes
    $novedades = $conn->query("SELECT titulo, contenido, categoria_destino, fecha_publicacion, fecha_vencimiento 
                               FROM novedades 
                               WHERE (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
                               ORDER BY fecha_publicacion DESC LIMIT 5");
}

if ($novedades && $novedades->num_rows > 0):
  while($n = $novedades->fetch_assoc()):
    // Determinar el badge de categoría
    $badge_color = match($n['categoria_destino']) {
        'inicial' => 'secondary',
        'medium' => 'primary',
        'premium' => 'warning',
        default => 'secondary'
    };
?>
  <div class="card card-custom mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <h6 class="novedad-title">
          <?= htmlspecialchars($n['titulo']) ?>
        </h6>
        <span class="badge badge-<?= strtolower($n['categoria_destino']) ?> card-badge">
          <?= ucfirst($n['categoria_destino']) ?>
        </span>
      </div>
      <small class="novedad-date">
        📅 <?= date("d/m/Y", strtotime($n['fecha_publicacion'])) ?>
        <?php if (!empty($n['fecha_vencimiento'])): ?>
          <span class="text-danger"> • ⏱️ Vence: <?= date("d/m/Y", strtotime($n['fecha_vencimiento'])) ?></span>
        <?php endif; ?>
      </small>
      <p class="novedad-content"><?= htmlspecialchars($n['contenido']) ?></p>
    </div>
  </div>
<?php endwhile; else: ?>
  <p>No hay novedades publicadas aún.</p>
<?php endif; ?>
