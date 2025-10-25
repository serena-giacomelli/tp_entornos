<?php
session_start();
include_once("../includes/db.php");

// Cargar PHPMailer con Composer
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'duenio') {
    header("Location: ../auth/login.php");
    exit;
}

$duenio_id = $_SESSION['usuario_id'];

// Obtener datos del dueño
$result_duenio = $conn->query("SELECT * FROM usuarios WHERE id=$duenio_id");
$duenio = $result_duenio->fetch_assoc();

// Contar locales del dueño
$count_locales = $conn->query("SELECT COUNT(*) as total FROM locales WHERE id_duenio=$duenio_id")->fetch_assoc();
$num_locales = $count_locales['total'];

// Si tiene un solo local, obtener sus datos
$local_unico = null;
if ($num_locales == 1) {
    $local_unico = $conn->query("SELECT * FROM locales WHERE id_duenio=$duenio_id LIMIT 1")->fetch_assoc();
}

// --- CREAR PROMOCIÓN ---
if (isset($_POST['agregar_promo'])) {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $dias = trim($_POST['dias_vigencia']);
    $categoria = trim($_POST['categoria_minima']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    
    // Determinar el local
    if (isset($_POST['id_local'])) {
        // Si tiene varios locales, viene del dropdown
        $id_local = intval($_POST['id_local']);
    } else {
        // Si tiene un solo local, tomarlo automáticamente
        $local_result = $conn->query("SELECT id FROM locales WHERE id_duenio=$duenio_id LIMIT 1");
        if ($local_result && $local_result->num_rows > 0) {
            $local_data = $local_result->fetch_assoc();
            $id_local = $local_data['id'];
        } else {
            $error_message = "Error: No tienes ningún local registrado. Por favor, contacta al administrador.";
            $id_local = null;
        }
    }

    if ($id_local) {
        $sql = "INSERT INTO promociones 
            (id_local, titulo, descripcion, fecha_inicio, fecha_fin, dias_vigencia, categoria_minima, estado)
            VALUES ($id_local,'$titulo','$descripcion','$fecha_inicio','$fecha_fin','$dias','$categoria','pendiente')";
        
        if ($conn->query($sql)) {
            // Enviar email al administrador
            $admin = $conn->query("SELECT email, nombre FROM usuarios WHERE rol='admin' LIMIT 1")->fetch_assoc();
        
        if ($admin) {
            $mail = new PHPMailer(true);
            
            try {
                // Configuración según entorno
                if ($_SERVER['SERVER_NAME'] == 'localhost') {
                    // Local: MailHog
                    $mail->isSMTP();
                    $mail->Host = 'localhost';
                    $mail->Port = 1025;
                    $mail->SMTPAuth = false;
                    $mail->setFrom('no-reply@ofertopolis.com', 'Ofertópolis');
                } else {
                    // Producción: SMTP real
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'tuemail@gmail.com';
                    $mail->Password = 'tu_clave_de_aplicacion';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    $mail->setFrom('notificaciones@ofertopolis.com', 'Ofertópolis');
                }

                $mail->addAddress($admin['email'], $admin['nombre']);
                $mail->Subject = 'Nueva promoción pendiente de aprobación';
                $mail->Body = "Hola {$admin['nombre']},\n\n" .
                              "Un dueño de local ha creado una nueva promoción que necesita revisión:\n\n" .
                              "Título: $titulo\n" .
                              "Descripción: $descripcion\n" .
                              "Vigencia: del $fecha_inicio al $fecha_fin\n\n" .
                              "Por favor, ingresa al panel de administración para aprobar o rechazar esta promoción.\n\n" .
                              "Atentamente,\n" .
                              "Sistema Ofertópolis";
                
                $mail->send();
                $success_message = "Promoción creada exitosamente. Se ha notificado al administrador.";
            } catch (Exception $e) {
                $success_message = "Promoción creada, pero no se pudo enviar el correo al administrador. Error: {$mail->ErrorInfo}";
            }
        } else {
            $success_message = "Promoción creada exitosamente.";
        }
        } 
    } 
} 
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM promociones WHERE id=$id AND id_local IN (SELECT id FROM locales WHERE id_duenio=$duenio_id)");
}

