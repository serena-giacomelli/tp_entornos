<?php
session_start();
include_once("../includes/db.php");
include_once("../includes/mail_config.php");

// Cargar PHPMailer con Composer
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    $nombre = trim($_POST['nombre']);
    $token = bin2hex(random_bytes(16));

    $check = $conn->query("SELECT * FROM usuarios WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Ya existe un usuario con ese email.";
    } else {
        $sql = "INSERT INTO usuarios (nombre, email, password, rol, categoria, estado_cuenta, token_validacion)
                VALUES ('$nombre', '$email', '$password', 'cliente', 'inicial', 'pendiente', '$token')";
        if ($conn->query($sql)) {

            // Generar URL de validación según el entorno
            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $dominio = $_SERVER['HTTP_HOST'];
            $url_validacion = "$protocolo://$dominio/auth/validad_email.php?token=$token";

            $destinatario = $email;
            $asunto = "Validación de registro - Shopping Promociones";
            $mensaje = "Hola $nombre,\n\nGracias por registrarte en Shopping Promociones.\n
            Para activar tu cuenta hacé clic en el siguiente enlace:\n
            $url_validacion\n\n
            Atentamente,\nEl equipo del Shopping.";

            $mail = new PHPMailer(true);

            try {
                // Usar configuración centralizada
                configurarMail($mail);

                $mail->addAddress($destinatario, $nombre);
                $mail->Subject = $asunto;
                $mail->Body    = $mensaje;
                $mail->send();

                $mensajeOK = "Registro exitoso. Revisa tu correo para validar la cuenta.";
            } catch (Exception $e) {
                $mensajeOK = "Cuenta creada, pero no se pudo enviar el correo. Error: {$mail->ErrorInfo}";
            }

        } else {
            $error = "Error al registrar usuario: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Cliente - Ofertópolis</title>
<link rel="stylesheet" href="../css/estilos.css">
<link rel="stylesheet" href="../css/forms.css">
<link rel="stylesheet" href="../css/utilities.css">
</head>
<body>
<div class="form-container">
  <div class="form-card">
    <div class="form-header">
      <h1 class="form-title">Registro de Cliente</h1>
      <p class="form-subtitle">Únete a Ofertópolis y disfruta de las mejores promociones</p>
    </div>

    <?php if(isset($mensajeOK)): ?>
      <div class="form-alert form-alert-success"><?= $mensajeOK ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
      <div class="form-alert form-alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label-custom">Nombre completo</label>
        <input type="text" name="nombre" class="form-control-custom" placeholder="Ingresá tu nombre completo" required>
      </div>
      
      <div class="form-group">
        <label class="form-label-custom">Email</label>
        <input type="email" name="email" class="form-control-custom" placeholder="tu@email.com" required>
      </div>
      
      <div class="form-group">
        <label class="form-label-custom">Contraseña</label>
        <input type="password" name="password" class="form-control-custom" placeholder="Mínimo 6 caracteres" required>
      </div>
      
      <div class="form-button-group">
        <button type="submit" class="btn-primary-custom" style="width: 100%;">Registrarme</button>
        <a href="../index.php" class="btn-outline-custom" style="width: 100%; text-align: center; text-decoration: none; display: inline-block;">← Volver al Inicio</a>
      </div>
    </form>

    <div class="form-footer">
      <p class="form-footer-text">¿Ya tenés una cuenta? <a href="login.php" class="form-footer-link">Iniciá sesión</a></p>
    </div>
  </div>
</div>
</body>
</html>
