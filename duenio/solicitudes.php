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

                    $mail->addAddress($cliente_info['email'], $cliente_info['nombre']);
                    $mail->Subject = '🎉 ¡Felicitaciones! Has subido de categoría';
                    $mail->Body = "Hola {$cliente_info['nombre']},\n\n" .
                                  "¡Tenemos excelentes noticias! 🎊\n\n" .
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
            
            $success_message = "Solicitud aceptada exitosamente. El cliente ahora tiene $count usos.";
        } else {
            $success_message = "Solicitud rechazada.";
        }
    }
}

// --- Listar solicitudes ---
$sql = "SELECT u.id, c.nombre AS cliente, p.descripcion, l.nombre, u.estado 
        FROM uso_promociones u
        JOIN usuarios c ON u.id_cliente = c.id
        JOIN promociones p ON u.id_promo = p.id
        JOIN locales l ON p.id = l.id
        WHERE l.id_duenio = $idduenio
        ORDER BY u.id DESC";

$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Solicitudes de Promociones</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3 class="mb-3">Solicitudes Recibidas</h3>
  <a href="duenio.php" class="btn btn-secondary mb-3">Volver al panel</a>

  <?php if(isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $success_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if(isset($upgrade_message)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <strong>🎉 Upgrade de categoría:</strong> <?= $upgrade_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>Cliente</th>
        <th>Promoción</th>
        <th>Local</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $res->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['cliente']) ?></td>
        <td><?= htmlspecialchars($row['descripcion']) ?></td>
        <td><?= htmlspecialchars($row['nombre']) ?></td>
        <td>
          <?php if($row['estado'] == 'enviada'): ?>
            <span class="badge bg-warning">⏳ Pendiente</span>
          <?php elseif($row['estado'] == 'aceptada'): ?>
            <span class="badge bg-success">✅ Aceptada</span>
          <?php else: ?>
            <span class="badge bg-danger">❌ Rechazada</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($row['estado'] == 'enviada'): ?>
            <a href="?accion=aceptada&id=<?= $row['id'] ?>" 
               class="btn btn-success btn-sm"
               onclick="return confirm('¿Aceptar esta solicitud? El cliente sumará puntos.')">
              ✅ Aceptar
            </a>
            <a href="?accion=rechazada&id=<?= $row['id'] ?>" 
               class="btn btn-danger btn-sm"
               onclick="return confirm('¿Rechazar esta solicitud?')">
              ❌ Rechazar
            </a>
          <?php else: ?>
            <span class="text-muted">Finalizada</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
