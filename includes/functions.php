<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function isDuenio() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'duenio';
}

function isCliente() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente';
}

function redirect($url) {
    header("Location: $url");
    exit;
}
?>
