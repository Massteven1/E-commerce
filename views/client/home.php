<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar dependencias directamente
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);

// Obtener todos los cursos
$playlists = $playlistModel->readAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Profesor Hernán - Tienda de Cursos</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="../../public/css/course-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
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

    <section class="banner">
        <div class="container">
            <div class="banner-content">
                <div class="banner-text">
                    <h1>Bienvenido a tu Tienda de Cursos</h1>
                    <p>Explora todos nuestros cursos disponibles y comienza tu aprendizaje</p>
                    <div class="banner-buttons">
                        <a href="#cursos" class="btn-primary">VER CURSOS</a>
                        <a href="cart.php" class="btn-secondary">IR AL CARRITO</a>
                    </div>
                </div>
                <div class="banner-image">
                    <div class="image-container">
                        <img src="../../img/hero-image.png?height=300&width=300" alt="Person teaching">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="cursos" class="best-sellers">
        <div class="container">
            <h2>Todos Nuestros Cursos</h2>

            <div class="products-grid">
                <?php if (!empty($playlists)): ?>
                    <?php foreach ($playlists as $playlist): ?>
                        <div class="product-card">
                            <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="product-tumb">
                                <?php if (!empty($playlist['cover_image'])): ?>
                                    <img src="../../<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="<?php echo htmlspecialchars($playlist['name']); ?>">
                                <?php else: ?>
                                    <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                                <?php endif; ?>
                            </a>
                            <div class="product-details">
                                <span class="product-catagory">Nivel <?php echo htmlspecialchars($playlist['level']); ?></span>
                                <h4><a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>"><?php echo htmlspecialchars($playlist['name']); ?></a></h4>
                                <p><?php echo htmlspecialchars($playlist['description'] ?: 'Sin descripción'); ?></p>
                                <div class="product-bottom-details">
                                    <div class="product-price">$<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?></div>
                                    <div class="product-links">
                                        <?php if (!isset($_SESSION['cart'][$playlist['id']])): ?>
                                            <a href="cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="add-to-cart-btn">Añadir al Carrito</a>
                                        <?php else: ?>
                                            <button class="add-to-cart-btn" disabled style="opacity: 0.6;">Ya en el Carrito</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No hay cursos disponibles en este momento.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sort-options">
                <span>Ordenar por:</span>
                <button class="sort-btn active">Novedades</button>
                <button class="sort-btn">Ofertas</button>
                <button class="sort-btn">Todos los artículos</button>
            </div>
        </div>
    </section>

    <section class="courses">
        <div class="container">
            <h2>Cursos por Nivel</h2>

            <div class="courses-grid">
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--orange-color); color: white;">A1</div>
                    <div class="course-icon"><i class="fas fa-book"></i></div>
                    <h3 class="course-title">BÁSICO</h3>
                    <p class="course-subtitle">Nivel Básico</p>
                    <p class="course-price">$55 <span class="original-price">$70</span> <span class="discount">-22%</span></p>
                </div>
                
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--red-color); color: white;">A2</div>
                    <div class="course-icon"><i class="fas fa-comments"></i></div>
                    <h3 class="course-title">PRE INTERMEDIO</h3>
                    <p class="course-subtitle">Nivel Pre Intermedio</p>
                    <p class="course-price">$55</p>
                </div>
                
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--blue-color); color: white;">B1</div>
                    <div class="course-icon"><i class="fas fa-pen"></i></div>
                    <h3 class="course-title">INTERMEDIO</h3>
                    <p class="course-subtitle">Nivel Intermedio</p>
                    <p class="course-price">$55</p>
                </div>
                
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--teal-color); color: white;">B2</div>
                    <div class="course-icon"><i class="fas fa-microphone"></i></div>
                    <h3 class="course-title">INTERMEDIO ALTO</h3>
                    <p class="course-subtitle">Nivel Intermedio Alto</p>
                    <p class="course-price">$55</p>
                </div>
                
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--purple-color); color: white;">C1</div>
                    <div class="course-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3 class="course-title">AVANZADO</h3>
                    <p class="course-subtitle">Nivel Avanzado</p>
                    <p class="course-price">$55</p>
                </div>
            </div>
        </div>
    </section>

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

    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="../../auth/firebase-config.js"></script>
    <script src="../../auth/auth.js"></script>
</body>
</html>
