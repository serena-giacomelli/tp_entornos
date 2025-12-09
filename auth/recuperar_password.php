<?php
session_start();
include_once("../includes/db.php");
require_once("../includes/mail_config.php");
use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        // Verificar si el email existe usando prepared statement
        $stmt = $conn->prepare("SELECT id, nombre, email FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            
            // Guardar token en la base de datos usando prepared statement
            $stmt_update = $conn->prepare("UPDATE usuarios SET token_validacion = ? WHERE email = ?");
            $stmt_update->bind_param("ss", $token, $email);
            
            if ($stmt_update->execute()) {
                // Enviar email con el enlace de recuperación
                $mail = new PHPMailer(true);
                
                try {
                    configurarMail($mail);
                    
                    $mail->addAddress($email, $usuario['nombre']);
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperación de Contraseña - Ofertópolis';
                    
                    // Detectar protocolo correcto
                    $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $enlace_recuperacion = $protocolo . "://" . $_SERVER['HTTP_HOST'] . "/auth/restablecer_password.php?token=$token";
                    
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #710014;'>Recuperación de Contraseña</h2>
                            <p>Hola <strong>{$usuario['nombre']}</strong>,</p>
                            <p>Recibimos una solicitud para restablecer tu contraseña en Ofertópolis.</p>
                            <p>Hacé clic en el siguiente botón para crear una nueva contraseña:</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='$enlace_recuperacion' style='background-color: #710014; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Restablecer Contraseña</a>
                            </div>
                            <p style='color: #666; font-size: 14px;'>O copia y pega este enlace en tu navegador:</p>
                            <p style='color: #0066cc; font-size: 12px; word-break: break-all;'>$enlace_recuperacion</p>
                            <p style='color: #d9534f; font-weight: bold;'>Este enlace es válido por 1 hora.</p>
                            <p>Si no solicitaste este cambio, podés ignorar este email.</p>
                            <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                            <p style='font-size: 12px; color: #666; text-align: center;'>Ofertópolis - Tu shopping de confianza</p>
                        </div>
                    ";
                    
                    $mail->send();
                    $mensajeOK = "Se ha enviado un correo con las instrucciones para recuperar tu contraseña. Revisá tu bandeja de entrada.";
                } catch (Exception $e) {
                    $error = "Error al enviar el correo. Por favor, intenta nuevamente más tarde. Detalle: " . $mail->ErrorInfo;
                }
            } else {
                $error = "Error al procesar la solicitud. Intenta nuevamente.";
            }
            
            $stmt_update->close();
        } else {
            // Por seguridad, mostramos el mismo mensaje aunque el email no exista
            $mensajeOK = "Si el email existe en nuestro sistema, recibirás las instrucciones para recuperar tu contraseña.";
        }
        
        $stmt->close();
    } else {
        $error = "Por favor, ingresa un email válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recuperar Contraseña - Ofertópolis</title>
<link rel="stylesheet" href="../css/estilos.css">
<link rel="stylesheet" href="../css/forms.css">
<link rel="stylesheet" href="../css/utilities.css">
</head>
<body>
<div class="form-container">
  <div class="form-card">
    <div class="form-header">
      <h1 class="form-title">Recuperar Contraseña</h1>
      <p class="form-subtitle">Te enviaremos un enlace para restablecer tu contraseña</p>
    </div>

    <?php if(isset($mensajeOK)): ?>
      <div class="form-alert form-alert-success"><?= $mensajeOK ?></div>
      <div style="text-align: center; margin-top: 20px;">
        <a href="login.php" class="btn-primary-custom" style="text-decoration: none; display: inline-block; padding: 12px 24px;">Volver al Login</a>
      </div>
    <?php else: ?>
      
      <?php if(isset($error)): ?>
        <div class="form-alert form-alert-error"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label-custom">Email</label>
          <input type="email" name="email" class="form-control-custom" placeholder="tu@email.com" required>
        </div>
        
        <div class="form-button-group">
          <button type="submit" class="btn-primary-custom" style="width: 100%;">Enviar Enlace de Recuperación</button>
          <a href="login.php" class="btn-outline-custom" style="width: 100%; text-align: center; text-decoration: none; display: inline-block;">← Volver al Login</a>
        </div>
      </form>

    <?php endif; ?>

    <div class="form-footer">
      <p class="form-footer-text">¿Recordaste tu contraseña? <a href="login.php" class="form-footer-link">Iniciá sesión</a></p>
    </div>
  </div>
</div>
</body>
</html>