// Consultar locales del dueño
$locales = $conn->query("SELECT * FROM locales WHERE id_duenio=$duenio_id");
// Consultar promociones del dueño
$promos = $conn->query("SELECT p.*, l.nombre AS local 
                        FROM promociones p 
                        JOIN locales l ON p.id_local=l.id 
                        WHERE l.id_duenio=$duenio_id ORDER BY p.fecha_inicio DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Dueño - OFERTÓPOLIS</title>
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
    <h3 style="color: var(--primary-color); font-weight: 700;">Panel de Dueño</h3>
    <div>
      <a href="reportes.php" class="btn btn-outline-primary me-2">Ver Reportes</a>
      <a href="solicitudes.php" class="btn btn-secondary me-2">Solicitudes</a>
      <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
  </div>

  <?php if(isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $success_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if(isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= $error_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- INFORMACIÓN DEL DUEÑO -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Información del Dueño</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <p><strong>Nombre:</strong> <?= htmlspecialchars($duenio['nombre']) ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($duenio['email']) ?></p>
        </div>
        <div class="col-md-6">
          <p><strong>Rol:</strong> <span class="badge" style="background-color: var(--secondary-color); color: var(--dark);">Dueño de Local</span></p>
          <p><strong>Total de Locales:</strong> <span class="badge bg-primary"><?= $count_locales['total'] ?></span></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Sección de Promociones -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Mis Promociones</h5>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregar">
          Nueva Promoción
        </button>
      </div>
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
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <tbody>
      <?php while($p = $promos->fetch_assoc()): ?>
      <tr>
        <td><strong>#<?= $p['id'] ?></strong></td>
        <td><?= htmlspecialchars($p['titulo']) ?></td>
        <td><?= htmlspecialchars($p['descripcion']) ?></td>
        <td><?= htmlspecialchars($p['local']) ?></td>
        <td>
          <?php if($p['estado'] == 'pendiente'): ?>
            <span class="badge bg-warning text-dark">Pendiente</span>
          <?php elseif($p['estado'] == 'aprobada'): ?>
            <span class="badge bg-success">Aprobada</span>
          <?php elseif($p['estado'] == 'rechazada'): ?>
            <span class="badge bg-danger">Rechazada</span>
          <?php endif; ?>
        </td>
        <td>
          <a href="?eliminar=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta promoción?')">
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

</div>
  </div>

</div>
</main>

<!-- Modal agregar promoción -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
        <h5 class="modal-title" id="modalAgregarLabel">Nueva Promoción</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label fw-bold">Título:</label>
              <input type="text" name="titulo" class="form-control" placeholder="Ej: 2x1 en pizzas" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Descripción:</label>
            <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe los detalles de la promoción..." required></textarea>
          </div>
          </div>
            
            <?php if($num_locales == 1): ?>
              <!-- Si solo tiene un local, mostrar el nombre y pasar el ID oculto -->
              <div class="mb-3">
                <label class="form-label fw-bold">Local:</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($local_unico['nombre']) ?>" disabled>
                <input type="hidden" name="id_local" value="<?= $local_unico['id'] ?>">
                <small class="text-muted">Este es tu único local registrado</small>
              </div>
            <?php else: ?>
              <!-- Si tiene múltiples locales, mostrar selector -->
              <div class="mb-3">
                <label class="form-label fw-bold">Local:</label>
                <select name="id_local" class="form-select" required>
                  <option value="">Seleccionar local...</option>
                  <?php
                  $locales->data_seek(0);
                  while($l = $locales->fetch_assoc()):
                  ?>
                    <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nombre']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
            <?php endif; ?>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Días de vigencia:</label>
                <input type="text" name="dias_vigencia" class="form-control" placeholder="Ej: lunes, martes, miércoles">
                <small class="text-muted">Opcional</small>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Categoría mínima:</label>
                <select name="categoria_minima" class="form-select" required>
                  <option value="">Seleccionar categoría...</option>
                  <option value="inicial">Inicial</option>
                  <option value="medium">Medium</option>
                  <option value="premium">Premium</option>
                </select>
                <small class="text-muted">Los clientes de esta categoría o superior podrán verla</small>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Fecha inicio:</label>
                <input type="date" name="fecha_inicio" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Fecha fin:</label>
                <input type="date" name="fecha_fin" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" name="agregar_promo" class="btn btn-primary">Crear Promoción</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>
</main>

<?php include("../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>