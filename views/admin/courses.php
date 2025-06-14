<?php
session_start();

// Verificar si es admin
$isAdmin = false;
if (isset($_SESSION['user_email'])) {
    $adminEmails = ['admin@ecommerce.com', 'admin@elprofehernan.com'];
    $isAdmin = in_array($_SESSION['user_email'], $adminEmails);
}

if (!$isAdmin) {
    header('Location: ../../login.html');
    exit();
}

// Cargar controladores necesarios
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../controllers/PlaylistController.php';

// Enrutamiento básico
$controller = $_GET['controller'] ?? 'admin';
$action = $_GET['action'] ?? 'dashboard';

if ($controller === 'admin' && $action === 'dashboard') {
    require_once __DIR__ . '/dashboard.php';
} else {
    // Redirigir al dashboard por defecto
    header('Location: courses.php?controller=admin&action=dashboard');
    exit();
}
?>
