<?php
if (session_status() == PHP_SESSION_NONE) {
session_start();
}

// Incluir archivos necesarios para las funcionalidades de administración
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/Video.php';
require_once __DIR__ . '/../../controllers/PlaylistController.php';
require_once __DIR__ . '/../../controllers/VideoController.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

$controller = $_GET['controller'] ?? 'playlist'; // Por defecto al controlador de playlist
$action = $_GET['action'] ?? 'index'; // Por defecto a la acción de index
$id = $_GET['id'] ?? null;

// Instanciar controladores según la solicitud
switch ($controller) {
case 'playlist':
    $playlistController = new PlaylistController();
    if ($action === 'index') {
        // Si la acción es 'index', cargar la vista principal de gestión de cursos
        $playlistController->index(); // El método index() ya incluye la vista
    } elseif (method_exists($playlistController, $action)) {
        if ($id) {
            $playlistController->$action($id);
        } else {
            $playlistController->$action();
        }
    } else {
        // Manejar acción inválida para el controlador de playlist
        header('Location: courses.php?controller=playlist&action=index');
        exit();
    }
    break;
case 'video':
    $videoController = new VideoController();
    if ($action === 'view_playlist') {
        // Si la acción es 'view_playlist', cargar la vista de videos de una playlist
        if ($id) {
            $videoController->viewPlaylist($id); // El método viewPlaylist() ya incluye la vista
        } else {
            header('Location: courses.php?controller=playlist&action=index'); // Redirigir si no hay ID
            exit();
        }
    } elseif ($action === 'view_video') {
        if ($id) {
            $videoController->viewVideo($id);
        } else {
            // Si no hay ID para ver el video, redirigir al índice de playlist
            header('Location: courses.php?controller=playlist&action=index');
            exit();
        }
    } elseif (method_exists($videoController, $action)) {
        if ($id) {
            $videoController->$action($id);
        } else {
            $videoController->$action();
        }
    } else {
        // Manejar acción inválida para el controlador de video
        header('Location: courses.php?controller=playlist&action=index'); // Redirigir de vuelta al índice de playlist
        exit();
    }
    break;
case 'admin': // Este caso es para el dashboard, aunque el router principal ya lo maneja
    $adminController = new AdminController();
    if ($action === 'dashboard') {
        $adminController->dashboard(); // El método dashboard() ya incluye la vista
    } else {
        header('Location: courses.php?controller=playlist&action=index');
        exit();
    }
    break;
default:
    // Redirigir al índice de playlist si el controlador no es reconocido
    header('Location: courses.php?controller=playlist&action=index');
    exit();
}
?>
