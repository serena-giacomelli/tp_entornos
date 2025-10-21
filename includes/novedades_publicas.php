<h3 class="text-info mb-3">📰 Últimas novedades</h3>
<?php
$novedades = $conn->query("SELECT titulo, contenido, fecha_publicacion FROM novedades ORDER BY fecha_publicacion DESC LIMIT 5");

if ($novedades && $novedades->num_rows > 0):
  while($n = $novedades->fetch_assoc()):
?>
  <div class="card mb-3 border-info">
    <div class="card-body">
      <h6 class="fw-bold"><?= htmlspecialchars($n['titulo']) ?></h6>
      <small class="text-muted"><?= date("d/m/Y", strtotime($n['fecha_publicacion'])) ?></small>
      <p><?= htmlspecialchars($n['contenido']) ?></p>
    </div>
  </div>
<?php endwhile; else: ?>
  <p>No hay novedades publicadas aún.</p>
<?php endif; ?>
