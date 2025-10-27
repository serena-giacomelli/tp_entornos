<?php
session_start();
include_once("../includes/db.php");
include_once("../includes/mail_config.php");

// Cargar PHPMailer
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Aprobar dueño y asignar locales
if (isset($_POST['aprobar'])) {
    $duenio_id = intval($_POST['duenio_id']);
    $locales_asignados = $_POST['locales'] ?? []; // Array de IDs de locales
    
    // Actualizar estado del dueño
    $conn->query("UPDATE usuarios SET estado_cuenta='activo' WHERE id=$duenio_id");
    
    // Asignar locales al dueño
    if (!empty($locales_asignados)) {
        foreach ($locales_asignados as $local_id) {
            $local_id = intval($local_id);
            $conn->query("UPDATE locales SET id_duenio=$duenio_id WHERE id=$local_id");
        }
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
        $success = "Dueño aprobado, pero no se pudo enviar el email. Error: {$mail->ErrorInfo}";
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
        $success = "Cuenta denegada, pero no se pudo enviar el email.";
    }
}

// Obtener dueños pendientes
$duenios_pendientes = $conn->query("SELECT * FROM usuarios WHERE rol='duenio' AND estado_cuenta='pendiente' ORDER BY fecha_registro DESC");

// Obtener todos los dueños (para ver histórico)
$todos_duenios = $conn->query("SELECT * FROM usuarios WHERE rol='duenio' ORDER BY fecha_registro DESC");

// Obtener locales sin dueño asignado
$locales_disponibles = $conn->query("SELECT * FROM locales WHERE id_duenio IS NULL OR id_duenio = 0 ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Validar Dueños - Admin - OFERTÓPOLIS</title>
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
    <h3 style="color: var(--primary-color); font-weight: 700;">Validar Dueños de Locales</h3>
    <a href="admin.php" class="btn btn-secondary">← Volver al Panel</a>
  </div>

  <?php if(isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $success ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="alert alert-info">
    <strong>ℹ️ Instrucciones:</strong> Aprueba las cuentas de los dueños de locales y asígnales uno o más locales disponibles.
  </div>

  <!-- Dueños Pendientes -->
  <div class="card shadow-sm mb-4">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">⏳ Dueños Pendientes de Aprobación</h5>
    </div>
    <div class="card-body">
      <?php if($duenios_pendientes->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background-color: var(--light);">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Fecha Registro</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php while($d = $duenios_pendientes->fetch_assoc()): ?>
              <tr>
                <td><strong>#<?= $d['id'] ?></strong></td>
                <td><?= htmlspecialchars($d['nombre']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($d['fecha_registro'])) ?></td>
                <td>
                  <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAprobar<?= $d['id'] ?>">
                    ✓ Aprobar y Asignar Locales
                  </button>
                  <a href="?denegar=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Denegar esta cuenta?\n\nEl usuario será notificado por email.')">
                    ✗ Denegar
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
                          <strong>👤 Dueño:</strong> <?= htmlspecialchars($d['nombre']) ?><br>
                          <strong>📧 Email:</strong> <?= htmlspecialchars($d['email']) ?><br>
                          <strong>📅 Registro:</strong> <?= date('d/m/Y H:i', strtotime($d['fecha_registro'])) ?>
                        </div>
                        
                        <hr>
                        
                        <label class="form-label fw-bold">🏪 Seleccionar Locales a Asignar:</label>
                        
                        <?php
                        $locales_disponibles->data_seek(0);
                        if ($locales_disponibles->num_rows > 0):
                        ?>
                          <div class="list-group mb-3">
                            <?php while($local = $locales_disponibles->fetch_assoc()): ?>
                              <label class="list-group-item list-group-item-action">
                                <input class="form-check-input me-2" type="checkbox" name="locales[]" value="<?= $local['id'] ?>">
                                <strong style="color: var(--primary-color);"><?= htmlspecialchars($local['nombre']) ?></strong>
                                <span class="badge bg-primary ms-2">#<?= $local['id'] ?></span>
                                <?php if(!empty($local['ubicacion'])): ?>
                                  <br><small class="text-muted ms-4">📍 <?= htmlspecialchars($local['ubicacion']) ?></small>
                                <?php endif; ?>
                                <?php if(!empty($local['rubro'])): ?>
                                  <span class="badge bg-secondary ms-2"><?= htmlspecialchars($local['rubro']) ?></span>
                                <?php endif; ?>
                              </label>
                            <?php endwhile; ?>
                          </div>
                          <div class="alert alert-warning">
                            <strong>⚠️ Importante:</strong> Debes seleccionar al menos un local para aprobar la cuenta.
                          </div>
                        <?php else: ?>
                          <div class="alert alert-danger">
                            <strong>⚠️ No hay locales disponibles sin dueño asignado.</strong><br>
                            Primero debes <a href="locales.php" class="alert-link">crear nuevos locales</a> antes de aprobar dueños.
                          </div>
                        <?php endif; ?>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <?php if($locales_disponibles->num_rows > 0): ?>
                          <button type="submit" name="aprobar" class="btn btn-success">
                            ✓ Aprobar y Asignar
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
          <div style="font-size: 3rem; color: var(--primary-color);">✅</div>
          <p class="text-muted mb-0">No hay dueños pendientes de aprobación</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Todos los Dueños (Histórico) -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">📋 Todos los Dueños de Locales</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background-color: var(--light);">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Locales Asignados</th>
              <th>Estado</th>
              <th>Fecha Registro</th>
            </tr>
          </thead>
          <tbody>
            <?php while($d = $todos_duenios->fetch_assoc()): 
              // Contar locales del dueño
              $locales_count = $conn->query("SELECT COUNT(*) as total FROM locales WHERE id_duenio={$d['id']}")->fetch_assoc();
            ?>
            <tr>
              <td><strong>#<?= $d['id'] ?></strong></td>
              <td><?= htmlspecialchars($d['nombre']) ?></td>
              <td><?= htmlspecialchars($d['email']) ?></td>
              <td>
                <span class="badge bg-primary"><?= $locales_count['total'] ?> local<?= $locales_count['total'] != 1 ? 'es' : '' ?></span>
              </td>
              <td>
                <?php if($d['estado_cuenta'] == 'activo'): ?>
                  <span class="badge bg-success">✓ Activo</span>
                <?php elseif($d['estado_cuenta'] == 'pendiente'): ?>
                  <span class="badge bg-warning text-dark">⏳ Pendiente</span>
                <?php else: ?>
                  <span class="badge bg-danger">✗ Denegado</span>
                <?php endif; ?>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($d['fecha_registro'])) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
</main>

<?php include("../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>