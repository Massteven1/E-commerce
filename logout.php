<?php
require_once 'controllers/AuthController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$authController = new AuthController();
$authController->logout();

// Redirigir al usuario a la página de inicio de sesión o a la página principal
header('Location: login.php');
exit();
?>
