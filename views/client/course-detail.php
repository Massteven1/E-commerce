<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir todas las dependencias necesarias
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/UserCourse.php';
require_once __DIR__ . '/../../controllers/CartController.php';

// Usar los namespaces correctos
use Controllers\AuthController;
use Config\Database;
use Models\Playlist;
use Models\UserCourse;
use Controllers\CartController;

// Verificar autenticación
if (!AuthController::isAuthenticated()) {
    AuthController::setFlashMessage('error', 'Debes iniciar sesión para ver los detalles del curso.');
    header('Location: ../../login.php');
    exit();
}

// Obtener ID del curso
$courseId = $_GET['id'] ?? null;

if (!$courseId || !is_numeric($courseId)) {
    AuthController::setFlashMessage('error', 'Curso no encontrado.');
    header('Location: home.php');
    exit();
}

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar modelos
$playlistModel = new Playlist($db);
$userCourseModel = new UserCourse($db);

// Obtener detalles del curso
$course = $playlistModel->readOne($courseId);

if (!$course) {
    AuthController::setFlashMessage('error', 'Curso no encontrado.');
    header('Location: home.php');
    exit();
}

// Obtener usuario actual
$currentUser = AuthController::getCurrentUser();
$userId = $currentUser['id'];

// Verificar si el usuario tiene acceso al curso
$hasAccess = $userCourseModel->hasAccess($userId, $courseId);

// Obtener conteo del carrito para el header
$cartController = new CartController();
$cart_count = $cartController->getCartCount();

