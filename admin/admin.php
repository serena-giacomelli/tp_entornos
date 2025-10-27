<?php
session_start();
include_once("../includes/db.php");

// Cargar PHPMailer
require '../vendor/autoload.php';
require '../includes/mail_config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar sesión y rol
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Aprobar dueño y asignar locales
if (isset($_POST['aprobar'])) {
    $duenio_id = intval($_POST['duenio_id']);
    $locales_asignados = $_POST['locales'] ?? [];
    
    if (!empty($locales_asignados)) {
        // Actualizar estado del dueño
        $conn->query("UPDATE usuarios SET estado_cuenta='activo' WHERE id=$duenio_id");
        
        // Asignar locales al dueño
        foreach ($locales_asignados as $local_id) {
            $local_id = intval($local_id);
            $conn->query("UPDATE locales SET id_duenio=$duenio_id WHERE id=$local_id");
        }
        
        // Obtener email del dueño para notificación
        $duenio = $conn->query("SELECT email, nombre FROM usuarios WHERE id=$duenio_id")->fetch_assoc();
        
        // Enviar email de notificación
        $mail = new PHPMailer(true);
        try {
            configurarMail($mail);

            $mail->addAddress($duenio['email'], $duenio['nombre']);
            $mail->Subject = 'Cuenta aprobada - Ofertópolis';
            
            $cantidad_locales = count($locales_asignados);
            $mail->Body = "Hola {$duenio['nombre']},\n\n" .
                          "¡Buenas noticias! Tu cuenta de Dueño de Local ha sido aprobada.\n\n" .
                          "Se te han asignado $cantidad_locales local(es).\n\n" .
                          "Ya puedes iniciar sesión y comenzar a crear promociones para tus locales.\n\n" .
                          "Atentamente,\n" .
                          "Equipo Ofertópolis";
            
            $mail->send();
            $success = "Dueño aprobado y $cantidad_locales local(es) asignado(s) exitosamente.";
        } catch (Exception $e) {
            $success = "Dueño aprobado, pero no se pudo enviar el email.";
        }
    } else {
        $error = "Debes seleccionar al menos un local para aprobar.";
    }
}

// Denegar dueño
if (isset($_GET['denegar'])) {
    $id = intval($_GET['denegar']);
    $conn->query("UPDATE usuarios SET estado_cuenta='denegado' WHERE id=$id");
    
    // Notificar al dueño
    $duenio = $conn->query("SELECT email, nombre FROM usuarios WHERE id=$id")->fetch_assoc();
    
    $mail = new PHPMailer(true);
    try {
        configurarMail($mail);

        $mail->addAddress($duenio['email'], $duenio['nombre']);
        $mail->Subject = 'Solicitud denegada - Ofertópolis';
        $mail->Body = "Hola {$duenio['nombre']},\n\n" .
                      "Lamentablemente tu solicitud de registro como Dueño de Local ha sido denegada.\n\n" .
                      "Para más información, contacta al administrador.\n\n" .
                      "Atentamente,\n" .
                      "Equipo Ofertópolis";
        
        $mail->send();
        $success = "Cuenta denegada y notificación enviada.";
    } catch (Exception $e) {
        $success = "Cuenta denegada.";
    }
}

