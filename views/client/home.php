<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/UserCourse.php'; // Añadido para la nueva funcionalidad
require_once __DIR__ . '/../../controllers/CartController.php';
require_once __DIR__ . '/../../controllers/AuthController.php'; // Añadido para la nueva funcionalidad

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);
$userCourseModel = new UserCourse($db); // Instancia del modelo UserCourse

// Obtener las 3 playlists más vendidas (o simplemente las primeras 3 si no hay lógica de ventas)
$best_sellers = $playlistModel->readAll();
shuffle($best_sellers); // Mezclar para simular "más vendidos" si no hay datos reales
$best_sellers = array_slice($best_sellers, 0, 3);

// Obtener playlists por nivel para la sección "Nuestros Cursos por Nivel"
$playlists_by_level = [];
$all_playlists = $playlistModel->readAll();
foreach ($all_playlists as $playlist) {
    $level = 'Mixto'; // Default
    if (preg_match('/(A1|A2|B1|B2|C1)/', $playlist['name'], $matches)) {
        $level = $matches[0];
    }
    $playlists_by_level[$level][] = $playlist;
}

// Ordenar los niveles para que A1, A2, B1, B2, C1 aparezcan primero
$ordered_levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'Mixto'];
$sorted_playlists_by_level = [];
foreach ($ordered_levels as $level) {
    if (isset($playlists_by_level[$level])) {
        $sorted_playlists_by_level[$level] = $playlists_by_level[$level];
    }
}

