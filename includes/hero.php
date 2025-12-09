<?php
// Definir las imágenes estáticas del carousel con descripciones accesibles
$imagenes_hero = [
    [
        'src' => '/imagenes/img1.jpg',
        'alt' => 'Shopping Ofertópolis - Vista del centro comercial con locales y promociones destacadas'
    ],
    [
        'src' => '/imagenes/img2.jpg',
        'alt' => 'Cafeteria en Ofertópolis - Ambiente para disfrutar de café y postres'
    ]
];
?>

<section class="hero-carousel-section fade-in" role="banner" aria-label="Carrusel principal de imágenes destacadas">
  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000" data-bs-pause="false" aria-roledescription="carrusel"
    
    <!-- Indicadores -->
    <div class="carousel-indicators">
      <?php foreach ($imagenes_hero as $index => $imagen): ?>
        <button type="button" 
                data-bs-target="#heroCarousel" 
                data-bs-slide-to="<?php echo $index; ?>" 
                <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?>
                aria-label="Ir a imagen <?php echo $index + 1; ?>">
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Slides -->
    <div class="carousel-inner">
      <?php foreach ($imagenes_hero as $index => $imagen): ?>
        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
          <img src="<?php echo $imagen['src']; ?>" class="d-block w-100 hero-carousel-img" alt="<?php echo $imagen['alt']; ?>">
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Controles -->
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Siguiente</span>
    </button>
    
  </div>
</section>
