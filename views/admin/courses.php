<?php
require_once __DIR__ . '/../../controllers/PlaylistController.php';
require_once __DIR__ . '/../../controllers/VideoController.php';

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
        header('Location: courses.php?controller=playlist&action=index');
        exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--     <link rel="stylesheet" href="../../public/css/product.css"> -->

    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .container { width: 80%; margin: 0 auto; padding: 20px; }
        header { background: #333; color: white; padding: 10px 0; }
        .logo { display: flex; align-items: center; }
        .logo-circle { width: 40px; height: 40px; background: #007bff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; }
        nav ul { list-style: none; display: flex; gap: 20px; }
        nav a { color: white; text-decoration: none; }
        .banner { background: #f4f4f4; padding: 20px 0; }
        .checkout-section { margin: 20px 0; }
        .form-row { display: flex; flex-direction: column; gap: 10px; }
        input, textarea, select { padding: 10px; font-size: 16px; }
        .btn-primary { background: #007bff; color: white; padding: 10px; border: none; cursor: pointer; }
        .products-grid { list-style: none; padding: 0; display: grid; gap: 20px; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
        .product-card { border: 1px solid #ddd; padding: 10px; }
        .product-tumb img { max-width: 100%; height: auto; }
        .product-details { padding: 10px 0; }
        .product-catagory { color: #6c757d; font-size: 14px; }
        .product-details h4 a { color: #333; text-decoration: none; }
        .product-details p { font-size: 14px; color: #666; }
        .product-bottom-details { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
        .product-price { font-weight: bold; }
        .product-price small { color: #888; text-decoration: line-through; margin-right: 5px; }
        .product-links a { color: #333; margin-left: 10px; text-decoration: none; }
    </style>
</head>
<body>
    
</body>
</html>