<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php">Ofertópolis</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto">
        <?php if (!isset($_SESSION['usuario_rol'])): ?>
          <li class="nav-item"><a href="/auth/login.php" class="nav-link">Iniciar sesión</a></li>
          <li class="nav-item"><a href="/auth/register.php" class="nav-link">Registrarse</a></li>
          <li class="nav-item"><a href="/contacto.php" class="nav-link">Contacto</a></li>
        <?php else: ?>
          <?php if ($_SESSION['usuario_rol'] == 'admin'): ?>
            <li class="nav-item"><a href="/admin/admin.php" class="nav-link">Panel Admin</a></li>
          <?php elseif ($_SESSION['usuario_rol'] == 'duenio'): ?>
            <li class="nav-item"><a href="/duenio/duenio.php" class="nav-link">Panel Dueño</a></li>
          <?php elseif ($_SESSION['usuario_rol'] == 'cliente'): ?>
            <li class="nav-item"><a href="/cliente/cliente.php" class="nav-link">Panel Cliente</a></li>
          <?php endif; ?>
          <li class="nav-item"><a href="/auth/logout.php" class="nav-link text-danger">Cerrar sesión</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

