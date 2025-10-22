<?php
$to = "cliente@dominio.com";
$subject = "Prueba MailHog";
$message = "Hola, este es un correo de prueba desde XAMPP y MailHog";
$headers = "From: no-reply@shoppingpromos.com\r\n";

if(mail($to, $subject, $message, $headers)){
    echo "Correo enviado!";
} else {
    echo "Error al enviar";
}
?>
