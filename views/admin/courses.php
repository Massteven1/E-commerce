<?php
require_once __DIR__ . '/../../controllers/PlaylistController.php';
require_once __DIR__ . '/../../controllers/VideoController.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'admin';
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

$id = isset($_GET['id']) ? $_GET['id'] : null;

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
        if ($action === 'create') {
            $playlistController->create();
        } elseif ($action === 'edit' && $id) {
            $playlistController->edit($id);
        } elseif ($action === 'update') {
            $playlistController->update();
        } else {
            $playlistController->index();
        }
        break;
    case 'video':
        $videoController = new VideoController();
        if ($action === 'upload') {
            $videoController->upload();
        } elseif ($action === 'view_playlist') {
            $videoController->viewPlaylist($id);
        } elseif ($action === 'view_video') {
            $videoController->viewVideo($id);
        } elseif ($action === 'edit_video' && $id) { // Nuevo: Acción para editar video
            $videoController->editVideo($id);
        } elseif ($action === 'update_video') { // Nuevo: Acción para actualizar video
            $videoController->updateVideo();
        }
        break;
    default:
        header('Location: courses.php?controller=admin&action=dashboard');
        exit();
}
?>
