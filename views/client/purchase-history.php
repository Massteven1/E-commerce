<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
   session_start();
}

// Incluir todas las dependencias necesarias con rutas correctas
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/UserCourse.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../controllers/CartController.php';

// Usar los namespaces correctos
use Controllers\AuthController;
use Config\Database;
use Models\Order;
use Models\UserCourse;
use Models\Playlist;
use Controllers\CartController;

// Función helper para obtener el nombre del usuario de forma segura
function getUserDisplayName($user) {
    if (empty($user) || !is_array($user)) {
        return 'Usuario';
    }
    
    // Intentar diferentes campos para el nombre
    if (!empty($user['name'])) {
        return htmlspecialchars($user['name']);
    }
    
    if (!empty($user['first_name'])) {
        $name = $user['first_name'];
        if (!empty($user['last_name'])) {
            $name .= ' ' . $user['last_name'];
        }
        return htmlspecialchars($name);
    }
    
    if (!empty($user['email'])) {
        $emailParts = explode('@', $user['email']);
        return htmlspecialchars(ucfirst($emailParts[0]));
    }
    
    return 'Usuario';
}

// Verificar autenticación
if (!AuthController::isAuthenticated()) {
   AuthController::setFlashMessage('error', 'Debes iniciar sesión para ver tu historial de compras.');
   header('Location: ../../login.php');
   exit();
}

// Obtener usuario actual
$currentUser = AuthController::getCurrentUser();
$userId = $currentUser['id'];

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar modelos
$orderModel = new Order($db);
$userCourseModel = new UserCourse($db);
$playlistModel = new Playlist($db);

// Obtener historial de pedidos del usuario
$orders = $orderModel->readByUserId($userId);

// Obtener cursos comprados del usuario con detalles completos
$purchasedCourses = $userCourseModel->readByUserId($userId);

// Obtener estadísticas del usuario
try {
    $userStats = [
        'total_courses' => count($purchasedCourses),
        'total_spent' => 0,
        'different_levels' => 0,
        'first_purchase' => null
    ];
    
    // Calcular estadísticas desde los pedidos
    if (!empty($orders)) {
        $totalSpent = 0;
        $levels = [];
        $firstPurchase = null;
        
        foreach ($orders as $order) {
            if (($order['status'] ?? '') === 'completed') { // Usar ?? para evitar undefined array key
                $totalSpent += floatval($order['amount'] ?? 0); // Usar ?? para evitar undefined array key
            }
            
            if ($firstPurchase === null || strtotime($order['created_at'] ?? 'now') < strtotime($firstPurchase)) { // Usar ?? para evitar undefined array key
                $firstPurchase = $order['created_at'];
            }
        }
        
        // Obtener niveles únicos de los cursos
        foreach ($purchasedCourses as $course) {
            if (!empty($course['level']) && !in_array($course['level'], $levels)) {
                $levels[] = $course['level'];
            }
        }
        
        $userStats['total_spent'] = $totalSpent;
        $userStats['different_levels'] = count($levels);
        $userStats['first_purchase'] = $firstPurchase;
    }
    
} catch (Exception $e) {
    error_log("Error obteniendo estadísticas de usuario: " . $e->getMessage());
    $userStats = [
        'total_courses' => 0,
        'total_spent' => 0,
        'different_levels' => 0,
        'first_purchase' => null
    ];
}

// Obtener conteo del carrito para el header
$cartController = new CartController();
$cart_count = $cartController->getCartCount();

// Obtener mensaje flash si existe
$flashMessage = AuthController::getFlashMessage();

// Función para formatear fechas
function formatDate($date) {
   return date('d/m/Y H:i', strtotime($date));
}

// Función para obtener el color del nivel
function getLevelColor($level) {
   $colors = [
       'A1' => '#56e2c6',
       'A2' => '#4dabf7',
       'B1' => '#ffa726',
       'B2' => '#ff5a5a',
       'C1' => '#8a56e2',
       'Mixto' => '#6c757d'
   ];
   return $colors[$level] ?? '#6c757d';
}

