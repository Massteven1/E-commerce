<?php
require_once 'controllers/PlaylistController.php';
require_once 'controllers/VideoController.php';

$controller = isset($_GET['controller']) ? $_GET['controller'] : 'playlist';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($controller) {
    case 'playlist':
        $playlistController = new PlaylistController();
        if ($action === 'create') {
            $playlistController->create();
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
        }
        break;
    default:
        header('Location: index.php?controller=playlist&action=index');
        exit();
}
?>