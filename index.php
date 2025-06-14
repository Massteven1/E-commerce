<?php
// Iniciar la sesión al principio de todo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir las clases necesarias para la base de datos y los modelos
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Playlist.php';
require_once __DIR__ . '/controllers/CartController.php';

// Función para cargar controladores
function loadController($name) {
    $file = __DIR__ . "/controllers/{$name}Controller.php";
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
}

// Obtener el controlador y la acción de la URL
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

$id = $_GET['id'] ?? null;
$param = $_GET['param'] ?? null;

// Iniciar el buffer de salida para capturar el contenido de la vista
ob_start();

// Enrutamiento
try {
    switch ($controller) {
        case 'home':
            $database = new Database();
            $db = $database->getConnection();
            $playlistModel = new Playlist($db);
            $playlists = $playlistModel->readAll();
            require_once __DIR__ . '/views/client/home.php'; // Carga la nueva vista de inicio
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
                case 'apply_promo':
                    $cartController->applyPromoCode($_POST['promo_code'] ?? '');
                    break;
                case 'checkout':
                    $cartController->checkout();
                    break;
                case 'view':
                    $cartController->view();
                    break;
                default:
                    $cartController->view();
            }
            break;

        default:
            // Redirigir a la página principal si el controlador no es reconocido
            header('Location: index.php');
            exit();
    }
} catch (Exception $e) {
    // Manejo de errores general
    die("Error en la aplicación: " . $e->getMessage());
}

// Capturar el contenido de la vista
$view_content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Profesor Hernan</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Firebase Scripts -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <style>
        /* Estilo para el badge del carrito */
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: var(--red-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7em;
            font-weight: bold;
            line-height: 1;
            min-width: 18px;
            text-align: center;
        }
        .cart {
            position: relative;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-circle">
                    <img src="img/logo-profe-hernan.png" alt="Logo">
                </div>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Buscar">
                <i class="fas fa-search"></i>
            </div>
            <nav>
                <ul>
                    <li><a href="login.html">login</a></li>
                    <li><a href="index.php">Cursos</a></li>
                    <li><a href="#">Sales</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
                <div class="cart">
                    <a href="index.php?controller=cart&action=view"><i class="fas fa-shopping-cart"></i></a>
                    <?php 
                        $cart_item_count = count($_SESSION['cart'] ?? []);
                        if ($cart_item_count > 0) {
                            echo '<span class="cart-badge">' . $cart_item_count . '</span>';
                        }
                    ?>
                </div>
                <div class="logout" id="logoutBtn">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <?php echo $view_content; ?>

    <!-- Back to Top Button -->
    <div class="back-to-top">
        <a href="#"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>