// Obtener el conteo del carrito para el header
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Lógica de autenticación para la nueva funcionalidad
$isLoggedIn = AuthController::isAuthenticated();
$currentUserId = $isLoggedIn ? AuthController::getCurrentUser()['id'] : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Profesor Hernán - Cursos de Inglés</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="../../public/css/course-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

    <!-- Banner Section -->
    <section class="banner">
        <div class="container">
            <div class="banner-content">
                <div class="banner-text">
                    <h1>Domina el Inglés con Nuestros Cursos Online</h1>
                    <p>Aprende a tu propio ritmo con lecciones interactivas, profesores expertos y una comunidad de apoyo. ¡Tu fluidez comienza aquí!</p>
                    <div class="banner-buttons">
                        <a href="#best-sellers" class="btn-primary">Explorar Cursos</a>
                        <a href="#about-section" class="btn-secondary">Conocer al Profesor</a>
                    </div>
                    <div class="banner-stats">
                        <div class="stat-item">
                            <h3>10.000+</h3>
                            <p>Estudiantes Felices</p>
                        </div>
                        <div class="stat-item">
                            <h3>50+</h3>
                            <p>Cursos Completos</p>
                        </div>
                        <div class="stat-item">
                            <h3>4.9/5</h3>
                            <p>Calificación Promedio</p>
                        </div>
                    </div>
                </div>
                <div class="banner-image">
                    <div class="image-container">
                        <img src="../../img/profe-hernan-banner.png" alt="Profesor Hernán enseñando inglés">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Best Sellers Section -->
    <section class="best-sellers" id="best-sellers">
        <div class="container">
            <h2>Nuestros Cursos Más Populares</h2>
            <p class="section-subtitle">Descubre los cursos que nuestros estudiantes aman y que te ayudarán a alcanzar tus metas.</p>
            <div class="products-grid">
                <?php foreach ($best_sellers as $playlist): ?>
                    <div class="product-card">
                        <div class="product-tumb">
                            <?php if (!empty($playlist['cover_image'])): ?>
                                <img src="../../<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="<?php echo htmlspecialchars($playlist['name']); ?>">
                            <?php else: ?>
                                <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                            <?php endif; ?>
                            <div class="course-overlay">
                                <?php
                                $hasAccess = false;
                                if ($isLoggedIn && $currentUserId) {
                                    $hasAccess = $userCourseModel->hasAccess($currentUserId, $playlist['id']);
                                }
                                ?>
                                <?php if ($hasAccess): ?>
                                    <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-overlay">Acceder al Curso</a>
                                <?php else: ?>
                                    <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-overlay">Ver Detalles</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="product-details">
                            <span class="product-catagory">
                                <?php 
                                    preg_match('/(A|B|C)\d/', $playlist['name'], $matches);
                                    echo $matches[0] ?? 'General';
                                ?>
                            </span>
                            <h4><a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>"><?php echo htmlspecialchars($playlist['name']); ?></a></h4>
                            <p><?php echo htmlspecialchars($playlist['description'] ?: 'Curso completo de inglés para todos los niveles.'); ?></p>
                            <div class="product-bottom-details">
                                <div class="product-price">$<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?></div>
                                <?php if ($hasAccess): ?>
                                    <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="add-to-cart-btn">Acceder</a>
                                <?php else: ?>
                                    <a href="cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="add-to-cart-btn">Añadir al Carrito</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="view-more">
                <a href="all-courses.php">Ver todos los cursos <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Courses by Level Section -->
    <section class="courses">
        <div class="container">
            <h2>Nuestros Cursos por Nivel</h2>
            <p class="section-subtitle">Encuentra el curso perfecto para tu nivel actual y avanza con confianza.</p>
            <div class="courses-grid">
                <?php 
                $level_colors = [
                    'A1' => 'var(--teal-color)',
                    'A2' => 'var(--blue-color)',
                    'B1' => 'var(--orange-color)',
                    'B2' => 'var(--red-color)',
                    'C1' => 'var(--purple-color)',
                    'Mixto' => 'var(--secondary-color)'
                ];
                ?>
                <?php foreach ($sorted_playlists_by_level as $level => $playlists): ?>
                    <?php if (!empty($playlists)): ?>
                        <?php $playlist = $playlists[0]; // Tomar el primer curso de cada nivel para la tarjeta de nivel ?>
                        <div class="course-card">
                            <div class="level-badge neon-glow" style="background-color: <?php echo $level_colors[$level]; ?>; color: white;">
                                <?php echo htmlspecialchars($level); ?>
                            </div>
                            <div class="course-icon"><i class="fas fa-graduation-cap"></i></div>
                            <h3 class="course-title">Nivel <?php echo htmlspecialchars($level); ?></h3>
                            <p class="course-subtitle">Ideal para <?php 
                                switch ($level) {
                                    case 'A1': echo 'principiantes absolutos.'; break;
                                    case 'A2': echo 'quienes tienen bases y quieren avanzar.'; break;
                                    case 'B1': echo 'usuarios intermedios que buscan fluidez.'; break;
                                    case 'B2': echo 'usuarios avanzados que perfeccionan su inglés.'; break;
                                    case 'C1': echo 'expertos que buscan maestría.'; break;
                                    default: echo 'todos los niveles.'; break;
                                }
                            ?></p>
                            <ul class="course-features">
                                <li>Acceso a todos los cursos de nivel <?php echo htmlspecialchars($level); ?></li>
                                <li>Material descargable</li>
                                <li>Ejercicios interactivos</li>
                            </ul>
                            <p class="course-price">
                                $<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?>
                                <?php if (isset($playlist['original_price']) && $playlist['original_price'] && $playlist['original_price'] > $playlist['price']): ?>
                                    <span class="original-price">$<?php echo htmlspecialchars(number_format($playlist['original_price'], 2)); ?></span>
                                    <span class="discount">-<?php echo round((($playlist['original_price'] - $playlist['price']) / $playlist['original_price']) * 100); ?>%</span>
                                <?php endif; ?>
                            </p>
                            <a href="all-courses.php#level-<?php echo strtolower($level); ?>" class="btn-primary">Ver Cursos</a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="view-more">
                <a href="all-courses.php">Explorar todos los niveles <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Call to action for all courses -->
    <section class="view-all-courses">
        <div class="container">
            <div class="view-all-content">
                <h2>¿Listo para llevar tu inglés al siguiente nivel?</h2>
                <p>Explora nuestra biblioteca completa de cursos y encuentra el camino perfecto hacia la fluidez.</p>
                <a href="all-courses.php" class="btn-large">
                    <i class="fas fa-book-open"></i> Ver Todos los Cursos
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Conoce al Profesor Hernán</h2>
                    <p>Con más de 15 años de experiencia, el Profesor Hernán ha ayudado a miles de estudiantes a alcanzar sus metas en inglés. Su metodología se centra en la práctica constante, la inmersión cultural y un enfoque personalizado para cada alumno.</p>
                    <div class="about-features">
                        <div class="feature-item">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <div>
                                <h4>Metodología Comprobada</h4>
                                <p>Clases dinámicas y efectivas diseñadas para el aprendizaje rápido.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <h4>Comunidad de Apoyo</h4>
                                <p>Únete a una red de estudiantes y practica con hablantes nativos.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-certificate"></i>
                            <div>
                                <h4>Certificación Reconocida</h4>
                                <p>Obtén certificados al completar tus cursos y valida tus habilidades.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="../../img/profe-hernan-about.png" alt="Profesor Hernán">
                </div>
            </div>
        </div>
    </section>

    <!-- Promo Box -->
    <section class="promo-box">
        <div class="container">
            <h2 class="promo-title">¡Oferta Especial! Obtén 20% de Descuento en tu Primer Curso</h2>
            <p>Usa el código **BIENVENIDO20** al finalizar la compra.</p>
            <a href="all-courses.php" class="btn-secondary">¡Aprovechar Ahora!</a>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact">
        <div class="container">
            <div class="contact-form">
                <h2>Contáctanos</h2>
                <p>¿Tienes preguntas? Envíanos un mensaje y te responderemos a la brevedad.</p>
                <form>
                    <div class="form-group">
                        <label for="name">Nombre Completo</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Asunto</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Tu Mensaje</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Enviar Mensaje</button>
                </form>
            </div>
            <div class="contact-info">
                <div class="info-item">
                    <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="text">
                        <h3>Nuestra Ubicación</h3>
                        <p>Calle Falsa 123, Ciudad Ficticia, País Imaginario</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="icon"><i class="fas fa-envelope"></i></div>
                    <div class="text">
                        <h3>Correo Electrónico</h3>
                        <p>info@profesorhernan.com</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="icon"><i class="fas fa-phone"></i></div>
                    <div class="text">
                        <h3>Teléfono</h3>
                        <p>+123 456 7890</p>
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
