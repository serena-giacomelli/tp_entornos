<footer class="footer-custom" role="contentinfo">
  <div class="footer-content">
    <!-- Información del Shopping -->
    <div class="footer-section">
      <h4>OFERTÓPOLIS</h4>
      <p>Tu shopping de confianza con las mejores promociones y descuentos exclusivos.</p>
      <p>📍 Av. Pellegrini 1234, Rosario</p>
      <p>0800-OFERTAS (633-7827)</p>
      <p>info@ofertopolis.com</p>
    </div>

    <!-- Enlaces rápidos -->
    <div class="footer-section">
      <h4>Enlaces Rápidos</h4>
      <a href="/tp_eg/index.php">Inicio</a>
      <?php if (!isset($_SESSION['usuario_id'])): ?>
        <a href="/tp_eg/auth/login.php">Iniciar Sesión</a>
        <a href="/tp_eg/auth/register_cliente.php">Registrarse como Cliente</a>
        <a href="/tp_eg/auth/register_duenio.php">Registrarse como Dueño</a>
      <?php endif; ?>
      <a href="/tp_eg/contacto.php">Contacto</a>
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
      <a href="https://facebook.com/ofertopolis" target="_blank" rel="noopener noreferrer">Facebook</a>
      <a href="https://instagram.com/ofertopolis" target="_blank" rel="noopener noreferrer">Instagram</a>
      <a href="https://twitter.com/ofertopolis" target="_blank" rel="noopener noreferrer">Twitter</a>
      <a href="https://linkedin.com/company/ofertopolis" target="_blank" rel="noopener noreferrer">LinkedIn</a>
      <p class="footer-spacing" style="margin-top: 15px;">
      </p>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© <?= date('Y') ?> OFERTÓPOLIS. Todos los derechos reservados.</p>
    <p class="footer-text-small">
      Desarrollado con ❤️ por Alaniz & Giacomelli | UTN FRRO - Entornos Gráficos
    </p>
  </div>
</footer>