// Asignar locales adicionales a dueño existente
if (isset($_POST['asignar_locales_adicionales'])) {
    $duenio_id = intval($_POST['duenio_id']);
    $locales_asignados = $_POST['locales'] ?? [];
    
    if (!empty($locales_asignados)) {
        // Asignar locales al dueño
        foreach ($locales_asignados as $local_id) {
            $local_id = intval($local_id);
            $conn->query("UPDATE locales SET id_duenio=$duenio_id WHERE id=$local_id");
        }
        
        // Obtener información del dueño
        $duenio = $conn->query("SELECT email, nombre FROM usuarios WHERE id=$duenio_id")->fetch_assoc();
        
        // Enviar email de notificación
        $mail = new PHPMailer(true);
        try {
            configurarMail($mail);

            $mail->addAddress($duenio['email'], $duenio['nombre']);
            $mail->Subject = 'Nuevos locales asignados - Ofertópolis';
            
            $cantidad_locales = count($locales_asignados);
            $mail->Body = "Hola {$duenio['nombre']},\n\n" .
                          "Se te han asignado $cantidad_locales nuevo(s) local(es) adicional(es).\n\n" .
                          "Ya puedes verlos en tu panel y crear promociones para ellos.\n\n" .
                          "Atentamente,\n" .
                          "Equipo Ofertópolis";
            
            $mail->send();
            $success = $cantidad_locales . " local(es) asignado(s) exitosamente a " . htmlspecialchars($duenio['nombre']) . ".";
        } catch (Exception $e) {
            $success = "Locales asignados, pero no se pudo enviar el email.";
        }
    } else {
        $error = "Debes seleccionar al menos un local para asignar.";
    }
}

// Mensaje de aprobación
if (isset($_GET['msg']) && $_GET['msg'] == 'duenio_aprobado') {
    $msg = "Dueño aprobado correctamente.";
}

// Consultar dueños pendientes
$sql_duenios = "SELECT * FROM usuarios WHERE rol='duenio' AND estado_cuenta='pendiente' ORDER BY fecha_registro DESC";
$res_duenios = $conn->query($sql_duenios);

// Obtener locales sin dueño asignado
$locales_disponibles = $conn->query("SELECT * FROM locales WHERE id_duenio IS NULL OR id_duenio = 0 ORDER BY nombre ASC");

// Consultar todos los dueños de locales (activos y pendientes)
$sql_todos_duenios = "SELECT u.id, u.nombre, u.email, u.estado_cuenta, u.fecha_registro, 
                       COUNT(l.id) as total_locales
                       FROM usuarios u
                       LEFT JOIN locales l ON u.id = l.id_duenio
                       WHERE u.rol='duenio'
                       GROUP BY u.id, u.nombre, u.email, u.estado_cuenta, u.fecha_registro
                       ORDER BY u.fecha_registro DESC";
$res_todos_duenios = $conn->query($sql_todos_duenios);

// Contar dueños por estado
$total_duenios = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='duenio'")->fetch_assoc()['total'];
$duenios_activos = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='duenio' AND estado_cuenta='activo'")->fetch_assoc()['total'];
$duenios_pendientes = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='duenio' AND estado_cuenta='pendiente'")->fetch_assoc()['total'];

// Consultar todos los clientes
$sql_clientes = "SELECT id, nombre, email, categoria, fecha_registro, estado_cuenta FROM usuarios WHERE rol='cliente' ORDER BY fecha_registro DESC";
$res_clientes = $conn->query($sql_clientes);

