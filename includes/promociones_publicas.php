<h3 class="text-primary mb-3">🎁 Promociones destacadas</h3>
<?php
$promos = $conn->query("SELECT p.titulo, p.descripcion, l.nombre AS local
                        FROM promociones p 
                        JOIN locales l ON p.id_local = l.id
                        WHERE p.estado='aprobada'
                        ORDER BY p.id DESC LIMIT 5");

if ($promos && $promos->num_rows > 0):
  while($p = $promos->fetch_assoc()):
?>
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="fw-bold text-dark"><?= htmlspecialchars($p['titulo']) ?></h5>
      <p class="text-muted mb-1"><strong>Local:</strong> <?= htmlspecialchars($p['local']) ?></p>
      <p><?= htmlspecialchars($p['descripcion']) ?></p>
    </div>
  </div>
<?php endwhile; else: ?>
  <p>No hay promociones disponibles por el momento.</p>
<?php endif; ?>
