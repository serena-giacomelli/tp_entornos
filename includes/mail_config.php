<?php
/**
 * Configuración centralizada de emails usando variables de entorno
 * Ofertópolis - Sistema de notificaciones
 */

// Cargar variables de entorno desde .env
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env_vars = parse_ini_file($env_file);
    foreach ($env_vars as $key => $value) {
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
    }
}

function configurarMail($mail) {
    // PRODUCCIÓN: SendGrid SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.sendgrid.net';
    $mail->SMTPAuth = true;
    $mail->Username = 'apikey';
    $mail->Password = $_ENV['SENDGRID_API_KEY'] ?? '';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    $from_email = $_ENV['SMTP_FROM_EMAIL'] ?? 'no-reply@ofertopolis.com';
    $from_name = $_ENV['SMTP_FROM_NAME'] ?? 'Ofertópolis';
    $mail->setFrom($from_email, $from_name);
    
    // Configuración adicional para compatibilidad
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
}
?>
