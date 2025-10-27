<?php
include_once("includes/db.php");
include_once("includes/mail_config.php");

// Cargar PHPMailer
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nombre = trim($_POST['nombre']);
  $email = trim($_POST['email']);
  $mensaje = trim($_POST['mensaje']);

  if ($nombre && $email && $mensaje) {
    $sql = "INSERT INTO contactos (nombre, email, mensaje) VALUES ('$nombre', '$email', '$mensaje')";
    if ($conn->query($sql)) {
      
      // Enviar email al administrador
      $mail = new PHPMailer(true);
      
      try {
        configurarMail($mail);
        
        // Email del administrador (puedes cambiarlo)
        $admin_email = "lusechi3@gmail.com";
        
        $mail->addAddress($admin_email, 'Administrador Ofertópolis');
        $mail->addReplyTo($email, $nombre);
        
        $mail->Subject = "Nuevo mensaje de contacto - Ofertópolis";
        $mail->Body = "Has recibido un nuevo mensaje de contacto:\n\n" .
                      "Nombre: $nombre\n" .
                      "Email: $email\n" .
                      "Mensaje:\n$mensaje\n\n" .
                      "---\n" .
                      "Este mensaje fue enviado desde el formulario de contacto de Ofertópolis.";
        
        $mail->send();
        $ok = "Mensaje enviado correctamente. El administrador responderá a la brevedad.";
        
      } catch (Exception $e) {
        $ok = "Mensaje guardado, pero no se pudo enviar la notificación por email. Error: {$mail->ErrorInfo}";
      }
      
    } else {
      $error = "Error al guardar mensaje: " . $conn->error;
    }
  } else {
    $error = "Todos los campos son obligatorios.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contacto - Ofertópolis</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/estilos.css" rel="stylesheet">
  <link href="css/header.css" rel="stylesheet">
  <link href="css/footer.css" rel="stylesheet">
  <link href="css/forms.css" rel="stylesheet">
  <style>
    .contact-container {
      max-width: 600px;
      margin: 0 auto;
      padding: 2rem 1rem;
    }
    
    .contact-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    
    .contact-header {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      padding: 2rem;
      text-align: center;
    }
    
    .contact-header h1 {
      font-size: 1.75rem;
      font-weight: 700;
      margin: 0 0 0.5rem 0;
    }
    
    .contact-header p {
      margin: 0;
      opacity: 0.9;
      font-size: 0.95rem;
    }
    
    .contact-body {
      padding: 2rem;
    }
    
    .form-label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 0.5rem;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(113, 0, 20, 0.15);
    }
    
    .contact-info {
      background: var(--light);
      border-left: 4px solid var(--primary-color);
      padding: 1rem;
      border-radius: 8px;
      margin-top: 1.5rem;
    }
    
    .contact-info-item {
      display: flex;
      align-items: center;
      margin-bottom: 0.75rem;
    }
    
    .contact-info-item:last-child {
      margin-bottom: 0;
    }
    
    .contact-info-icon {
      width: 24px;
      margin-right: 0.75rem;
      font-size: 1.2rem;
    }
  </style>
</head>
<body>

<?php include("includes/header.php"); ?>

<main id="main-content" class="main-content">
  <div class="contact-container">
    <div class="contact-card">
      <div class="contact-header">
        <h1>Formulario de Contacto</h1>
        <p>¿Tenés alguna consulta? Escribinos y te responderemos a la brevedad</p>
      </div>

      <div class="contact-body">

      <div class="contact-body">
        <?php if(isset($ok)): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Éxito!</strong> <?= $ok ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre completo</label>
            <input type="text" 
                   id="nombre" 
                   name="nombre" 
                   class="form-control" 
                   placeholder="Ingresa tu nombre"
                   required>
          </div>
          
          <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control" 
                   placeholder="tu@email.com"
                   required>
          </div>
          
          <div class="mb-3">
            <label for="mensaje" class="form-label">Mensaje</label>
            <textarea id="mensaje" 
                      name="mensaje" 
                      class="form-control" 
                      rows="5" 
                      placeholder="Escribe tu consulta aquí..."
                      required></textarea>
          </div>
          
          <button type="submit" class="btn btn-primary w-100 mb-3">
            Enviar Mensaje
          </button>
          
          <a href="index.php" class="btn btn-secondary w-100">
            Volver al Inicio
          </a>
        </form>
      </div>
    </div>
  </div>
</main>

<?php include("includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