// Función para obtener el estado del pedido con color
function getOrderStatusBadge($status) {
   $badges = [
       'completed' => ['text' => 'Completado', 'class' => 'success'],
       'pending' => ['text' => 'Pendiente', 'class' => 'warning'],
       'failed' => ['text' => 'Fallido', 'class' => 'danger'],
       'cancelled' => ['text' => 'Cancelado', 'class' => 'secondary']
   ];
   
   $badge = $badges[$status] ?? ['text' => ucfirst($status), 'class' => 'secondary'];
   return '<span class="status-badge status-' . $badge['class'] . '">' . $badge['text'] . '</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Mi Historial de Compras - El Profesor Hernán</title>
   <link rel="stylesheet" href="../../public/css/styles.css">
   <link rel="stylesheet" href="../../public/css/cart-improvements.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <style>
       .purchase-history-section {
           padding: 2rem 0;
           min-height: 70vh;
       }

       .page-header {
           text-align: center;
           margin-bottom: 3rem;
       }

       .page-header h1 {
           color: var(--text-color);
           margin-bottom: 0.5rem;
           font-size: 2.5rem;
       }

       .page-header p {
           color: var(--dark-gray);
           font-size: 1.1rem;
       }

       .stats-grid {
           display: grid;
           grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
           gap: 1.5rem;
           margin-bottom: 3rem;
       }

       .stat-card {
           background: white;
           padding: 1.5rem;
           border-radius: var(--border-radius-lg);
           box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
           text-align: center;
           transition: var(--transition);
       }

       .stat-card:hover {
           transform: translateY(-5px);
           box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
       }

       .stat-card .icon {
           font-size: 2.5rem;
           margin-bottom: 1rem;
       }

       .stat-card .icon.courses { color: var(--primary-color); }
       .stat-card .icon.spent { color: var(--teal-color); }
       .stat-card .icon.levels { color: var(--orange-color); }
       .stat-card .icon.time { color: var(--blue-color); }

       .stat-card .number {
           font-size: 2rem;
           font-weight: 700;
           color: var(--text-color);
           margin-bottom: 0.5rem;
       }

       .stat-card .label {
           color: var(--dark-gray);
           font-weight: 500;
       }

       .content-tabs {
           display: flex;
           justify-content: center;
           margin-bottom: 2rem;
           border-bottom: 2px solid #eee;
       }

       .tab-button {
           background: none;
           border: none;
           padding: 1rem 2rem;
           font-size: 1rem;
           font-weight: 500;
           color: var(--dark-gray);
           cursor: pointer;
           transition: var(--transition);
           border-bottom: 3px solid transparent;
       }

       .tab-button.active {
           color: var(--primary-color);
           border-bottom-color: var(--primary-color);
       }

       .tab-content {
           display: none;
       }

       .tab-content.active {
           display: block;
       }

       .courses-grid {
           display: grid;
           grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
           gap: 1.5rem;
           margin-bottom: 2rem;
       }

       .course-card {
           background: white;
           border-radius: var(--border-radius-lg);
           overflow: hidden;
           box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
           transition: var(--transition);
       }

       .course-card:hover {
           transform: translateY(-5px);
           box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
       }

       .course-image {
           position: relative;
           height: 200px;
           overflow: hidden;
       }

       .course-image img {
           width: 100%;
           height: 100%;
           object-fit: cover;
       }

       .course-level {
           position: absolute;
           top: 1rem;
           right: 1rem;
           padding: 0.25rem 0.75rem;
           border-radius: 20px;
           color: white;
           font-size: 0.8rem;
           font-weight: 600;
       }

       .course-content {
           padding: 1.5rem;
       }

       .course-title {
           font-size: 1.2rem;
           font-weight: 600;
           color: var(--text-color);
           margin-bottom: 0.5rem;
       }

       .course-description {
           color: var(--dark-gray);
           font-size: 0.9rem;
           margin-bottom: 1rem;
           line-height: 1.5;
       }

       .course-meta {
           display: flex;
           justify-content: space-between;
           align-items: center;
           margin-bottom: 1rem;
           font-size: 0.9rem;
           color: var(--dark-gray);
       }

       .course-price {
           font-size: 1.1rem;
           font-weight: 600;
           color: var(--teal-color);
       }

       .course-actions {
           display: flex;
           gap: 0.5rem;
       }

       .btn-access {
           flex: 1;
           background: var(--primary-color);
           color: white;
           padding: 0.75rem 1rem;
           border: none;
           border-radius: var(--border-radius-sm);
           text-decoration: none;
           text-align: center;
           font-weight: 500;
           transition: var(--transition);
       }

       .btn-access:hover {
           background: var(--purple-color);
           transform: translateY(-2px);
       }

       .orders-table {
           background: white;
           border-radius: var(--border-radius-lg);
           overflow: hidden;
           box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
       }

       .table-header {
           background: var(--light-gray);
           padding: 1rem;
           font-weight: 600;
           color: var(--text-color);
       }

       .table-row {
           display: grid;
           grid-template-columns: 1fr 2fr 1fr 1fr 1fr;
           gap: 1rem;
           padding: 1rem;
           border-bottom: 1px solid #eee;
           align-items: center;
       }

       .table-row:last-child {
           border-bottom: none;
       }

       .status-badge {
           padding: 0.25rem 0.75rem;
           border-radius: 20px;
           font-size: 0.8rem;
           font-weight: 600;
           text-align: center;
       }

       .status-success {
           background: #e6ffe6;
           color: #28a745;
       }

       .status-warning {
           background: #fff3cd;
           color: #856404;
       }

       .status-danger {
           background: #f8d7da;
           color: #721c24;
       }

       .status-secondary {
           background: #e2e3e5;
           color: #6c757d;
       }

       .empty-state {
           text-align: center;
           padding: 3rem;
           color: var(--dark-gray);
       }

       .empty-state i {
           font-size: 4rem;
           margin-bottom: 1rem;
           color: var(--light-gray);
       }

       .empty-state h3 {
           margin-bottom: 1rem;
           color: var(--text-color);
       }

       .empty-state p {
           margin-bottom: 2rem;
       }

       .btn-browse {
           background: var(--primary-color);
           color: white;
           padding: 1rem 2rem;
           border-radius: var(--border-radius-sm);
           text-decoration: none;
           font-weight: 500;
           transition: var(--transition);
       }

       .btn-browse:hover {
           background: var(--purple-color);
           transform: translateY(-2px);
       }

       @media (max-width: 768px) {
           .stats-grid {
               grid-template-columns: repeat(2, 1fr);
           }

           .courses-grid {
               grid-template-columns: 1fr;
           }

           .content-tabs {
               flex-direction: column;
           }

           .tab-button {
               padding: 0.75rem 1rem;
           }

           .table-row {
               grid-template-columns: 1fr;
               gap: 0.5rem;
               text-align: center;
           }

           .table-header {
               display: none;
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
               <a href="purchase-history.php" class="btn-history active">Mis Cursos</a>
               <a href="../../logout.php" class="btn-logout">Cerrar Sesión</a>
           </div>
       </div>
   </header>

   <!-- Purchase History Section -->
   <section class="purchase-history-section">
       <div class="container">
           <!-- Page Header -->
           <div class="page-header">
               <h1><i class="fas fa-graduation-cap"></i> Mi Historial de Aprendizaje</h1>
               <p>Gestiona tus cursos y revisa tu progreso académico</p>
           </div>

           <?php if ($flashMessage): ?>
               <div class="alert alert-<?php echo $flashMessage['type']; ?>">
                   <i class="fas fa-<?php echo $flashMessage['type'] === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                   <?php echo $flashMessage['message']; ?>
               </div>
           <?php endif; ?>

           <!-- Statistics -->
           <?php if (!empty($userStats)): ?>
               <div class="stats-grid">
                   <div class="stat-card">
                       <div class="icon courses">
                           <i class="fas fa-book"></i>
                       </div>
                       <div class="number"><?php echo $userStats['total_courses'] ?? 0; ?></div>
                       <div class="label">Cursos Adquiridos</div>
                   </div>
                   <div class="stat-card">
                       <div class="icon spent">
                           <i class="fas fa-dollar-sign"></i>
                       </div>
                       <div class="number">$<?php echo number_format($userStats['total_spent'] ?? 0, 2); ?></div>
                       <div class="label">Total Invertido</div>
                   </div>
                   <div class="stat-card">
                       <div class="icon levels">
                           <i class="fas fa-layer-group"></i>
                       </div>
                       <div class="number"><?php echo $userStats['different_levels'] ?? 0; ?></div>
                       <div class="label">Niveles Diferentes</div>
                   </div>
                   <div class="stat-card">
                       <div class="icon time">
                           <i class="fas fa-calendar-alt"></i>
                       </div>
                       <div class="number">
                           <?php 
                           if (!empty($userStats['first_purchase'])) {
                               $days = floor((time() - strtotime($userStats['first_purchase'])) / (60 * 60 * 24));
                               echo $days;
                           } else {
                               echo '0';
                           }
                           ?>
                       </div>
                       <div class="label">Días Aprendiendo</div>
                   </div>
               </div>
           <?php endif; ?>

           <!-- Content Tabs -->
           <div class="content-tabs">
               <button class="tab-button active" onclick="switchTab('courses')">
                   <i class="fas fa-play-circle"></i> Mis Cursos
               </button>
               <button class="tab-button" onclick="switchTab('orders')">
                   <i class="fas fa-receipt"></i> Historial de Pedidos
               </button>
           </div>

           <!-- Courses Tab -->
           <div id="courses-tab" class="tab-content active">
               <?php if (!empty($purchasedCourses)): ?>
                   <div class="courses-grid">
                       <?php foreach ($purchasedCourses as $course): ?>
                           <div class="course-card">
                               <div class="course-image">
                                   <?php 
                                   $courseImageUrl = !empty($course['cover_image']) ? 
                                       (strpos($course['cover_image'], 'public/') === 0 ? '../../' . $course['cover_image'] : '../../public/' . $course['cover_image']) : 
                                       'https://i.imgur.com/xdbHo4E.png';
                                   ?>
                                   <img src="<?php echo htmlspecialchars($courseImageUrl); ?>" 
                                        alt="<?php echo htmlspecialchars($course['name'] ?? 'Curso'); ?>">
                                   <div class="course-level" style="background-color: <?php echo getLevelColor($course['level'] ?? 'Mixto'); ?>">
                                       <?php echo htmlspecialchars($course['level'] ?? 'Todos los niveles'); ?>
                                   </div>
                               </div>
                               <div class="course-content">
                                   <h3 class="course-title"><?php echo htmlspecialchars($course['name'] ?? 'Curso sin nombre'); ?></h3>
                                   <p class="course-description">
                                       <?php echo htmlspecialchars($course['description'] ?: 'Curso completo de inglés diseñado para mejorar tus habilidades lingüísticas.'); ?>
                                   </p>
                                   <div class="course-meta">
                                       <span><i class="fas fa-calendar"></i> Adquirido: <?php echo formatDate($course['purchase_date'] ?? $course['created_at']); ?></span>
                                   </div>
                                   <div class="course-meta">
                                       <span class="course-price">$<?php echo number_format($course['paid_amount'] ?? $course['price'] ?? 0, 2); ?></span>
                                       <span><i class="fas fa-check-circle" style="color: var(--teal-color);"></i> Acceso Completo</span>
                                   </div>
                                   <div class="course-actions">
                                       <a href="course-detail.php?id=<?php echo $course['playlist_id']; ?>" class="btn-access">
                                           <i class="fas fa-play"></i> Acceder al Curso
                                       </a>
                                   </div>
                               </div>
                           </div>
                       <?php endforeach; ?>
                   </div>
               <?php else: ?>
                   <div class="empty-state">
                       <i class="fas fa-book-open"></i>
                       <h3>Aún no tienes cursos</h3>
                       <p>¡Comienza tu viaje de aprendizaje hoy! Explora nuestros cursos de inglés y encuentra el perfecto para ti.</p>
                       <a href="home.php" class="btn-browse">
                           <i class="fas fa-search"></i> Explorar Cursos
                       </a>
                   </div>
               <?php endif; ?>
           </div>

           <!-- Orders Tab -->
           <div id="orders-tab" class="tab-content">
               <?php if (!empty($orders)): ?>
                   <div class="orders-table">
                       <div class="table-header table-row">
                           <div>Pedido #</div>
                           <div>Cursos</div>
                           <div>Total</div>
                           <div>Estado</div>
                           <div>Fecha</div>
                       </div>
                       <?php foreach ($orders as $order): ?>
                           <div class="table-row">
                               <div>
                                   <strong>#<?php echo htmlspecialchars($order['id'] ?? ''); ?></strong>
                               </div>
                               <div>
                                   <?php if (!empty($order['courses_purchased'])): ?>
                                       <?php echo htmlspecialchars($order['courses_purchased']); ?>
                                   <?php else: ?>
                                       <?php echo ($order['course_count'] ?? 0); ?> curso(s)
                                   <?php endif; ?>
                               </div>
                               <div>
                                   <strong>$<?php echo number_format($order['amount'] ?? 0, 2); ?></strong>
                               </div>
                               <div>
                                   <?php echo getOrderStatusBadge($order['status'] ?? 'unknown'); ?>
                               </div>
                               <div>
                                   <?php echo formatDate($order['created_at'] ?? 'now'); ?>
                               </div>
                           </div>
                       <?php endforeach; ?>
                   </div>
               <?php else: ?>
                   <div class="empty-state">
                       <i class="fas fa-receipt"></i>
                       <h3>No tienes pedidos aún</h3>
                       <p>Cuando realices tu primera compra, aparecerá aquí tu historial de pedidos.</p>
                       <a href="home.php" class="btn-browse">
                           <i class="fas fa-shopping-cart"></i> Hacer Primera Compra
                       </a>
                   </div>
               <?php endif; ?>
           </div>

           <!-- Quick Actions -->
           <div style="text-align: center; margin-top: 3rem;">
               <a href="home.php" class="btn-browse" style="margin-right: 1rem;">
                   <i class="fas fa-home"></i> Volver al Inicio
               </a>
               <a href="home.php" class="btn-browse">
                   <i class="fas fa-plus"></i> Explorar Más Cursos
               </a>
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
               <a href="purchase-history.php">Mis Cursos</a>
           </div>
           <p>Aprende inglés con los mejores cursos online</p>
       </div>
   </footer>

   <!-- Scripts -->
   <script>
       function switchTab(tabName) {
           // Ocultar todas las pestañas
           document.querySelectorAll('.tab-content').forEach(tab => {
               tab.classList.remove('active');
           });
           
           // Remover clase active de todos los botones
           document.querySelectorAll('.tab-button').forEach(button => {
               button.classList.remove('active');
           });
           
           // Mostrar la pestaña seleccionada
           document.getElementById(tabName + '-tab').classList.add('active');
           
           // Activar el botón correspondiente
           event.target.classList.add('active');
       }

       // Animaciones al cargar la página
       document.addEventListener('DOMContentLoaded', function() {
           // Animar las tarjetas de estadísticas
           const statCards = document.querySelectorAll('.stat-card');
           statCards.forEach((card, index) => {
               setTimeout(() => {
                   card.style.opacity = '0';
                   card.style.transform = 'translateY(20px)';
                   card.style.transition = 'all 0.5s ease';
                   
                   setTimeout(() => {
                       card.style.opacity = '1';
                       card.style.transform = 'translateY(0)';
                   }, 100);
               }, index * 100);
           });

           // Animar las tarjetas de cursos
           const courseCards = document.querySelectorAll('.course-card');
           courseCards.forEach((card, index) => {
               setTimeout(() => {
                   card.style.opacity = '0';
                   card.style.transform = 'translateY(20px)';
                   card.style.transition = 'all 0.5s ease';
                   
                   setTimeout(() => {
                       card.style.opacity = '1';
                       card.style.transform = 'translateY(0)';
                   }, 100);
               }, index * 150);
           });
       });
   </script>
</body>
</html>