// Obtener mensaje flash si existe
$flashMessage = AuthController::getFlashMessage();

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['name']); ?> - El Profesor Hernán</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="../../public/css/cart-improvements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .course-detail-section {
            padding: 2rem 0;
            min-height: 70vh;
        }

        .course-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--purple-color) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
        }

        .course-header-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
            align-items: center;
        }

        .course-info h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .course-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .meta-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .course-description {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .course-image {
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .course-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .course-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
        }

        .main-content {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .price-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--teal-color);
            margin-bottom: 0.5rem;
        }

        .price-label {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .access-button {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: var(--transition);
            margin-bottom: 1rem;
        }

        .btn-access {
            background: var(--teal-color);
            color: white;
        }

        .btn-access:hover {
            background: #48b090;
            transform: translateY(-2px);
        }

        .btn-add-cart {
            background: var(--primary-color);
            color: white;
        }

        .btn-add-cart:hover {
            background: var(--purple-color);
            transform: translateY(-2px);
        }

        .features-list {
            list-style: none;
            padding: 0;
        }

        .features-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .features-list li:last-child {
            border-bottom: none;
        }

        .features-list i {
            color: var(--teal-color);
            width: 20px;
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .guarantee-box {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: var(--border-radius-sm);
            text-align: center;
            margin-top: 2rem;
        }

        .guarantee-box i {
            font-size: 2rem;
            color: var(--blue-color);
            margin-bottom: 1rem;
        }

        .guarantee-box h4 {
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .guarantee-box p {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .course-header-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .course-content {
                grid-template-columns: 1fr;
            }

            .course-info h1 {
                font-size: 2rem;
            }

            .sidebar {
                position: static;
            }
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
                <span>Hola, <?php echo htmlspecialchars($currentUser['name']); ?></span>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="../admin/index.php?controller=admin&action=dashboard" class="btn-admin">Panel Admin</a>
                <?php endif; ?>
                <a href="purchase-history.php" class="btn-history">Mis Cursos</a>
                <a href="../../logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Course Header -->
    <section class="course-header">
        <div class="container">
            <div class="course-header-content">
                <div class="course-info">
                    <h1><?php echo htmlspecialchars($course['name']); ?></h1>
                    <div class="course-meta">
                        <div class="meta-item">
                            <i class="fas fa-layer-group"></i>
                            Nivel <?php echo htmlspecialchars($course['level'] ?? 'Todos los niveles'); ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            Acceso de por vida
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-certificate"></i>
                            Certificado incluido
                        </div>
                    </div>
                    <p class="course-description">
                        <?php echo htmlspecialchars($course['description'] ?: 'Curso completo de inglés diseñado para mejorar tus habilidades lingüísticas de manera efectiva y práctica.'); ?>
                    </p>
                </div>
                <div class="course-image">
                    <?php if (!empty($course['cover_image'])): ?>
                        <img src="../../<?php echo htmlspecialchars($course['cover_image']); ?>" 
                             alt="<?php echo htmlspecialchars($course['name']); ?>">
                    <?php else: ?>
                        <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Course Detail Section -->
    <section class="course-detail-section">
        <div class="container">
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?>">
                    <i class="fas fa-<?php echo $flashMessage['type'] === 'error' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
                    <?php echo $flashMessage['message']; ?>
                </div>
            <?php endif; ?>

            <div class="course-content">
                <!-- Main Content -->
                <div class="main-content">
                    <h2 class="section-title">¿Qué aprenderás?</h2>
                    <ul class="features-list">
                        <li><i class="fas fa-check"></i> Vocabulario esencial para comunicación diaria</li>
                        <li><i class="fas fa-check"></i> Gramática práctica y fácil de entender</li>
                        <li><i class="fas fa-check"></i> Pronunciación correcta con ejercicios de audio</li>
                        <li><i class="fas fa-check"></i> Conversaciones reales y situaciones cotidianas</li>
                        <li><i class="fas fa-check"></i> Comprensión auditiva con material auténtico</li>
                        <li><i class="fas fa-check"></i> Escritura efectiva para diferentes contextos</li>
                        <li><i class="fas fa-check"></i> Estrategias para mejorar la fluidez</li>
                        <li><i class="fas fa-check"></i> Preparación para exámenes internacionales</li>
                    </ul>

                    <h2 class="section-title" style="margin-top: 2rem;">Contenido del curso</h2>
                    <div class="course-curriculum">
                        <div class="module">
                            <h4><i class="fas fa-play-circle"></i> Módulo 1: Fundamentos</h4>
                            <p>Introducción al inglés, alfabeto, números y saludos básicos.</p>
                        </div>
                        <div class="module">
                            <h4><i class="fas fa-play-circle"></i> Módulo 2: Vocabulario Esencial</h4>
                            <p>Palabras y frases más importantes para la comunicación diaria.</p>
                        </div>
                        <div class="module">
                            <h4><i class="fas fa-play-circle"></i> Módulo 3: Gramática Práctica</h4>
                            <p>Estructuras gramaticales fundamentales con ejemplos prácticos.</p>
                        </div>
                        <div class="module">
                            <h4><i class="fas fa-play-circle"></i> Módulo 4: Conversación</h4>
                            <p>Diálogos reales y práctica de conversación en diferentes contextos.</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <div class="price-section">
                        <div class="price">$<?php echo number_format($course['price'], 2); ?></div>
                        <div class="price-label">Acceso completo de por vida</div>
                    </div>

                    <?php if ($hasAccess): ?>
                        <a href="#" class="access-button btn-access">
                            <i class="fas fa-play"></i> Acceder al Curso
                        </a>
                        <p style="text-align: center; color: var(--teal-color); font-weight: 500;">
                            <i class="fas fa-check-circle"></i> Ya tienes acceso a este curso
                        </p>
                    <?php else: ?>
                        <a href="../../controllers/CartController.php?action=add&id=<?php echo $course['id']; ?>" 
                           class="access-button btn-add-cart">
                            <i class="fas fa-shopping-cart"></i> Añadir al Carrito
                        </a>
                    <?php endif; ?>

                    <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Este curso incluye:</h3>
                    <ul class="features-list">
                        <li><i class="fas fa-video"></i> Lecciones en video HD</li>
                        <li><i class="fas fa-file-pdf"></i> Material descargable</li>
                        <li><i class="fas fa-headphones"></i> Ejercicios de audio</li>
                        <li><i class="fas fa-tasks"></i> Ejercicios interactivos</li>
                        <li><i class="fas fa-certificate"></i> Certificado de finalización</li>
                        <li><i class="fas fa-mobile-alt"></i> Acceso móvil</li>
                        <li><i class="fas fa-infinity"></i> Acceso de por vida</li>
                        <li><i class="fas fa-headset"></i> Soporte técnico</li>
                    </ul>

                    <div class="guarantee-box">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Garantía de 30 días</h4>
                        <p>Si no estás satisfecho con el curso, te devolvemos tu dinero sin preguntas.</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div style="text-align: center; margin-top: 3rem;">
                <a href="home.php" style="margin-right: 1rem; color: var(--primary-color); text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Volver a Cursos
                </a>
                <a href="purchase-history.php" style="color: var(--primary-color); text-decoration: none;">
                    <i class="fas fa-graduation-cap"></i> Ver Mis Cursos
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
</body>
</html>
