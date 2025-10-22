<?php
session_start();
include_once("../includes/db.php");

// Cargar PHPMailer con Composer
require '../vendor/autoload.php'; // <-- aquí carga todo automáticamente
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

            $destinatario = $email;
            $asunto = "Validación de registro - Shopping Promociones";
            $mensaje = "Hola $nombre,\n\nGracias por registrarte en Shopping Promociones.\n
            Para activar tu cuenta hacé clic en el siguiente enlace:\n
            http://localhost/tp_eg/auth/validar_email.php?token=$token\n\n
            Atentamente,\nEl equipo del Shopping.";

            $mail = new PHPMailer(true);

            try {
                // Detectar entorno
                if ($_SERVER['SERVER_NAME'] == 'localhost') {
                    // Local: MailHog
                    $mail->isSMTP();
                    $mail->Host = 'localhost';
                    $mail->Port = 1025;
                    $mail->SMTPAuth = false;
                    $mail->setFrom('no-reply@shoppingpromos.com', 'Shopping Promociones');
                } else {
                    // Producción: SMTP real
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'tuemail@gmail.com';
                    $mail->Password = 'tu_clave_de_aplicacion';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    $mail->setFrom('tuemail@gmail.com', 'Shopping Promociones');
                }

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
<title>Registro de Cliente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 col-md-6">
  <h3 class="mb-3 text-center">Registro de Cliente</h3>
  <?php if(isset($mensajeOK)): ?><div class="alert alert-success"><?= $mensajeOK ?></div><?php endif; ?>
  <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label>Nombre completo</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Contraseña</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Registrarme</button>
  </form>
  <p class="mt-3 text-center"><a href="login.php">Ya tengo una cuenta</a></p>
</div>
</body>
</html>
