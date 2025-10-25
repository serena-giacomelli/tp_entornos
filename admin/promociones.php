<?php
session_start();
include_once("../includes/db.php");

// Cargar PHPMailer con Composer
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// --- CREAR PROMOCIÓN ---
if (isset($_POST['agregar'])) {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $id_local = intval($_POST['id_local']);
    $categoria = trim($_POST['categoria_minima']);
    $fecha_inicio = trim($_POST['fecha_inicio']);
    $fecha_fin = trim($_POST['fecha_fin']);
    $estado = "aprobada";

    $conn->query("INSERT INTO promociones (titulo, descripcion, id_local, categoria_minima, fecha_inicio, fecha_fin, estado)
                  VALUES ('$titulo','$descripcion',$id_local,'$categoria','$fecha_inicio','$fecha_fin','$estado')");
}

// --- ELIMINAR PROMOCIÓN ---
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM promociones WHERE id=$id");
}

// --- APROBAR PROMOCIÓN ---
if (isset($_GET['accion']) && $_GET['accion'] == 'aprobar' && isset($_GET['id'])) {
    $promo_id = intval($_GET['id']);
    $conn->query("UPDATE promociones SET estado='aprobada' WHERE id=$promo_id");
    
    // Obtener datos del dueño y la promoción
    $result = $conn->query("SELECT u.email, u.nombre, p.titulo, p.descripcion, p.fecha_inicio, p.fecha_fin 
                            FROM usuarios u 
                            JOIN locales l ON l.id_duenio=u.id 
                            JOIN promociones p ON p.id_local=l.id
                            WHERE p.id=$promo_id LIMIT 1");
    
    if ($result && $duenio = $result->fetch_assoc()) {
        $mail = new PHPMailer(true);
        
        try {
            // Configuración según entorno
            if ($_SERVER['SERVER_NAME'] == 'localhost') {
                // Local: MailHog
                $mail->isSMTP();
                $mail->Host = 'localhost';
                $mail->Port = 1025;
                $mail->SMTPAuth = false;
                $mail->setFrom('admin@ofertopolis.com', 'Administración Ofertópolis');
            } else {
                // Producción: SMTP real
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'tuemail@gmail.com';
                $mail->Password = 'tu_clave_de_aplicacion';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('admin@ofertopolis.com', 'Administración Ofertópolis');
            }

            $mail->addAddress($duenio['email'], $duenio['nombre']);
            $mail->Subject = 'Promoción aprobada';
            $mail->Body = "Hola {$duenio['nombre']},\n\n" .
                          "¡Buenas noticias! Tu promoción ha sido aprobada por el administrador.\n\n" .
                          "Título: {$duenio['titulo']}\n" .
                          "Descripción: {$duenio['descripcion']}\n" .
                          "Vigencia: del {$duenio['fecha_inicio']} al {$duenio['fecha_fin']}\n\n" .
                          "Tu promoción ya está visible para los clientes del shopping.\n\n" .
                          "Atentamente,\n" .
                          "Equipo Ofertópolis";
            
            $mail->send();
            $notification = "Promoción aprobada y dueño notificado exitosamente.";
        } catch (Exception $e) {
            $notification = "Promoción aprobada, pero no se pudo enviar el correo. Error: {$mail->ErrorInfo}";
        }
    }
}

// --- RECHAZAR PROMOCIÓN ---
if (isset($_GET['accion']) && $_GET['accion'] == 'rechazar' && isset($_GET['id'])) {
    $promo_id = intval($_GET['id']);
    $conn->query("UPDATE promociones SET estado='rechazada' WHERE id=$promo_id");
    
    // Obtener datos del dueño y la promoción
    $result = $conn->query("SELECT u.email, u.nombre, p.titulo, p.descripcion 
                            FROM usuarios u 
                            JOIN locales l ON l.id_duenio=u.id 
                            JOIN promociones p ON p.id_local=l.id
                            WHERE p.id=$promo_id LIMIT 1");
    
    if ($result && $duenio = $result->fetch_assoc()) {
        $mail = new PHPMailer(true);
        
        try {
            // Configuración según entorno
            if ($_SERVER['SERVER_NAME'] == 'localhost') {
                // Local: MailHog
                $mail->isSMTP();
                $mail->Host = 'localhost';
                $mail->Port = 1025;
                $mail->SMTPAuth = false;
                $mail->setFrom('admin@ofertopolis.com', 'Administración Ofertópolis');
            } else {
                // Producción: SMTP real
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'tuemail@gmail.com';
                $mail->Password = 'tu_clave_de_aplicacion';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('admin@ofertopolis.com', 'Administración Ofertópolis');
            }

            $mail->addAddress($duenio['email'], $duenio['nombre']);
            $mail->Subject = 'Promoción rechazada';
            $mail->Body = "Hola {$duenio['nombre']},\n\n" .
                          "Lamentamos informarte que tu promoción ha sido rechazada.\n\n" .
                          "Título: {$duenio['titulo']}\n" .
                          "Descripción: {$duenio['descripcion']}\n\n" .
                          "Por favor, revisa los términos y condiciones para crear promociones y " .
                          "vuelve a intentarlo con una promoción que cumpla con nuestras políticas.\n\n" .
                          "Atentamente,\n" .
                          "Equipo Ofertópolis";
            
            $mail->send();
            $notification = "Promoción rechazada y dueño notificado exitosamente.";
        } catch (Exception $e) {
            $notification = "Promoción rechazada, pero no se pudo enviar el correo. Error: {$mail->ErrorInfo}";
        }
    }
}

// --- PAGINACIÓN ---
$limite = 5;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $limite;

// Contar total de promociones
$total_result = $conn->query("SELECT COUNT(*) as total FROM promociones");
$total_row = $total_result->fetch_assoc();
$total_promociones = $total_row['total'];
$total_paginas = ceil($total_promociones / $limite);

// Traer promociones con paginación
$promos = $conn->query("SELECT p.*, l.nombre AS local FROM promociones p LEFT JOIN locales l ON p.id_local = l.id LIMIT $inicio, $limite");
$locales = $conn->query("SELECT id, nombre FROM locales");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Promociones - Panel Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/estilos.css" rel="stylesheet">
<link href="../css/header.css" rel="stylesheet">
<link href="../css/footer.css" rel="stylesheet">
<link href="../css/panels.css" rel="stylesheet">
</head>
<body>

<?php include("../includes/header.php"); ?>

<main id="main-content" class="main-content">
<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 style="color: var(--primary-color); font-weight: 700;">Gestión de Promociones</h3>
    <div>
      <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalAgregar">Nueva Promoción</button>
      <a href="admin.php" class="btn btn-secondary">Volver al Panel</a>
    </div>
  </div>

  <?php if(isset($notification)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= $notification ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Tabla -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Lista de Promociones</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background-color: var(--light);">
            <tr>
              <th>ID</th>
              <th>Título</th>
              <th>Descripción</th>
              <th>Local</th>
              <th>Categoría Mínima</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
      <?php while($p = $promos->fetch_assoc()): ?>
        <tr>
          <td><strong>#<?= $p['id'] ?></strong></td>
          <td><?= htmlspecialchars($p['titulo']) ?></td>
          <td><?= htmlspecialchars($p['descripcion']) ?></td>
          <td><?= htmlspecialchars($p['local']) ?></td>
          <td><span class="badge bg-info"><?= htmlspecialchars($p['categoria_minima']) ?></span></td>
          <td>
            <?php if($p['estado'] == 'pendiente'): ?>
              <span class="badge bg-warning">Pendiente</span>
            <?php elseif($p['estado'] == 'aprobada'): ?>
              <span class="badge bg-success">Aprobada</span>
            <?php elseif($p['estado'] == 'rechazada'): ?>
              <span class="badge bg-danger">Rechazada</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if($p['estado'] == 'pendiente'): ?>
              <a href="?accion=aprobar&id=<?= $p['id'] ?>&pagina=<?= $pagina ?>" 
                 class="btn btn-success btn-sm" 
                 onclick="return confirm('¿Aprobar esta promoción?')">
                Aprobar
              </a>
              <a href="?accion=rechazar&id=<?= $p['id'] ?>&pagina=<?= $pagina ?>" 
                 class="btn btn-warning btn-sm" 
                 onclick="return confirm('¿Rechazar esta promoción?')">
                Rechazar
              </a>
            <?php endif; ?>
            <a href="?eliminar=<?= $p['id'] ?>&pagina=<?= $pagina ?>" 
               class="btn btn-danger btn-sm" 
               onclick="return confirm('¿Eliminar promoción?')">
              Eliminar
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Paginación -->
  <nav aria-label="Paginación de promociones" class="mt-4">
    <ul class="pagination justify-content-center">
      <!-- Botón Anterior -->
      <?php if ($pagina > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?pagina=<?= $pagina - 1 ?>">← Anterior</a>
        </li>
      <?php else: ?>
        <li class="page-item disabled">
          <span class="page-link">← Anterior</span>
        </li>
      <?php endif; ?>

      <!-- Números de página -->
      <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
        <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
          <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <!-- Botón Siguiente -->
      <?php if ($pagina < $total_paginas): ?>
        <li class="page-item">
          <a class="page-link" href="?pagina=<?= $pagina + 1 ?>">Siguiente →</a>
        </li>
      <?php else: ?>
        <li class="page-item disabled">
          <span class="page-link">Siguiente →</span>
        </li>
      <?php endif; ?>
    </ul>
  </nav>

  <!-- Información de paginación -->
  <p class="text-center text-muted">
    Página <?= $pagina ?> de <?= $total_paginas ?> | Total: <?= $total_promociones ?> promociones
  </p>
</div>
</main>

<!-- Modal agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
        <h5 class="modal-title">Nueva Promoción</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Título:</label>
            <input type="text" name="titulo" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Descripción:</label>
            <textarea name="descripcion" class="form-control" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Local:</label>
            <select name="id_local" class="form-select" required>
              <option value="">Seleccionar...</option>
              <?php
              $locales->data_seek(0);
              while($l = $locales->fetch_assoc()):
              ?>
                <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nombre']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Fecha inicio:</label>
            <input type="date" name="fecha_inicio" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Fecha fin:</label>
            <input type="date" name="fecha_fin" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Categoría mínima:</label>
            <select name="categoria_minima" class="form-select" required>
              <option value="inicial">Inicial</option>
              <option value="medium">Medium</option>
              <option value="premium">Premium</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="agregar" class="btn btn-primary">Agregar Promoción</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
