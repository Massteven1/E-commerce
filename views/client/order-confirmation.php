<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir dependencias
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/UserCourse.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../controllers/CartController.php';

use Controllers\AuthController;
use Config\Database;
use Models\Order;
use Models\UserCourse;
use Models\Playlist;
use Controllers\CartController;

// Verificar autenticación
if (!AuthController::isAuthenticated()) {
    AuthController::setFlashMessage('error', 'Debes iniciar sesión para ver esta página.');
    header('Location: ../../login.php');
    exit();
}

// Obtener ID del pedido
$orderId = $_GET['order_id'] ?? null;
if (!$orderId || !is_numeric($orderId)) {
    AuthController::setFlashMessage('error', 'ID de pedido inválido.');
    header('Location: home.php');
    exit();
}

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar modelos
$orderModel = new Order($db);
$userCourseModel = new UserCourse($db);
$playlistModel = new Playlist($db);

// Obtener datos del pedido
$order = $orderModel->getOrderById($orderId);
if (!$order) {
    AuthController::setFlashMessage('error', 'Pedido no encontrado.');
    header('Location: home.php');
    exit();
}

// Verificar que el pedido pertenece al usuario actual
$currentUser = AuthController::getCurrentUser();
if ($order['user_id'] != $currentUser['id']) {
    AuthController::setFlashMessage('error', 'No tienes permiso para ver este pedido.');
    header('Location: home.php');
    exit();
}

// Obtener cursos del pedido
$orderItems = $orderModel->getOrderItems($orderId);

// Obtener conteo del carrito (debería ser 0 después de la compra)
$cartController = new CartController();
$cart_count = $cartController->getCartCount();

