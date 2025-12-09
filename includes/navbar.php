<nav class="navbar navbar-expand-lg navbar-dark bg-dark" role="navigation" aria-label="Navegación principal">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php" aria-label="Ofertópolis - Ir a página principal">Ofertópolis</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Abrir menú de navegación">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto">
        <?php if (!isset($_SESSION['usuario_rol'])): ?>
          <li class="nav-item"><a href="/auth/login.php" class="nav-link" aria-label="Ir a página de inicio de sesión">Iniciar sesión</a></li>
          <li class="nav-item"><a href="/auth/register.php" class="nav-link" aria-label="Ir a página de registro">Registrarse</a></li>
          <li class="nav-item"><a href="/contacto.php" class="nav-link" aria-label="Ir a página de contacto">Contacto</a></li>
        <?php else: ?>
          <?php if ($_SESSION['usuario_rol'] == 'admin'): ?>
            <li class="nav-item"><a href="/admin/admin.php" class="nav-link" aria-label="Ir a panel de administrador">Panel Admin</a></li>
          <?php elseif ($_SESSION['usuario_rol'] == 'duenio'): ?>
            <li class="nav-item"><a href="/duenio/duenio.php" class="nav-link" aria-label="Ir a panel de dueño de local">Panel Dueño</a></li>
          <?php elseif ($_SESSION['usuario_rol'] == 'cliente'): ?>
            <li class="nav-item"><a href="/cliente/cliente.php" class="nav-link" aria-label="Ir a panel de cliente">Panel Cliente</a></li>
          <?php endif; ?>
          <li class="nav-item"><a href="/auth/logout.php" class="nav-link text-danger" aria-label="Cerrar sesión y salir de la cuenta">Cerrar sesión</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

