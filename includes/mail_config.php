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
        // PRODUCCIÓN: Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lusechi3@gmail.com';
        $mail->Password = 'LasMasLindas';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('lusechi3@gmail.com', 'Ofertópolis');
    }
}
?>
