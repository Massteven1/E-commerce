<?php
// Asegúrate de que la sesión esté iniciada si planeas usarla para el logout
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

require_once __DIR__ . '/../../controllers/PlaylistController.php';
require_once __DIR__ . '/../../controllers/VideoController.php';
require_once __DIR__ . '/../../controllers/AdminController.php'; // Nuevo: Incluye el AdminController

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'admin'; // Cambiado: 'admin' es el controlador por defecto
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard'; // Cambiado: 'dashboard' es la acción por defecto

$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($controller) {
    case 'admin': // Nuevo caso para el controlador de administración
        $adminController = new AdminController();
        if ($action === 'dashboard') {
            $adminController->dashboard();
        } else {
            // Si la acción no es 'dashboard', redirige al dashboard por defecto
            header('Location: courses.php?controller=admin&action=dashboard');
            exit();
        }
        break;
    case 'playlist':
        $playlistController = new PlaylistController();
        if ($action === 'create') {
            $playlistController->create();
        } else {
            $playlistController->index(); // Esto incluirá views/admin/index.php
        }
        break;
    case 'video':
        $videoController = new VideoController();
        if ($action === 'upload') {
            $videoController->upload();
        } elseif ($action === 'view_playlist') {
            $videoController->viewPlaylist($id); // Esto incluirá views/admin/view_playlist.php
        }
        break;
    default:
        // Redirige al dashboard si el controlador no es válido
        header('Location: courses.php?controller=admin&action=dashboard');
        exit();
}
?>
