<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/UserCourse.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

if (!AuthController::isAuthenticated()) {
    AuthController::setFlashMessage('error', 'Debes iniciar sesión para ver tu historial de compras.');
    header('Location: ../../login.php');
    exit();
}

$currentUser = AuthController::getCurrentUser();
$userId = $currentUser['id'];

$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);
$userCourseModel = new UserCourse($db);

$userOrders = $orderModel->readByUserId($userId);
$purchasedPlaylists = $userCourseModel->getPurchasedPlaylistsByUserId($userId);

$flashMessage = AuthController::getFlashMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras - El Profesor Hernán</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .history-section {
            padding: 60px 0;
            background-color: var(--light-gray);
        }
        .history-section h1 {
            text-align: center;
            font-size: 2.8rem;
            margin-bottom: 40px;
            color: var(--primary-color);
            font-weight: 700;
        }
        .history-card {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        .history-card h2 {
            font-size: 1.8rem;
            color: var(--text-color);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-info {
            text-align: left;
        }
        .order-info h3 {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        .order-info p {
            font-size: 0.9rem;
            color: var(--dark-gray);
        }
        .order-amount {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        .no-orders {
            text-align: center;
            padding: 40px;
            color: var(--dark-gray);
            font-size: 1.1rem;
        }
        .purchased-courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .purchased-course-card {
            background-color: var(--light-gray);
            border-radius: var(--border-radius-lg);
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }
        .purchased-course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .purchased-course-card img {
            width: 100%;
            max-height: 120px;
            object-fit: cover;
            border-radius: var(--border-radius-sm);
            margin-bottom: 15px;
        }
        .purchased-course-card h3 {
            font-size: 1.2rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        .purchased-course-card p {
            font-size: 0.9rem;
            color: var(--dark-gray);
            margin-bottom: 15px;
        }
        .purchased-course-card .btn-access {
            background-color: var(--teal-color);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-block;
        }
        .purchased-course-card .btn-access:hover {
            background-color: #48b090;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="../../img/logo-profe-hernan.png" alt="El Profesor Hernán" style="height: 40px;">
                <span>El Profesor Hernán</span>
            </div>
            
            <nav class="nav">
                <ul>
                    <li><a href="home.php">Inicio</a></li>
                    <li><a href="home.php">Cursos</a></li>
                    <li><a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        Carrito
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a></li>
                </ul>
            </nav>
            
            <div class="auth-links">
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']); ?></span>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="../admin/index.php?controller=admin&action=dashboard" class="btn-admin">Panel Admin</a>
                    <?php endif; ?>
                    <a href="../../logout.php" class="btn-logout">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="../../login.php" class="btn-login">Iniciar Sesión</a>
                    <a href="../../signup.php" class="btn-signup">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="history-section">
        <div class="container">
            <h1>Tu Historial de Compras</h1>

            <?php if ($flashMessage): ?>
                <div class="promo-message <?php echo $flashMessage['type']; ?>" style="margin-bottom: 20px;">
                    <?php echo $flashMessage['message']; ?>
                </div>
            <?php endif; ?>

            <div class="history-card">
                <h2>Pedidos Realizados</h2>
                <?php if (!empty($userOrders)): ?>
                    <?php foreach ($userOrders as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <h3>Pedido #<?php echo htmlspecialchars($order['id']); ?></h3>
                                <p>Fecha: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></p>
                                <p>Cursos: <?php echo htmlspecialchars($order['courses_purchased']); ?></p>
                                <p>Transacción ID: <?php echo htmlspecialchars($order['transaction_id']); ?></p>
                                <p>Estado: <?php echo htmlspecialchars(ucfirst($order['status'])); ?></p>
                            </div>
                            <div class="order-amount">
                                $<?php echo htmlspecialchars(number_format($order['amount'], 2)); ?> <?php echo htmlspecialchars(strtoupper($order['currency'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-orders">Aún no has realizado ningún pedido.</p>
                <?php endif; ?>
            </div>

            <div class="history-card">
                <h2>Tus Cursos Comprados</h2>
                <?php if (!empty($purchasedPlaylists)): ?>
                    <div class="purchased-courses-grid">
                        <?php foreach ($purchasedPlaylists as $playlist): ?>
                            <div class="purchased-course-card">
                                <?php if (!empty($playlist['cover_image'])): ?>
                                    <img src="../../<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="<?php echo htmlspecialchars($playlist['name']); ?>">
                                <?php else: ?>
                                    <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($playlist['name']); ?></h3>
                                <p>Nivel: <?php echo htmlspecialchars($playlist['level']); ?></p>
                                <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-access">Acceder al Curso</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-orders">Aún no has comprado ningún curso. ¡Explora nuestra oferta!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 El Profesor Hernán. Todos los derechos reservados.</p>
            <div class="footer-links">
                <a href="home.php">Inicio</a>
                <a href="home.php">Cursos</a>
                <a href="cart.php">Carrito</a>
            </div>
            <p>Aprende inglés con los mejores cursos online</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="../../auth/firebase-config.js"></script>
    <script src="../../auth/auth.js"></script>
</body>
</html>
