<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir dependencias necesarias
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Playlist.php';

// Usar los namespaces correctos
use Controllers\AuthController;
use Config\Database;
use Models\Order;
use Models\User;
use Models\Playlist;

// Verificar autenticación
if (!AuthController::isAuthenticated()) {
    header('Location: ../../login.php');
    exit();
}

$user = AuthController::getCurrentUser();

// Obtener el ID del pedido desde la URL
$order_id = $_GET['order_id'] ?? null;

if (!$order_id || !is_numeric($order_id)) {
    header('Location: home.php');
    exit();
}

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

// Obtener detalles del pedido
$orderModel = new Order($db);
$order = $orderModel->getOrderById($order_id);

if (!$order || $order['user_id'] != $user['id']) {
    header('Location: home.php');
    exit();
}

// Obtener detalles de los cursos del pedido
$orderItems = $orderModel->getOrderItems($order_id);

// Calcular la ruta base para los recursos
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Obtener la ruta base del proyecto
if (strpos($scriptName, '/views/client/') !== false) {
    $basePath = str_replace('/views/client/order-confirmation.php', '', $scriptName);
} else {
    $basePath = '';
}

$baseUrl = $protocol . '://' . $host . $basePath;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido - El Profesor Hernán</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/public/css/styles.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/public/css/checkout-improvements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .success-icon i {
            font-size: 4rem;
            color: #28a745;
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 50%;
            border: 3px solid #28a745;
        }
        
        .confirmation-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .confirmation-header h1 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .confirmation-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .order-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            text-align: center;
        }
        
        .info-item strong {
            display: block;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .info-item span {
            color: #6c757d;
        }
        
        .courses-list {
            margin-top: 1.5rem;
        }
        
        .course-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .course-item img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            margin-right: 1rem;
            object-fit: cover;
        }
        
        .course-info {
            flex: 1;
        }
        
        .course-info h4 {
            margin: 0 0 0.5rem 0;
            color: #2c3e50;
        }
        
        .course-info p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .course-price {
            font-weight: bold;
            color: #28a745;
            font-size: 1.1rem;
        }
        
        .total-section {
            border-top: 2px solid #dee2e6;
            padding-top: 1rem;
            text-align: right;
        }
        
        .total-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .action-buttons {
            text-align: center;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 2px solid #dee2e6;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .next-steps {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }
        
        .next-steps h3 {
            color: #1976d2;
            margin-bottom: 1rem;
        }
        
        .next-steps ul {
            list-style: none;
            padding: 0;
        }
        
        .next-steps li {
            padding: 0.5rem 0;
            color: #424242;
        }
        
        .next-steps li i {
            color: #1976d2;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo $baseUrl; ?>/index.php">
                    <img src="<?php echo $baseUrl; ?>/img/logo-profe-hernan.png" alt="El Profesor Hernán" style="height: 40px;">
                    <span>El Profesor Hernán</span>
                </a>
            </div>
            
            <nav class="nav">
                <ul>
                    <li><a href="home.php">Inicio</a></li>
                    <li><a href="all-courses.php">Cursos</a></li>
                    <li><a href="cart.php">Carrito</a></li>
                </ul>
            </nav>
            
            <div class="user-menu">
                <span>Hola, <?php echo htmlspecialchars($user['first_name']); ?></span>
                <a href="<?php echo $baseUrl; ?>/logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="confirmation-container">
                <!-- Icono de éxito -->
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <!-- Header de confirmación -->
                <div class="confirmation-header">
                    <h1>¡Pago Exitoso!</h1>
                    <p>Tu pedido ha sido procesado correctamente</p>
                </div>
                
                <!-- Detalles del pedido -->
                <div class="order-details">
                    <div class="order-info">
                        <div class="info-item">
                            <strong>Número de Pedido</strong>
                            <span>#<?php echo htmlspecialchars($order['id']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Fecha</strong>
                            <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Estado</strong>
                            <span style="color: #28a745; font-weight: bold;">
                                <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Método de Pago</strong>
                            <span>Tarjeta de Crédito</span>
                        </div>
                    </div>
                    
                    <!-- Lista de cursos -->
                    <div class="courses-list">
                        <h3>Cursos Adquiridos:</h3>
                        <?php if (!empty($orderItems)): ?>
                            <?php foreach ($orderItems as $item): ?>
                                <div class="course-item">
                                    <img src="<?php echo !empty($item['cover_image']) ? $baseUrl . '/' . htmlspecialchars($item['cover_image']) : 'https://i.imgur.com/xdbHo4E.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div class="course-info">
                                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p>Nivel <?php echo htmlspecialchars($item['level'] ?? 'Todos los niveles'); ?> - <?php echo htmlspecialchars($item['description'] ?: 'Curso completo de inglés'); ?></p>
                                    </div>
                                    <div class="course-price">
                                        $<?php echo number_format($item['price'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No se encontraron detalles de los cursos.</p>
                        <?php endif; ?>
                        
                        <!-- Total -->
                        <div class="total-section">
                            <div class="total-amount">
                                Total: $<?php echo number_format($order['amount'], 2); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Próximos pasos -->
                <div class="next-steps">
                    <h3><i class="fas fa-lightbulb"></i> Próximos Pasos</h3>
                    <ul>
                        <li><i class="fas fa-envelope"></i> Recibirás un email de confirmación con los detalles de tu compra</li>
                        <li><i class="fas fa-play-circle"></i> Ya puedes acceder a tus cursos desde tu panel de estudiante</li>
                        <li><i class="fas fa-graduation-cap"></i> Comienza tu aprendizaje cuando quieras, a tu propio ritmo</li>
                        <li><i class="fas fa-headset"></i> Si tienes dudas, nuestro soporte está disponible 24/7</li>
                    </ul>
                </div>
                
                <!-- Botones de acción -->
                <div class="action-buttons">
                    <a href="home.php" class="btn btn-primary">
                        <i class="fas fa-play"></i> Comenzar a Estudiar
                    </a>
                    <a href="home.php" class="btn btn-secondary">
                        <i class="fas fa-book"></i> Ver Más Cursos
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <img src="<?php echo $baseUrl; ?>/img/logo-profe-hernan.png" alt="El Profesor Hernán" style="height: 40px;">
                        <span>El Profesor Hernán</span>
                    </div>
                    <p>Tu mejor opción para aprender inglés online.</p>
                </div>
                <div class="footer-section">
                    <h4>Enlaces</h4>
                    <ul>
                        <li><a href="home.php">Inicio</a></li>
                        <li><a href="home.php">Cursos</a></li>
                        <li><a href="cart.php">Carrito</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Soporte</h4>
                    <ul>
                        <li><a href="#">Centro de Ayuda</a></li>
                        <li><a href="#">Contacto</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 El Profesor Hernán. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
