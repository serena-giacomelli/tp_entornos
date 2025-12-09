<footer class="footer-custom" role="contentinfo" aria-label="Información del sitio">
  <div class="footer-content">
    <!-- Información del Shopping -->
    <div class="footer-section">
      <h4>OFERTÓPOLIS</h4>
      <p>Tu shopping de confianza con las mejores promociones y descuentos exclusivos.</p>
      <p><span aria-label="Ubicación">📍</span> Av. Pellegrini 1234, Rosario</p>
      <p><span aria-label="Teléfono">📞</span> <a href="tel:08006337827" style="color: inherit; text-decoration: none;">0800-OFERTAS (633-7827)</a></p>
      <p><span aria-label="Email">✉️</span> <a href="mailto:info@ofertopolis.com" style="color: inherit; text-decoration: none;">info@ofertopolis.com</a></p>
    </div>

    <!-- Enlaces rápidos -->
    <div class="footer-section">
      <h4>Enlaces Rápidos</h4>
      <nav aria-label="Enlaces rápidos del sitio">
        <a href="/index.php" aria-label="Ir a página de inicio">Inicio</a>
        <?php if (!isset($_SESSION['usuario_id'])): ?>
          <a href="/auth/login.php" aria-label="Ir a página de inicio de sesión">Iniciar Sesión</a>
          <a href="/auth/register_cliente.php" aria-label="Registrarse como cliente">Registrarse como Cliente</a>
          <a href="/auth/register_duenio.php" aria-label="Registrarse como dueño de local">Registrarse como Dueño</a>
        <?php endif; ?>
        <a href="/contacto.php" aria-label="Ir a página de contacto">Contacto</a>
      </nav>
    </div>

    <!-- Horarios -->
    <div class="footer-section">
      <h4>Horarios de Atención</h4>
      <p><strong>Lunes a Viernes:</strong><br>10:00 - 22:00 hs</p>
      <p><strong>Sábados:</strong><br>10:00 - 23:00 hs</p>
      <p><strong>Domingos y Feriados:</strong><br>12:00 - 21:00 hs</p>
    </div>

    <!-- Redes sociales -->
    <div class="footer-section">
      <h4>Seguinos</h4>
      <nav aria-label="Redes sociales">
        <a href="https://facebook.com/ofertopolis" target="_blank" rel="noopener noreferrer" aria-label="Seguinos en Facebook, se abre en nueva ventana">Facebook</a>
        <a href="https://instagram.com/ofertopolis" target="_blank" rel="noopener noreferrer" aria-label="Seguinos en Instagram, se abre en nueva ventana">Instagram</a>
        <a href="https://twitter.com/ofertopolis" target="_blank" rel="noopener noreferrer" aria-label="Seguinos en Twitter, se abre en nueva ventana">Twitter</a>
        <a href="https://linkedin.com/company/ofertopolis" target="_blank" rel="noopener noreferrer" aria-label="Seguinos en LinkedIn, se abre en nueva ventana">LinkedIn</a>
      </nav>
      <p class="footer-spacing" style="margin-top: 15px;">
      </p>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© <?= date('Y') ?> OFERTÓPOLIS. Todos los derechos reservados.</p>
    <p class="footer-text-small">
      Desarrollado con <span aria-label="amor">❤️</span> por Alaniz & Giacomelli | UTN FRRO - Entornos Gráficos
    </p>
  </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
