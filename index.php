<?php
session_start();
include_once("includes/db.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Descubrí las mejores promociones y descuentos exclusivos en tu shopping favorito. Ofertas para clientes Inicial, Medium y Premium.">
  <title>Ofertópolis - Tu shopping con las mejores promociones</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/estilos.css?v=<?php echo time(); ?>" rel="stylesheet">
  <link href="css/header.css?v=<?php echo time(); ?>" rel="stylesheet">
  <link href="css/footer.css?v=<?php echo time(); ?>" rel="stylesheet">
  <link href="css/hero.css?v=<?php echo time(); ?>" rel="stylesheet">
  <link href="css/cards.css?v=<?php echo time(); ?>" rel="stylesheet">
  <link href="css/utilities.css?v=<?php echo time(); ?>" rel="stylesheet">
  <style>
    /* Animaciones para el carousel */
    .carousel-item {
      transition: transform 0.8s ease-in-out, opacity 0.8s ease-in-out;
    }
    
    /* Efecto fade para las cards dentro del carousel */
    .carousel-item .card-custom {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.6s ease-out;
    }
    
    .carousel-item.active .card-custom {
      opacity: 1;
      transform: translateY(0);
    }
    
    /* Delay escalonado para las cards */
    .carousel-item.active .card-custom:nth-child(1) {
      transition-delay: 0.1s;
    }
    
    .carousel-item.active .card-custom:nth-child(2) {
      transition-delay: 0.2s;
    }
    
    .carousel-item.active .card-custom:nth-child(3) {
      transition-delay: 0.3s;
    }
    
    /* Efecto zoom suave en hover */
    .card-custom {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card-custom:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 25px rgba(113, 0, 20, 0.25);
    }
    
    /* Animación para los controles del carousel */
    .carousel-control-prev,
    .carousel-control-next {
      opacity: 0.7;
      transition: opacity 0.3s ease, transform 0.3s ease;
    }
    
    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      opacity: 1;
      transform: scale(1.1);
    }
    
    /* Animación para los indicadores */
    .carousel-indicators button {
      transition: all 0.3s ease;
    }
    
    .carousel-indicators button:hover {
      transform: scale(1.2);
    }
    
    /* Efecto de entrada para la sección de novedades */
    #novedadesCarousel .carousel-item .card-custom {
      opacity: 0;
      transform: scale(0.9);
      transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    #novedadesCarousel .carousel-item.active .card-custom {
      opacity: 1;
      transform: scale(1);
    }
    
    /* Animación pulsante para el título de novedad */
    .novedad-title {
      animation: subtle-pulse 2s ease-in-out infinite;
    }
    
    @keyframes subtle-pulse {
      0%, 100% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.02);
      }
    }
    
    /* Mejora en la transición del carousel de Bootstrap */
    .carousel-inner {
      overflow: visible;
    }
    
    /* Efecto de deslizamiento personalizado */
    .carousel.slide .carousel-item {
      transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    
    /* Animación para la sección al hacer scroll */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .section-animate {
      animation: fadeInUp 0.8s ease-out;
    }
  </style>
</head>
<body>

<?php include("includes/header.php"); ?>

<main id="main-content" class="main-content" role="main">
  <?php include("includes/hero.php"); ?>

  <!-- CAROUSEL DE PROMOCIONES DESTACADAS -->
  <section id="promociones" class="container my-5 section-animate" aria-labelledby="promociones-title">
    <h2 id="promociones-title" class="section-title text-center mb-4">Promociones Destacadas</h2>

    <?php
    // Obtener las 6 promociones más recientes aprobadas
    $sql_promos = "SELECT p.*, l.nombre as local, l.rubro, l.ubicacion 
                   FROM promociones p 
                   INNER JOIN locales l ON p.id_local = l.id 
                   WHERE p.estado = 'aprobada'
                   AND p.fecha_fin >= CURDATE()
                   ORDER BY p.id DESC 
                   LIMIT 6";
    $result_promos = $conn->query($sql_promos);
    ?>

    <?php if ($result_promos && $result_promos->num_rows > 0): ?>
      <div id="promocionesCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-pause="hover" aria-label="Carrusel de promociones destacadas">
        <div class="carousel-indicators" role="tablist" aria-label="Indicadores de promociones">
          <?php for($i = 0; $i < ceil($result_promos->num_rows / 3); $i++): ?>
            <button type="button" data-bs-target="#promocionesCarousel" data-bs-slide-to="<?= $i ?>" 
                    class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" 
                    aria-label="Grupo de promociones <?= $i + 1 ?>" role="tab"></button>
          <?php endfor; ?>
        </div>
        
        <div class="carousel-inner" role="region" aria-live="polite">
          <?php 
          $promos = [];
          while($row = $result_promos->fetch_assoc()) {
            $promos[] = $row;
          }
          
          $chunks = array_chunk($promos, 3);
          foreach($chunks as $index => $chunk): 
          ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
              <div class="row g-3">
                <?php foreach($chunk as $promo): ?>
                  <div class="col-md-4">
                    <div class="card card-custom h-100">
                      <div class="card-body">
                        <h5 class="card-title-promo"><?= htmlspecialchars($promo['titulo']) ?></h5>
                        <p class="mb-2">
                          <strong>Local:</strong> <?= htmlspecialchars($promo['local']) ?>
                          <?php if (!empty($promo['rubro'])): ?>
                            <span class="badge bg-info ms-1"><?= htmlspecialchars($promo['rubro']) ?></span>
                          <?php endif; ?>
                        </p>
                        <?php if (!empty($promo['ubicacion'])): ?>
                          <p class="mb-2 text-muted small">
                            <strong>Ubicación:</strong> <?= htmlspecialchars($promo['ubicacion']) ?>
                          </p>
                        <?php endif; ?>
                        <p class="card-text"><?= htmlspecialchars(substr($promo['descripcion'], 0, 100)) ?>...</p>
                        <a href="promociones.php" class="btn btn-primary btn-sm">Ver más</a>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#promocionesCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#promocionesCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Siguiente</span>
        </button>
      </div>
    <?php else: ?>
      <div class="alert alert-info">No hay promociones disponibles en este momento.</div>
    <?php endif; ?>
  </section>

  <!-- CAROUSEL DE NOVEDADES -->
  <section id="novedades" class="container mt-4 mb-5 section-animate" aria-labelledby="novedades-title">
    <h2 id="novedades-title" class="section-title text-center mb-2">Últimas Novedades</h2>

    <?php
    // Obtener las 4 novedades más recientes vigentes
    $sql_novedades = "SELECT * FROM novedades 
                      WHERE (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
                      ORDER BY fecha_publicacion DESC 
                      LIMIT 4";
    $result_novedades = $conn->query($sql_novedades);
    ?>

    <?php if ($result_novedades && $result_novedades->num_rows > 0): ?>
      <div id="novedadesCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000" data-bs-pause="hover" aria-label="Carrusel de últimas novedades">
        <div class="carousel-indicators" role="tablist" aria-label="Indicadores de novedades">
          <?php for($i = 0; $i < $result_novedades->num_rows; $i++): ?>
            <button type="button" data-bs-target="#novedadesCarousel" data-bs-slide-to="<?= $i ?>" 
                    class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" 
                    aria-label="Novedad <?= $i + 1 ?>" role="tab"></button>
          <?php endfor; ?>
        </div>
        
        <div class="carousel-inner" role="region" aria-live="polite">
          <?php 
          $novedades = [];
          while($row = $result_novedades->fetch_assoc()) {
            $novedades[] = $row;
          }
          
          foreach($novedades as $index => $novedad): 
          ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
              <div class="row justify-content-center">
                <div class="col-md-8">
                  <div class="card card-custom h-100">
                    <div class="card-body text-center">
                      <h3 class="novedad-title"><?= htmlspecialchars($novedad['titulo']) ?></h3>
                      <p class="text-muted mb-3">
                        <small>Publicado: <?= date('d/m/Y', strtotime($novedad['fecha_publicacion'])) ?></small>
                      </p>
                      <p class="card-text"><?= nl2br(htmlspecialchars($novedad['contenido'])) ?></p>
                      <a href="novedades.php" class="btn btn-primary mt-3">Ver más novedades</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#novedadesCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#novedadesCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Siguiente</span>
        </button>
      </div>
    <?php else: ?>
      <div class="alert alert-info">No hay novedades disponibles en este momento.</div>
    <?php endif; ?>
  </section>
</main>

<?php include("includes/footer.php"); ?>

<script>
  // Animaciones avanzadas para los carousels
  document.addEventListener('DOMContentLoaded', function() {
    // Configurar carousels con eventos personalizados
    const promocionesCarousel = document.getElementById('promocionesCarousel');
    const novedadesCarousel = document.getElementById('novedadesCarousel');
    
    // Función para animar las cards al cambiar de slide
    function animateCards(carouselElement) {
      if (!carouselElement) return;
      
      const bsCarousel = new bootstrap.Carousel(carouselElement, {
        interval: carouselElement.dataset.bsInterval || 5000,
        pause: 'hover',
        wrap: true,
        touch: true
      });
      
      // Evento antes de cambiar de slide
      carouselElement.addEventListener('slide.bs.carousel', function(e) {
        const cards = e.relatedTarget.querySelectorAll('.card-custom');
        cards.forEach(card => {
          card.style.opacity = '0';
          card.style.transform = 'translateY(20px)';
        });
      });
      
      // Evento después de cambiar de slide
      carouselElement.addEventListener('slid.bs.carousel', function(e) {
        const cards = e.relatedTarget.querySelectorAll('.card-custom');
        cards.forEach((card, index) => {
          setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          }, index * 100);
        });
      });
    }
    
    // Aplicar animaciones a ambos carousels
    animateCards(promocionesCarousel);
    animateCards(novedadesCarousel);
    
    // Intersection Observer para animar secciones al hacer scroll
    const sections = document.querySelectorAll('.section-animate');
    
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -100px 0px'
    };
    
    const sectionObserver = new IntersectionObserver(function(entries) {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);
    
    sections.forEach(section => {
      section.style.opacity = '0';
      section.style.transform = 'translateY(30px)';
      section.style.transition = 'all 0.8s ease-out';
      sectionObserver.observe(section);
    });
    
    // Efecto de paralaje suave en los controles del carousel
    const carouselControls = document.querySelectorAll('.carousel-control-prev, .carousel-control-next');
    carouselControls.forEach(control => {
      control.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
      });
      
      control.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
      });
    });
    
    // Auto-play mejorado: pausar cuando el usuario interactúa
    document.querySelectorAll('.carousel').forEach(carousel => {
      carousel.addEventListener('mouseenter', function() {
        const bsCarousel = bootstrap.Carousel.getInstance(this);
        if (bsCarousel) bsCarousel.pause();
      });
      
      carousel.addEventListener('mouseleave', function() {
        const bsCarousel = bootstrap.Carousel.getInstance(this);
        if (bsCarousel) bsCarousel.cycle();
      });
    });
    
    // Preload de imágenes para transiciones más suaves
    const preloadCards = () => {
      const allCards = document.querySelectorAll('.carousel-item:not(.active) .card-custom');
      allCards.forEach(card => {
        card.style.willChange = 'transform, opacity';
      });
    };
    
    preloadCards();
  });
</script>
</body>
</html>
