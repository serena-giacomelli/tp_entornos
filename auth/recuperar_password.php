<?php
session_start();
include_once("../includes/db.php");
require_once("../includes/mail_config.php");
use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    // Verificar si el email existe
    $check = $conn->query("SELECT * FROM usuarios WHERE email='$email'");
    
    if ($check->num_rows > 0) {
        $usuario = $check->fetch_assoc();
        
        // Generar token único
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token válido por 1 hora
        
        // Guardar token en la base de datos
        $sql = "UPDATE usuarios SET token_validacion='$token' WHERE email='$email'";
        
        if ($conn->query($sql)) {
            // Enviar email con el enlace de recuperación
            $mail = new PHPMailer(true);
            
            try {
                configurarMail($mail);
                
                $mail->setFrom('noreply@ofertopolis.com', 'Ofertópolis');
                $mail->addAddress($email, $usuario['nombre']);
                
                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de Contraseña - Ofertópolis';
                
                $enlace_recuperacion = "http://" . $_SERVER['HTTP_HOST'] . "/auth/restablecer_password.php?token=$token";
                
                $mail->Body = "
                    <h2>Recuperación de Contraseña</h2>
                    <p>Hola {$usuario['nombre']},</p>
                    <p>Recibimos una solicitud para restablecer tu contraseña en Ofertópolis.</p>
                    <p>Hacé clic en el siguiente enlace para crear una nueva contraseña:</p>
                    <p><a href='$enlace_recuperacion' style='background-color: #710014; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Restablecer Contraseña</a></p>
                    <p>Este enlace expirará en 1 hora.</p>
                    <p>Si no solicitaste este cambio, podés ignorar este email.</p>
                    <hr>
                    <p style='font-size: 12px; color: #666;'>Ofertópolis - Tu shopping de confianza</p>
                ";
                
                $mail->send();
                $mensajeOK = "Se ha enviado un correo con las instrucciones para recuperar tu contraseña.";
            } catch (Exception $e) {
                $error = "Error al enviar el correo: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Error al procesar la solicitud. Intenta nuevamente.";
        }
    } else {
        // Por seguridad, mostramos el mismo mensaje aunque el email no exista
        $mensajeOK = "Si el email existe en nuestro sistema, recibirás las instrucciones para recuperar tu contraseña.";
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
