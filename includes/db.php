<?php

$host = 'localhost';
$user = 'serenita';
$pass = 'serenita'; 
$db   = 'ofertopolis';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
