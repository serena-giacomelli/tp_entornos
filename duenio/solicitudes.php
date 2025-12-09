<?php
session_start();
include_once("../includes/db.php");

require '../vendor/autoload.php';
require '../includes/mail_config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 'duenio') {
    header("Location: ../auth/login.php");
    exit;
}

$idduenio = $_SESSION['usuario_id'];

// --- Actualizar estado ---
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $accion = $_GET['accion'];
    $id = intval($_GET['id']);
    
    if (in_array($accion, ['aceptada', 'rechazada'])) {
        $conn->query("UPDATE uso_promociones SET estado='$accion' WHERE id=$id");

        if ($accion == 'aceptada') {
            // Obtener datos del cliente
            $cliente_data = $conn->query("SELECT id_cliente FROM uso_promociones WHERE id=$id")->fetch_assoc();
            $id_cliente = $cliente_data['id_cliente'];
            
            // Obtener información del cliente
            $cliente_info = $conn->query("SELECT nombre, email, categoria FROM usuarios WHERE id=$id_cliente")->fetch_assoc();
            $categoria_anterior = $cliente_info['categoria'];

            // Contar promociones aceptadas en los últimos 6 meses (según regla de negocio)
            $count = $conn->query("SELECT COUNT(*) AS total FROM uso_promociones 
                                   WHERE id_cliente=$id_cliente 
                                   AND estado='aceptada'
                                   AND fecha_uso >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)")
                        ->fetch_assoc()['total'];

            // Determinar nueva categoría según cantidad (último semestre)
            $nueva_categoria = 'inicial';
            if ($count >= 10) {
                $nueva_categoria = 'premium';
            } elseif ($count >= 5) {
                $nueva_categoria = 'medium';
            }

            // Actualizar categoría si cambió
            if ($categoria_anterior != $nueva_categoria) {
                $conn->query("UPDATE usuarios SET categoria='$nueva_categoria' WHERE id=$id_cliente");
                
                // Enviar email de notificación de upgrade
                $mail = new PHPMailer(true);
                
                try {
                    // Configuración de email
                    configurarMail($mail);

                    $mail->addAddress($cliente_info['email'], $cliente_info['nombre']);
                    $mail->Subject = '¡Felicitaciones! Has subido de categoría';
                    $mail->Body = "Hola {$cliente_info['nombre']},\n\n" .
                                  "¡Tenemos excelentes noticias!\n\n" .
                                  "Has alcanzado {$count} usos de promociones y has sido actualizado a la categoría:\n\n" .
                                  "🏆 " . strtoupper($nueva_categoria) . "\n\n" .
                                  "Ahora tendrás acceso a más beneficios y promociones exclusivas.\n\n" .
                                  "¡Gracias por ser parte de Ofertópolis!\n\n" .
                                  "Atentamente,\n" .
                                  "Equipo Ofertópolis";
                    
                    $mail->send();
                    $upgrade_message = "¡El cliente {$cliente_info['nombre']} ha sido promovido a {$nueva_categoria}!";
                } catch (Exception $e) {
                    $upgrade_message = "Categoría actualizada, pero no se pudo enviar el correo. Error: {$mail->ErrorInfo}";
                }
            }
            
            $success_message = "Solicitud aceptada exitosamente";
        } else {
            $success_message = "Solicitud rechazada";
        }
    }
}

// --- Contar solicitudes pendientes ---
$pendientes = $conn->query("SELECT COUNT(*) AS total 
                            FROM uso_promociones u
                            JOIN promociones p ON u.id_promo = p.id
                            JOIN locales l ON p.id_local = l.id
                            WHERE l.id_duenio = $idduenio AND u.estado = 'enviada'")->fetch_assoc();

// --- Listar solicitudes ---
$sql = "SELECT u.id, c.nombre AS cliente, p.titulo AS promo_titulo, p.descripcion, l.nombre, u.estado, u.fecha_uso 
        FROM uso_promociones u
        JOIN usuarios c ON u.id_cliente = c.id
        JOIN promociones p ON u.id_promo = p.id
        JOIN locales l ON p.id_local = l.id
        WHERE l.id_duenio = $idduenio
        ORDER BY u.id DESC";

$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Solicitudes de Promociones - OFERTÓPOLIS</title>
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
    <h3 style="color: var(--primary-color); font-weight: 700;">Solicitudes Recibidas</h3>
    <a href="duenio.php" class="btn btn-secondary">← Volver al panel</a>
  </div>

  <!-- Contador de solicitudes pendientes -->
  <div class="card shadow-sm mb-4">
    <div class="card-body" style="background: linear-gradient(135deg, rgba(113, 0, 20, 0.05), rgba(113, 0, 20, 0.02));">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h5 class="mb-0" style="color: var(--primary-color);">
            Total de solicitudes: <span class="badge bg-primary"><?= $res ? $res->num_rows : 0 ?></span>
          </h5>
        </div>
        <div class="col-md-6 text-md-end">
          <h5 class="mb-0" style="color: var(--primary-color);">
            Pendientes: <span class="badge bg-warning text-dark"><?= $pendientes['total'] ?></span>
          </h5>
        </div>
      </div>
    </div>
  </div>

  <?php if(isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $success_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if(isset($upgrade_message)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <strong> Upgrade de categoría:</strong> <?= $upgrade_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Tabla de solicitudes -->
  <div class="card shadow-sm">
    <div class="card-header text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
      <h5 class="mb-0">Lista de Solicitudes</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background-color: var(--light);">
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Promoción</th>
              <th>Local</th>
              <th>Fecha Solicitud</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
      <?php if($res && $res->num_rows > 0): ?>
        <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
          <td><strong>#<?= $row['id'] ?></strong></td>
          <td><?= htmlspecialchars($row['cliente']) ?></td>
          <td>
            <strong style="color: var(--primary-color);"><?= htmlspecialchars($row['promo_titulo']) ?></strong><br>
            <small class="text-muted"><?= htmlspecialchars(substr($row['descripcion'], 0, 50)) ?>...</small>
          </td>
          <td><?= htmlspecialchars($row['nombre']) ?></td>
          <td><?= $row['fecha_uso'] ? date('d/m/Y H:i', strtotime($row['fecha_uso'])) : 'N/A' ?></td>
          <td>
            <?php if($row['estado'] == 'enviada'): ?>
              <span class="badge bg-warning text-dark">⏳ Pendiente</span>
            <?php elseif($row['estado'] == 'aceptada'): ?>
              <span class="badge bg-success">✓ Aceptada</span>
            <?php else: ?>
              <span class="badge bg-danger">✗ Rechazada</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($row['estado'] == 'enviada'): ?>
              <a href="?accion=aceptada&id=<?= $row['id'] ?>" 
                 class="btn btn-success btn-sm mb-1"
                 onclick="return confirm('¿Aceptar esta solicitud? El cliente sumará puntos.')">
                ✓ Aceptar
              </a>
              <a href="?accion=rechazada&id=<?= $row['id'] ?>" 
                 class="btn btn-danger btn-sm mb-1"
                 onclick="return confirm('¿Rechazar esta solicitud?')">
                ✗ Rechazar
              </a>
            <?php else: ?>
              <span class="badge" style="background-color: var(--light); color: var(--dark);">Finalizada</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" class="text-center" style="padding: 3rem;">
            <div style="color: var(--primary-color); font-size: 3rem; margin-bottom: 1rem;">📭</div>
            <p class="text-muted mb-0">No hay solicitudes de promociones</p>
          </td>
        </tr>
      <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</main>

<?php include("../includes/footer.php"); ?>

</body>
</html>