// Contar clientes por categoría
$stats_inicial = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='cliente' AND categoria='inicial'")->fetch_assoc()['total'];
$stats_medium = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='cliente' AND categoria='medium'")->fetch_assoc()['total'];
$stats_premium = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='cliente' AND categoria='premium'")->fetch_assoc()['total'];
$total_clientes = $stats_inicial + $stats_medium + $stats_premium;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel del Administrador - OFERTÓPOLIS</title>
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
    <h2 style="color: var(--primary-color); font-weight: 700;">Panel del Administrador</h2>
    <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
  </div>

  <div class="alert alert-info">
    Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>.
  </div>

  <?php if(isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $success ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if(isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= $error ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if(isset($msg)): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

  <!-- Tarjetas de navegación -->
  <div class="row text-center mb-4">
    <div class="col-md-4 col-lg-2 mb-3">
      <a href="locales.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-3">
            <h5 style="color: var(--primary-color);">Locales</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4 col-lg-2 mb-3">
      <a href="novedades.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-3">
            <h5 style="color: var(--primary-color);">Novedades</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4 col-lg-2 mb-3">
      <a href="promociones.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-3">
            <h5 style="color: var(--primary-color);">Promociones</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4 col-lg-2 mb-3">
      <a href="reportes.php" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-3">
            <h5 style="color: var(--primary-color);">Reportes</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4 col-lg-2 mb-3">
      <a href="?seccion=clientes" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-3">
            <h5 style="color: var(--primary-color);">Clientes</h5>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4 col-lg-2 mb-3">
      <a href="?seccion=duenios" class="text-decoration-none">
        <div class="card shadow-sm h-100 hover-card">
          <div class="card-body py-3">
            <h5 style="color: var(--primary-color);">Dueños</h5>
          </div>
        </div>
      </a>
    </div>
  </div>

  <?php
  // Determinar qué sección mostrar
  $seccion = $_GET['seccion'] ?? 'inicio';
  ?>

  <?php if($seccion == 'inicio'): ?>
  <!-- Dueños pendientes -->
  <div class="card shadow-sm mb-4">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Dueños de Local Pendientes de Aprobación</h5>
    </div>
    <div class="card-body">
      <?php if($res_duenios->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background-color: var(--light);">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Fecha Registro</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $res_duenios->data_seek(0); // Reiniciar el puntero
              while($d = $res_duenios->fetch_assoc()): 
              ?>
                <tr>
                  <td><strong>#<?= $d['id'] ?></strong></td>
                  <td><?= htmlspecialchars($d['nombre']) ?></td>
                  <td><?= htmlspecialchars($d['email']) ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($d['fecha_registro'])) ?></td>
                  <td>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAprobar<?= $d['id'] ?>">
                      Asignar Locales
                    </button>
                    <a href="?denegar=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Rechazar esta cuenta?\n\nEl usuario será notificado por email.')">
                      Rechazar
                    </a>
                  </td>
                </tr>

                <!-- Modal para asignar locales -->
                <div class="modal fade" id="modalAprobar<?= $d['id'] ?>" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
                        <h5 class="modal-title">Aprobar y Asignar Locales</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <form method="POST">
                        <div class="modal-body">
                          <input type="hidden" name="duenio_id" value="<?= $d['id'] ?>">
                          
                          <div class="alert alert-info">
                            <strong>Dueño:</strong> <?= htmlspecialchars($d['nombre']) ?><br>
                            <strong>Email:</strong> <?= htmlspecialchars($d['email']) ?><br>
                            <strong>Registro:</strong> <?= date('d/m/Y H:i', strtotime($d['fecha_registro'])) ?>
                          </div>
                          
                          <hr>
                          
                          <label class="form-label fw-bold">Seleccionar Locales a Asignar:</label>
                          
                          <?php
                          $locales_disponibles->data_seek(0);
                          if ($locales_disponibles->num_rows > 0):
                          ?>
                            <div class="list-group mb-3" style="max-height: 400px; overflow-y: auto;">
                              <?php while($local = $locales_disponibles->fetch_assoc()): ?>
                                <label class="list-group-item list-group-item-action">
                                  <input class="form-check-input me-2" type="checkbox" name="locales[]" value="<?= $local['id'] ?>">
                                  <strong style="color: var(--primary-color);"><?= htmlspecialchars($local['nombre']) ?></strong>
                                  <span class="badge bg-primary ms-2">#<?= $local['id'] ?></span>
                                  <?php if(!empty($local['ubicacion'])): ?>
                                    <br><small class="text-muted ms-4"><?= htmlspecialchars($local['ubicacion']) ?></small>
                                  <?php endif; ?>
                                  <?php if(!empty($local['rubro'])): ?>
                                    <span class="badge bg-secondary ms-2"><?= htmlspecialchars($local['rubro']) ?></span>
                                  <?php endif; ?>
                                </label>
                              <?php endwhile; ?>
                            </div>
                            <div class="alert alert-warning">
                              <strong>Importante:</strong> Debes seleccionar al menos un local para aprobar la cuenta.
                            </div>
                          <?php else: ?>
                            <div class="alert alert-danger">
                              <strong>No hay locales disponibles sin dueño asignado.</strong><br>
                              Primero debes <a href="locales.php" class="alert-link">crear nuevos locales</a> antes de aprobar dueños.
                            </div>
                          <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                          <?php if($locales_disponibles->num_rows > 0): ?>
                            <button type="submit" name="aprobar" class="btn btn-success">
                              Aprobar y Asignar
                            </button>
                          <?php endif; ?>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-4">
          <p class="text-muted mb-0">No hay dueños pendientes de aprobación</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>

  <?php if($seccion == 'duenios'): ?>
  <!-- Lista de todos los dueños de locales -->
  <div class="card shadow-sm mb-4">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Todos los Dueños de Locales</h5>
    </div>
    <div class="card-body">
      <!-- Estadísticas de dueños -->
      <div class="row mb-3">
        <div class="col-md-4">
          <div class="alert alert-primary mb-0">
            <strong>Total Dueños:</strong> <?= $total_duenios ?>
          </div>
        </div>
        <div class="col-md-4">
          <div class="alert alert-success mb-0">
            <strong>Activos:</strong> <?= $duenios_activos ?>
          </div>
        </div>
        <div class="col-md-4">
          <div class="alert alert-warning mb-0">
            <strong>Pendientes:</strong> <?= $duenios_pendientes ?>
          </div>
        </div>
      </div>

      <?php if($res_todos_duenios && $res_todos_duenios->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background-color: var(--light);">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Locales</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php while($d = $res_todos_duenios->fetch_assoc()): ?>
                <tr>
                  <td><strong>#<?= $d['id'] ?></strong></td>
                  <td><?= htmlspecialchars($d['nombre']) ?></td>
                  <td><?= htmlspecialchars($d['email']) ?></td>
                  <td>
                    <span class="badge bg-primary"><?= $d['total_locales'] ?> local<?= $d['total_locales'] != 1 ? 'es' : '' ?></span>
                  </td>
                  <td>
                    <?php if($d['estado_cuenta'] == 'activo'): ?>
                      <span class="badge bg-success">Activo</span>
                    <?php elseif($d['estado_cuenta'] == 'pendiente'): ?>
                      <span class="badge bg-warning text-dark">Pendiente</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Denegado</span>
                    <?php endif; ?>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($d['fecha_registro'])) ?></td>
                  <td>
                    <?php if($d['estado_cuenta'] == 'activo'): ?>
                      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsignarLocales<?= $d['id'] ?>">
                        Asignar Locales
                      </button>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                </tr>

                <!-- Modal para asignar locales adicionales -->
                <?php if($d['estado_cuenta'] == 'activo'): ?>
                <div class="modal fade" id="modalAsignarLocales<?= $d['id'] ?>" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
                        <h5 class="modal-title">Asignar Locales Adicionales</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <form method="POST">
                        <div class="modal-body">
                          <input type="hidden" name="duenio_id" value="<?= $d['id'] ?>">
                          
                          <div class="alert alert-info">
                            <strong>Dueño:</strong> <?= htmlspecialchars($d['nombre']) ?><br>
                            <strong>Email:</strong> <?= htmlspecialchars($d['email']) ?><br>
                            <strong>Locales actuales:</strong> <span class="badge bg-primary"><?= $d['total_locales'] ?></span>
                          </div>
                          
                          <?php
                          // Obtener locales actuales del dueño
                          $locales_actuales = $conn->query("SELECT nombre FROM locales WHERE id_duenio={$d['id']}");
                          if ($locales_actuales->num_rows > 0):
                          ?>
                            <div class="mb-3">
                              <strong>Locales ya asignados:</strong>
                              <ul class="mb-0">
                                <?php while($la = $locales_actuales->fetch_assoc()): ?>
                                  <li><?= htmlspecialchars($la['nombre']) ?></li>
                                <?php endwhile; ?>
                              </ul>
                            </div>
                          <?php endif; ?>
                          
                          <hr>
                          
                          <label class="form-label fw-bold">Seleccionar Locales Adicionales a Asignar:</label>
                          
                          <?php
                          $locales_disponibles->data_seek(0);
                          if ($locales_disponibles->num_rows > 0):
                          ?>
                            <div class="list-group mb-3" style="max-height: 300px; overflow-y: auto;">
                              <?php while($local = $locales_disponibles->fetch_assoc()): ?>
                                <label class="list-group-item list-group-item-action">
                                  <input class="form-check-input me-2" type="checkbox" name="locales[]" value="<?= $local['id'] ?>">
                                  <strong style="color: var(--primary-color);"><?= htmlspecialchars($local['nombre']) ?></strong>
                                  <span class="badge bg-primary ms-2">#<?= $local['id'] ?></span>
                                  <?php if(!empty($local['ubicacion'])): ?>
                                    <br><small class="text-muted ms-4"><?= htmlspecialchars($local['ubicacion']) ?></small>
                                  <?php endif; ?>
                                  <?php if(!empty($local['rubro'])): ?>
                                    <span class="badge bg-secondary ms-2"><?= htmlspecialchars($local['rubro']) ?></span>
                                  <?php endif; ?>
                                </label>
                              <?php endwhile; ?>
                            </div>
                          <?php else: ?>
                            <div class="alert alert-warning">
                              <strong>No hay locales disponibles sin dueño asignado.</strong><br>
                              Todos los locales ya están asignados. Puedes <a href="locales.php" class="alert-link">crear nuevos locales</a> si es necesario.
                            </div>
                          <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                          <?php if($locales_disponibles->num_rows > 0): ?>
                            <button type="submit" name="asignar_locales_adicionales" class="btn btn-primary">
                              Asignar Locales
                            </button>
                          <?php endif; ?>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endif; ?>

              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted text-center mb-0">No hay dueños de locales registrados.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="text-center mt-3">
    <a href="admin.php" class="btn btn-secondary">← Volver al inicio</a>
  </div>

  <?php endif; ?>

  <?php if($seccion == 'clientes'): ?>
  <!-- Estadísticas de clientes -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h3 class="mb-0" style="color: var(--primary-color);"><?= $total_clientes ?></h3>
          <p class="text-muted mb-0">Total Clientes</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h3 class="mb-0 text-secondary"><?= $stats_inicial ?></h3>
          <p class="text-muted mb-0">Inicial</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h3 class="mb-0 text-info"><?= $stats_medium ?></h3>
          <p class="text-muted mb-0">Medium</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h3 class="mb-0 text-warning"><?= $stats_premium ?></h3>
          <p class="text-muted mb-0">Premium</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Lista de clientes -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Todos los Clientes Registrados</h5>
    </div>
    <div class="card-body p-0">
      <?php if($res_clientes && $res_clientes->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background-color: var(--light);">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
              </tr>
            </thead>
            <tbody>
              <?php while($c = $res_clientes->fetch_assoc()): ?>
                <tr>
                  <td><strong>#<?= $c['id'] ?></strong></td>
                  <td><?= htmlspecialchars($c['nombre']) ?></td>
                  <td><?= htmlspecialchars($c['email']) ?></td>
                  <td>
                    <?php if($c['categoria'] == 'inicial'): ?>
                      <span class="badge bg-secondary">Inicial</span>
                    <?php elseif($c['categoria'] == 'medium'): ?>
                      <span class="badge bg-info">Medium</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">Premium</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if($c['estado_cuenta'] == 'activo'): ?>
                      <span class="badge bg-success">Activo</span>
                    <?php elseif($c['estado_cuenta'] == 'pendiente'): ?>
                      <span class="badge bg-warning">Pendiente</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Denegado</span>
                    <?php endif; ?>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($c['fecha_registro'])) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="card-body">
          <p class="text-muted text-center mb-0">No hay clientes registrados.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="text-center mt-3">
    <a href="admin.php" class="btn btn-secondary">← Volver al inicio</a>
  </div>

  <?php endif; ?>

</div>
</main>

<style>
.hover-card {
  transition: transform 0.2s, box-shadow 0.2s;
}
.hover-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(113, 0, 20, 0.2) !important;
}
</style>

<?php include("../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
