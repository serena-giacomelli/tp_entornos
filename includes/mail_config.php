<?php
/**
 * Configuración centralizada de emails
 * Ofertópolis - Sistema de notificaciones
 */

function configurarMail($mail) {
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        // DESARROLLO LOCAL: MailHog (servidor de prueba)
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->setFrom('no-reply@ofertopolis.com', 'Ofertópolis');
    } else {
        // PRODUCCIÓN: Gmail con contraseña de aplicación
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lusechi3@gmail.com';
        // IMPORTANTE: Usa una contraseña de aplicación, no tu contraseña normal
        // Genera una en: https://myaccount.google.com/apppasswords
        $mail->Password = 'xxxx xxxx xxxx xxxx'; // Reemplaza con tu contraseña de aplicación
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('lusechi3@gmail.com', 'Ofertópolis');
        
        // Configuración adicional para servidores gratuitos
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    }
}
?>
