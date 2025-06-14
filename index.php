<?php
// Iniciar la sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir clases necesarias
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Playlist.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/PlaylistController.php';

// Obtener parámetros
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Buffer de salida
ob_start();

// Enrutamiento
try {
    switch ($controller) {
        case 'home':
            $database = new Database();
            $db = $database->getConnection();
            $playlistModel = new Playlist($db);
            $playlists = $playlistModel->readAll();
            require_once __DIR__ . '/views/client/home.php';
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
        
        case 'playlist':
            $playlistController = new PlaylistController();
            switch ($action) {
                case 'view_detail':
                    if ($id) $playlistController->viewClientDetail($id);
                    break;
                default:
                    header('Location: index.php');
                    exit();
            }
            break;

        default:
            header('Location: index.php');
            exit();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$view_content = ob_get_clean();

// Determinar si es admin
$isAdmin = false;
if (isset($_SESSION['user_email'])) {
    $adminEmails = ['admin@ecommerce.com', 'admin@elprofehernan.com'];
    $isAdmin = in_array($_SESSION['user_email'], $adminEmails);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Profesor Hernan</title>
    <!-- CSS Files -->
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/course-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header -->
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
                    <li><a href="index.php">Cursos</a></li>
                    <li><a href="#">Sales</a></li>
                    <li><a href="#">Contact</a></li>
                    <?php if ($isAdmin): ?>
                        <li><a href="views/admin/courses.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
                <div class="cart">
                    <a href="index.php?controller=cart&action=view"><i class="fas fa-shopping-cart"></i></a>
                    <?php 
                        $cart_count = count($_SESSION['cart'] ?? []);
                        if ($cart_count > 0): 
                    ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Auth Links -->
                <?php if (isset($_SESSION['firebase_uid'])): ?>
                    <div class="user-info">
                        <span><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
                        <div class="logout" id="logoutBtn">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-links">
                        <a href="login.html" class="btn-login">Login</a>
                        <a href="signup.html" class="btn-signup">Registro</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <?php echo $view_content; ?>

    <!-- Back to Top -->
    <div class="back-to-top">
        <a href="#"><i class="fas fa-arrow-up"></i></a>
    </div>

    <!-- Firebase Scripts -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="auth/firebase-config.js"></script>
    <script src="auth/auth.js"></script>
</body>
</html>

<style>
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
    min-width: 18px;
    text-align: center;
}
.cart { position: relative; }
.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-color);
}
.logout {
    cursor: pointer;
    padding: 5px;
    border-radius: 3px;
    transition: background-color 0.3s;
}
.logout:hover {
    background-color: var(--light-gray);
}
</style>
