<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Verificar autenticación
if (!AuthController::isAuthenticated()) {
    header('Location: ../../login.php');
    exit();
}

$currentUser = AuthController::getCurrentUser();
$userId = $currentUser['id'];

// Obtener el ID del pedido de la URL
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($orderId <= 0) {
    header('Location: home.php');
    exit();
}

// Obtener detalles del pedido
$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);
$playlistModel = new Playlist($db);

$order = $orderModel->read($orderId);

// Verificar que el pedido exista y pertenezca al usuario actual
if (!$order || $order['user_id'] != $userId) {
    header('Location: home.php');
    exit();
}

// Obtener los cursos comprados en este pedido
$query = "SELECT uc.*, p.name, p.description, p.price, p.cover_image 
          FROM user_courses uc
          JOIN playlists p ON uc.playlist_id = p.id
          WHERE uc.order_id = :order_id";

$stmt = $db->prepare($query);
$stmt->bindParam(':order_id', $orderId);
$stmt->execute();
$purchasedCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener mensaje flash si existe
$flashMessage = AuthController::getFlashMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Profesor Hernán - Confirmación de Pedido</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="../../public/css/course-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .order-confirmation {
            padding: 40px 0;
        }
        
        .confirmation-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .confirmation-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .confirmation-header i {
            font-size: 60px;
            color: var(--green-color);
            margin-bottom: 20px;
            display: block;
        }
        
        .confirmation-header h1 {
            color: var(--green-color);
            margin-bottom: 10px;
        }
        
        .order-details {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .order-details .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-details .detail-label {
            font-weight: 600;
            color: var(--text-color);
        }
        
        .purchased-courses {
            margin-bottom: 30px;
        }
        
        .purchased-courses h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .course-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .course-item:last-child {
            border-bottom: none;
        }
        
        .course-image {
            width: 80px;
            height: 80px;
            border-radius: 5px;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .course-info {
            flex: 1;
        }
        
        .course-info h3 {
            margin: 0 0 5px;
            font-size: 18px;
        }
        
        .course-info p {
            margin: 0 0 10px;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .course-price {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .course-actions {
            margin-top: 10px;
        }
        
        .btn-access {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        
        .btn-access:hover {
            background-color: var(--primary-dark);
        }
        
        .confirmation-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn-continue-shopping {
            background-color: var(--secondary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-continue-shopping:hover {
            background-color: var(--secondary-dark);
        }
        
        .btn-view-courses {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-view-courses:hover {
            background-color: var(--primary-dark);
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

    <!-- Order Confirmation Section -->
    <section class="order-confirmation">
        <div class="container">
            <?php if ($flashMessage): ?>
                <div class="promo-message <?php echo $flashMessage['type']; ?>" style="margin-bottom: 20px;">
                    <?php echo $flashMessage['message']; ?>
                </div>
            <?php endif; ?>
            
            <div class="confirmation-container">
                <div class="confirmation-header">
                    <i class="fas fa-check-circle"></i>
                    <h1>¡Gracias por tu compra!</h1>
                    <p>Tu pedido ha sido confirmado y procesado correctamente.</p>
                </div>
                
                <div class="order-details">
                    <h2>Detalles del Pedido</h2>
                    <div class="detail-row">
                        <span class="detail-label">Número de Pedido:</span>
                        <span><?php echo htmlspecialchars($order['id']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha:</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total:</span>
                        <span>$<?php echo htmlspecialchars(number_format($order['amount'], 2)); ?> <?php echo htmlspecialchars(strtoupper($order['currency'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Estado:</span>
                        <span style="color: var(--green-color); font-weight: 600;"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">ID de Transacción:</span>
                        <span><?php echo htmlspecialchars($order['transaction_id']); ?></span>
                    </div>
                </div>
                
                <div class="purchased-courses">
                    <h2>Cursos Adquiridos</h2>
                    <?php if (empty($purchasedCourses)): ?>
                        <p>No se encontraron cursos en este pedido.</p>
                    <?php else: ?>
                        <?php foreach ($purchasedCourses as $course): ?>
                            <div class="course-item">
                                <div class="course-image">
                                    <?php if (!empty($course['cover_image'])): ?>
                                        <img src="../../<?php echo htmlspecialchars($course['cover_image']); ?>" alt="<?php echo htmlspecialchars($course['name']); ?>">
                                    <?php else: ?>
                                        <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                                    <?php endif; ?>
                                </div>
                                <div class="course-info">
                                    <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($course['description'], 0, 100) . (strlen($course['description']) > 100 ? '...' : '')); ?></p>
                                    <div class="course-price">$<?php echo htmlspecialchars(number_format($course['price'], 2)); ?></div>
                                    <div class="course-actions">
                                        <a href="course-detail.php?id=<?php echo htmlspecialchars($course['playlist_id']); ?>" class="btn-access">
                                            <i class="fas fa-play-circle"></i> Acceder al Curso
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="confirmation-actions">
                    <a href="home.php" class="btn-continue-shopping">
                        <i class="fas fa-arrow-left"></i> Seguir Comprando
                    </a>
                    <a href="purchase-history.php" class="btn-view-courses">
                        <i class="fas fa-history"></i> Ver Historial de Compras
                    </a>
                </div>
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