// Función para obtener el nombre del usuario
function getUserDisplayName($user) {
    if (!empty($user['name'])) return htmlspecialchars($user['name']);
    if (!empty($user['first_name'])) {
        $name = $user['first_name'];
        if (!empty($user['last_name'])) $name .= ' ' . $user['last_name'];
        return htmlspecialchars($name);
    }
    if (!empty($user['email'])) {
        $emailParts = explode('@', $user['email']);
        return htmlspecialchars(ucfirst($emailParts[0]));
    }
    return 'Usuario';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra - El Profesor Hernán</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="../../public/css/cart-improvements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .confirmation-section {
            padding: 2rem 0;
            min-height: 70vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .confirmation-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .confirmation-header i {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .confirmation-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .confirmation-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .confirmation-content {
            padding: 2rem;
        }

        .order-summary {
            background: #f8f9fa;
            border-radius: var(--border-radius-sm);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .order-summary h3 {
            color: var(--text-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .order-detail {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .order-detail:last-child {
            border-bottom: none;
        }

        .order-detail strong {
            color: var(--text-color);
        }

        .courses-purchased {
            margin-top: 2rem;
        }

        .courses-purchased h3 {
            color: var(--text-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .course-list {
            display: grid;
            gap: 1rem;
        }

        .course-item {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius-sm);
            padding: 1rem;
            transition: var(--transition);
        }

        .course-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .course-image {
            width: 80px;
            height: 60px;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            margin-right: 1rem;
        }

        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-info {
            flex: 1;
        }

        .course-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.25rem;
        }

        .course-level {
            font-size: 0.9rem;
            color: var(--dark-gray);
        }

        .course-price {
            font-weight: 600;
            color: var(--teal-color);
            font-size: 1.1rem;
        }

        .next-steps {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .next-steps h4 {
            color: #1976d2;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .next-steps ul {
            list-style: none;
            padding: 0;
        }

        .next-steps li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .next-steps li i {
            color: #2196f3;
            width: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: var(--purple-color);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .confirmation-section {
                padding: 1rem;
            }

            .confirmation-content {
                padding: 1rem;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .course-item {
                flex-direction: column;
                text-align: center;
            }

            .course-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="../../public/img/logo-profe-hernan.png" alt="El Profesor Hernán" style="height: 40px;">
                <span>El Profesor Hernán</span>
            </div>
            
            <nav class="nav">
                <ul>
                    <li><a href="home.php">Inicio</a></li>
                    <li><a href="all-courses.php">Cursos</a></li>
                    <li><a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        Carrito
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                </ul>
            </nav>
            
            <div class="auth-links">
                <span>Hola, <?php echo getUserDisplayName($currentUser); ?></span>
                <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                    <a href="../admin/index.php?controller=admin&action=dashboard" class="btn-admin">Panel Admin</a>
                <?php endif; ?>
                <a href="purchase-history.php" class="btn-history">Mis Cursos</a>
                <a href="../../logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Confirmation Section -->
    <section class="confirmation-section">
        <div class="container">
            <div class="confirmation-container">
                <!-- Header -->
                <div class="confirmation-header">
                    <i class="fas fa-check-circle"></i>
                    <h1>¡Compra Exitosa!</h1>
                    <p>Tu pago ha sido procesado correctamente</p>
                </div>

                <!-- Content -->
                <div class="confirmation-content">
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h3><i class="fas fa-receipt"></i> Resumen del Pedido</h3>
                        <div class="order-details">
                            <div class="order-detail">
                                <span>Número de Pedido:</span>
                                <strong>#<?php echo htmlspecialchars($order['id']); ?></strong>
                            </div>
                            <div class="order-detail">
                                <span>ID de Transacción:</span>
                                <strong><?php echo htmlspecialchars($order['transaction_id']); ?></strong>
                            </div>
                            <div class="order-detail">
                                <span>Fecha:</span>
                                <strong><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></strong>
                            </div>
                            <div class="order-detail">
                                <span>Total Pagado:</span>
                                <strong>$<?php echo number_format($order['amount'], 2); ?> <?php echo strtoupper($order['currency']); ?></strong>
                            </div>
                            <div class="order-detail">
                                <span>Método de Pago:</span>
                                <strong><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></strong>
                            </div>
                            <div class="order-detail">
                                <span>Estado:</span>
                                <strong style="color: #28a745;">Completado</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Purchased -->
                    <div class="courses-purchased">
                        <h3><i class="fas fa-graduation-cap"></i> Cursos Adquiridos</h3>
                        <?php if (!empty($orderItems)): ?>
                            <div class="course-list">
                                <?php foreach ($orderItems as $course): ?>
                                    <div class="course-item">
                                        <div class="course-image">
                                            <?php 
                                            $courseImageUrl = !empty($course['cover_image']) ? 
                                                (strpos($course['cover_image'], 'public/') === 0 ? '../../' . $course['cover_image'] : '../../public/' . $course['cover_image']) : 
                                                'https://i.imgur.com/xdbHo4E.png';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($courseImageUrl); ?>" 
                                                 alt="<?php echo htmlspecialchars($course['name'] ?? 'Curso'); ?>">
                                        </div>
                                        <div class="course-info">
                                            <div class="course-name"><?php echo htmlspecialchars($course['name'] ?? 'Curso sin nombre'); ?></div>
                                            <div class="course-level">Nivel: <?php echo htmlspecialchars($course['level'] ?? 'Todos los niveles'); ?></div>
                                        </div>
                                        <div class="course-price">
                                            $<?php echo number_format($course['price'] ?? 0, 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No se encontraron detalles de los cursos. Por favor, contacta a soporte.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Next Steps -->
                    <div class="next-steps">
                        <h4><i class="fas fa-lightbulb"></i> Próximos Pasos</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> Ya tienes acceso completo a todos los cursos comprados</li>
                            <li><i class="fas fa-play"></i> Puedes comenzar a estudiar inmediatamente</li>
                            <li><i class="fas fa-history"></i> Revisa tu historial de compras en cualquier momento</li>
                            <li><i class="fas fa-envelope"></i> Recibirás un email de confirmación en breve</li>
                            <li><i class="fas fa-headset"></i> Si tienes dudas, contacta a nuestro soporte</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="purchase-history.php" class="btn-primary">
                            <i class="fas fa-graduation-cap"></i>
                            Ver Mis Cursos
                        </a>
                        <a href="home.php" class="btn-secondary">
                            <i class="fas fa-home"></i>
                            Volver al Inicio
                        </a>
                    </div>
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
                <a href="all-courses.php">Cursos</a>
                <a href="purchase-history.php">Mis Cursos</a>
            </div>
            <p>¡Gracias por confiar en nosotros para tu aprendizaje!</p>
        </div>
    </footer>

    <script>
        // Limpiar cualquier dato del carrito que pueda quedar en localStorage
        if (typeof(Storage) !== "undefined") {
            localStorage.removeItem('cart');
            localStorage.removeItem('cartCount');
        }

        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const courseItems = document.querySelectorAll('.course-item');
            courseItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    item.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 150);
            });
        });
    </script>
</body>
</html>
