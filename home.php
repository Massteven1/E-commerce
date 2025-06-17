<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir el router para manejar las rutas
require_once __DIR__ . '/router.php';
?>
