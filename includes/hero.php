<section class="bg-primary text-white text-center py-5">
  <div class="container">
    <h1 class="display-5 fw-bold">Bienvenido a Ofertópolis</h1>
    <p class="lead">Descubrí promociones, novedades y beneficios exclusivos.</p>
    <?php if (!isset($_SESSION['usuario_rol'])): ?>
      <a href="/tp_eg/auth/login.php" class="btn btn-light btn-lg me-2">Iniciar sesión</a>
      <a href="/tp_eg/auth/register.php" class="btn btn-outline-light btn-lg">Registrarse</a>
    <?php else: ?>
      <a href="<?php
        if ($_SESSION['usuario_rol'] == 'admin') echo '/tp_eg/admin/admin.php';
        elseif ($_SESSION['usuario_rol'] == 'dueno') echo '/tp_eg/dueno/dueno.php';
        else echo '/tp_eg/cliente/cliente.php';
      ?>" class="btn btn-light btn-lg">Ir a mi panel</a>
    <?php endif; ?>
  </div>
</section>
