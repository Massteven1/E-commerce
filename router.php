<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Playlist.php';
require_once __DIR__ . '/controllers/PlaylistController.php';
require_once __DIR__ . '/controllers/CartController.php';

$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);

switch ($controller) {
    case 'home':
        $playlists = $playlistModel->readAll();
        require_once __DIR__ . '/views/client/home.php';
        break;
        
    case 'playlist':
        $playlistController = new PlaylistController();
        if ($action === 'view_detail' && $id) {
            $playlistController->viewClientDetail($id);
        } else {
            header('Location: home.php');
            exit();
        }
        break;
        
    case 'cart':
        $cartController = new CartController();
        switch ($action) {
            case 'add':
                if ($id) $cartController->add($id);
                break;
            case 'remove':
                if ($id) $cartController->remove($id);
                break;
            case 'view':
                $cartController->view();
                break;
            case 'checkout':
                $cartController->checkout();
                break;
            case 'apply_promo':
                if (isset($_POST['promo_code'])) {
                    $cartController->applyPromoCode($_POST['promo_code']);
                } else {
                    $cartController->view();
                }
                break;
            default:
                $cartController->view();
        }
        break;
        
    default:
        $playlists = $playlistModel->readAll();
        require_once __DIR__ . '/views/client/home.php';
}
?>
