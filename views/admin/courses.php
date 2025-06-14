<?php
// Función simple para cargar controladores
function loadController($name) {
    $file = __DIR__ . "/../../controllers/{$name}Controller.php";
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
}

// Cargar todos los controladores necesarios
$controllers = ['Admin', 'Playlist', 'Video'];
foreach ($controllers as $controller) {
    if (!loadController($controller)) {
        die("Error: No se pudo cargar el controlador {$controller}");
    }
}

// Obtener parámetros
$controller = $_GET['controller'] ?? 'admin';
$action = $_GET['action'] ?? 'dashboard';
$id = $_GET['id'] ?? null;

// Enrutar
try {
    switch ($controller) {
        case 'admin':
            $adminController = new AdminController();
            if ($action === 'dashboard') {
                $adminController->dashboard();
            } else {
                header('Location: courses.php?controller=admin&action=dashboard');
                exit();
            }
            break;

        case 'playlist':
            $playlistController = new PlaylistController();
            switch ($action) {
                case 'create':
                    $playlistController->create();
                    break;
                case 'edit':
                    if ($id) $playlistController->edit($id);
                    break;
                case 'update':
                    $playlistController->update();
                    break;
                case 'delete':
                    if ($id) $playlistController->delete($id);
                    break;
                default:
                    $playlistController->index();
            }
            break;

        case 'video':
            $videoController = new VideoController();
            switch ($action) {
                case 'upload':
                    $videoController->upload();
                    break;
                case 'view_playlist':
                    if ($id) $videoController->viewPlaylist($id);
                    break;
                case 'view_video':
                    if ($id) $videoController->viewVideo($id);
                    break;
                case 'edit_video':
                    if ($id) $videoController->editVideo($id);
                    break;
                case 'update_video':
                    $videoController->updateVideo();
                    break;
                case 'delete_video':
                    if ($id) $videoController->deleteVideo($id);
                    break;
            }
            break;

        default:
            header('Location: courses.php?controller=admin&action=dashboard');
            exit();
    }
} catch (Exception $e) {
    die("Error en la aplicación: " . $e->getMessage());
}
?>
