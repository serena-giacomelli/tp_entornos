<?php
session_start();
include_once("../includes/db.php");

$token_valido = false;
$usuario_id = null;

// Verificar si el token es válido
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $check = $conn->query("SELECT id, nombre, email FROM usuarios WHERE token_validacion='$token'");
    
    if ($check->num_rows > 0) {
        $token_valido = true;
        $usuario = $check->fetch_assoc();
        $usuario_id = $usuario['id'];
    } else {
        $error = "El enlace de recuperación es inválido o ha expirado.";
    }
}

// Procesar el cambio de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    $token = $_POST['token'];
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Verificar token nuevamente
        $check = $conn->query("SELECT id FROM usuarios WHERE token_validacion='$token'");
        
        if ($check->num_rows > 0) {
            $usuario = $check->fetch_assoc();
            $password_hash = md5($password);
            
            // Actualizar contraseña y eliminar token
            $sql = "UPDATE usuarios SET password='$password_hash', token_validacion=NULL WHERE id={$usuario['id']}";
            
            if ($conn->query($sql)) {
                $mensajeOK = "Tu contraseña ha sido actualizada exitosamente. Ya podés iniciar sesión.";
                $token_valido = false;
            } else {
                $error = "Error al actualizar la contraseña. Intenta nuevamente.";
            }
        } else {
            $error = "El enlace de recuperación es inválido o ha expirado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Restablecer Contraseña - Ofertópolis</title>
<link rel="stylesheet" href="../css/estilos.css">
<link rel="stylesheet" href="../css/forms.css">
<link rel="stylesheet" href="../css/utilities.css">
</head>
<body>
<div class="form-container">
  <div class="form-card">
    <div class="form-header">
      <h1 class="form-title">Restablecer Contraseña</h1>
      <p class="form-subtitle">Ingresá tu nueva contraseña</p>
    </div>

    <?php if(isset($mensajeOK)): ?>
      <div class="form-alert form-alert-success"><?= $mensajeOK ?></div>
      <div style="text-align: center; margin-top: 20px;">
        <a href="login.php" class="btn-primary-custom" style="text-decoration: none; display: inline-block; padding: 12px 24px;">Ir al Login</a>
      </div>
    <?php elseif(!$token_valido): ?>
      <div class="form-alert form-alert-error">
        <?= isset($error) ? $error : "El enlace de recuperación es inválido o ha expirado." ?>
      </div>
      <div style="text-align: center; margin-top: 20px;">
        <a href="recuperar_password.php" class="btn-primary-custom" style="text-decoration: none; display: inline-block; padding: 12px 24px;">Solicitar Nuevo Enlace</a>
      </div>
    <?php else: ?>
      
      <?php if(isset($error)): ?>
        <div class="form-alert form-alert-error"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
        
        <div class="form-group">
          <label class="form-label-custom">Nueva Contraseña</label>
          <div class="password-wrapper">
            <input type="password" name="password" id="new-password" class="form-control-custom" placeholder="Mínimo 6 caracteres" required>
            <button type="button" class="password-toggle" onclick="togglePassword('new-password', this)" aria-label="Mostrar/ocultar contraseña">
              👁️
            </button>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label-custom">Confirmar Contraseña</label>
          <div class="password-wrapper">
            <input type="password" name="confirm_password" id="confirm-password" class="form-control-custom" placeholder="Repetí tu contraseña" required>
            <button type="button" class="password-toggle" onclick="togglePassword('confirm-password', this)" aria-label="Mostrar/ocultar contraseña">
              👁️
            </button>
          </div>
        </div>
        
        <div class="form-button-group">
          <button type="submit" class="btn-primary-custom" style="width: 100%;">Cambiar Contraseña</button>
          <a href="login.php" class="btn-outline-custom" style="width: 100%; text-align: center; text-decoration: none; display: inline-block;">← Volver al Login</a>
        </div>
      </form>

    <?php endif; ?>
  </div>
</div>

<script>
function togglePassword(inputId, button) {
  const passwordInput = document.getElementById(inputId);
  const isPassword = passwordInput.type === 'password';
  
  passwordInput.type = isPassword ? 'text' : 'password';
  button.textContent = isPassword ? '🙈' : '👁️';
  button.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
}
</script>
</body>
</html>
