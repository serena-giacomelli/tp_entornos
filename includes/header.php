<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Link para accesibilidad: saltar al contenido principal -->
<a href="#main-content" class="skip-link">Saltar al contenido principal</a>

<!-- Header fijo -->
<header class="header-fixed">
  <div class="header-content">
    <!-- Botón menú hamburguesa -->
    <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú de navegación" aria-expanded="false">
      <span>☰</span>
    </button>

    <!-- Logo central -->
    <a href="/tp_eg/index.php" class="logo-central" aria-label="Ir a página principal">
      O F E R T Ó P O L I S
    </a>

    <!-- Perfil de usuario / Botón iniciar sesión -->
    <div class="user-profile">
      <?php if (isset($_SESSION['usuario_id'])): ?>
        <!-- Usuario logueado -->
        <button class="profile-button" id="profileButton" aria-label="Abrir menú de perfil" aria-expanded="false">
          <div class="profile-info">
            <span class="profile-name"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
            <span class="profile-role">
              <?php 
                $rol_display = match($_SESSION['usuario_rol'] ?? '') {
                  'admin' => 'Administrador',
                  'duenio' => 'Dueño de Local',
                  'cliente' => 'Cliente',
                  default => 'Usuario'
                };
                echo $rol_display;
              ?>
            </span>
          </div>
          <div class="profile-avatar" aria-hidden="true">
            <?php 
              $nombre = $_SESSION['usuario_nombre'] ?? 'U';
              $iniciales = '';
              $palabras = explode(' ', $nombre);
              foreach ($palabras as $palabra) {
                $iniciales .= mb_strtoupper(mb_substr($palabra, 0, 1));
                if (strlen($iniciales) >= 2) break;
              }
              echo htmlspecialchars($iniciales);
            ?>
          </div>
          <span class="dropdown-icon"></span>
        </button>

        <!-- Dropdown de perfil -->
        <div class="profile-dropdown" id="profileDropdown" role="menu">
          <?php if ($_SESSION['usuario_rol'] == 'admin'): ?>
            <a href="/tp_eg/admin/admin.php" class="dropdown-item" role="menuitem">
              Panel de Administración
            </a>
          <?php elseif ($_SESSION['usuario_rol'] == 'duenio'): ?>
            <a href="/tp_eg/duenio/duenio.php" class="dropdown-item" role="menuitem">
              Panel de Dueño
            </a>
            <a href="/tp_eg/duenio/solicitudes.php" class="dropdown-item" role="menuitem">
              Solicitudes
            </a>
            <a href="/tp_eg/duenio/reportes.php" class="dropdown-item" role="menuitem">
              Reportes
            </a>
          <?php elseif ($_SESSION['usuario_rol'] == 'cliente'): ?>
            <a href="/tp_eg/cliente/cliente.php" class="dropdown-item" role="menuitem">
              Mi Panel
            </a>
          <?php endif; ?>
          <a href="/tp_eg/auth/logout.php" class="dropdown-item danger" role="menuitem">
            Cerrar Sesión
          </a>
        </div>
      <?php else: ?>
        <!-- Usuario no logueado -->
        <a href="/tp_eg/auth/login.php" class="btn btn-primary-custom btn-login-header">
          Iniciar Sesión
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Overlay para cerrar el sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Menú lateral (Sidebar) -->
<nav class="sidebar" id="sidebar" role="navigation" aria-label="Menú principal">
  <div class="sidebar-header">
    <button class="sidebar-close" id="sidebarClose" aria-label="Cerrar menú">
      ✕
    </button>
  </div>

  <div class="sidebar-nav">
    <!-- Enlaces públicos -->
    <a href="/tp_eg/index.php" class="sidebar-nav-item">
      <span>Inicio</span>
    </a>

    <?php if (isset($_SESSION['usuario_id'])): ?>
      <!-- Mi Panel según rol -->
      <?php if ($_SESSION['usuario_rol'] == 'admin'): ?>
        <a href="/tp_eg/admin/admin.php" class="sidebar-nav-item">
          <span>Mi Panel</span>
        </a>
      <?php elseif ($_SESSION['usuario_rol'] == 'duenio'): ?>
        <a href="/tp_eg/duenio/duenio.php" class="sidebar-nav-item">
          <span>Mi Panel</span>
        </a>
      <?php elseif ($_SESSION['usuario_rol'] == 'cliente'): ?>
        <a href="/tp_eg/cliente/cliente.php" class="sidebar-nav-item">
          <span>Mi Panel</span>
        </a>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Promociones y Novedades (visibles para todos) -->
    <a href="/tp_eg/promociones.php" class="sidebar-nav-item">
      <span>Promociones</span>
    </a>
    <a href="/tp_eg/novedades.php" class="sidebar-nav-item">
      <span>Novedades</span>
    </a>
    <a href="/tp_eg/locales.php" class="sidebar-nav-item">
      <span>Locales</span>
    </a>
    
    <a href="/tp_eg/contacto.php" class="sidebar-nav-item">
      <span>Contacto</span>
    </a>

    <?php if (isset($_SESSION['usuario_id'])): ?>
      <a href="/tp_eg/auth/logout.php" class="sidebar-nav-item sidebar-nav-logout">
        <span>Cerrar Sesión</span>
      </a>
    <?php endif; ?>
  </div>
</nav>

<script>
// JavaScript para manejo del menú y perfil
document.addEventListener('DOMContentLoaded', function() {
  const menuToggle = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  const sidebarClose = document.getElementById('sidebarClose');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const profileButton = document.getElementById('profileButton');
  const profileDropdown = document.getElementById('profileDropdown');

  // Toggle sidebar
  function toggleSidebar() {
    sidebar.classList.toggle('show');
    sidebarOverlay.classList.toggle('show');
    const isOpen = sidebar.classList.contains('show');
    menuToggle.setAttribute('aria-expanded', isOpen);
    
    // Prevenir scroll del body cuando el menú está abierto
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  }

  if (menuToggle) {
    menuToggle.addEventListener('click', toggleSidebar);
  }

  if (sidebarClose) {
    sidebarClose.addEventListener('click', toggleSidebar);
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', toggleSidebar);
  }

  // Toggle dropdown de perfil
  if (profileButton && profileDropdown) {
    profileButton.addEventListener('click', function(e) {
      e.stopPropagation();
      profileDropdown.classList.toggle('show');
      profileButton.classList.toggle('active');
      const isOpen = profileDropdown.classList.contains('show');
      profileButton.setAttribute('aria-expanded', isOpen);
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function(e) {
      if (!profileButton.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove('show');
        profileButton.classList.remove('active');
        profileButton.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Cerrar sidebar con tecla ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (sidebar.classList.contains('show')) {
        toggleSidebar();
      }
      if (profileDropdown && profileDropdown.classList.contains('show')) {
        profileDropdown.classList.remove('show');
        profileButton.classList.remove('active');
        profileButton.setAttribute('aria-expanded', 'false');
      }
    }
  });
});
</script>
