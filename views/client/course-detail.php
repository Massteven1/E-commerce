<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar dependencias
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: home.php');
    exit();
}

$playlist = $playlistModel->readOne($id);
if (!$playlist) {
    header('Location: home.php');
    exit();
}

// Usar el nivel directamente de la base de datos
$level_badge = htmlspecialchars($playlist['level']);

// Determinar el color del badge basado en el nivel
$level_color = 'var(--primary-color)'; // Color por defecto
switch ($level_badge) {
    case 'A1': $level_color = 'var(--orange-color)'; break;
    case 'A2': $level_color = 'var(--red-color)'; break;
    case 'B1': $level_color = 'var(--blue-color)'; break;
    case 'B2': $level_color = 'var(--teal-color)'; break;
    case 'C1': $level_color = 'var(--purple-color)'; break;
    case 'mixto': $level_color = 'var(--primary-color)'; break;
}

// Determinar el nombre del nivel para mostrar
$level_name = '';
switch ($level_badge) {
    case 'A1': $level_name = 'Básico'; break;
    case 'A2': $level_name = 'Pre Intermedio'; break;
    case 'B1': $level_name = 'Intermedio'; break;
    case 'B2': $level_name = 'Intermedio Alto'; break;
    case 'C1': $level_name = 'Avanzado'; break;
    case 'mixto': $level_name = 'Mixto'; break;
    default: $level_name = 'Nivel ' . $level_badge; break;
}

// Calcular el descuento si hay precio original y actual
$original_price = 70.00; // Precio original estático para el ejemplo
$discount_percentage = 0;
if ($playlist['price'] < $original_price && $original_price > 0) {
    $discount_percentage = round((($original_price - $playlist['price']) / $original_price) * 100);
}
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

<!-- Breadcrumb Navigation -->
<div class="breadcrumb">
    <div class="container">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="home.php">Cursos</a></li>
            <li><?php echo htmlspecialchars($playlist['name']); ?></li>
        </ul>
    </div>
</div>

<!-- Course Detail Hero Section -->
<section class="course-detail-hero">
    <div class="container">
        <div class="course-detail-content">
            <div class="course-image-container">
                <?php if (!empty($playlist['cover_image'])): ?>
                    <img src="../../<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="<?php echo htmlspecialchars($playlist['name']); ?>">
                <?php else: ?>
                    <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                <?php endif; ?>
                <div class="level-badge neon-glow" style="background-color: <?php echo $level_color; ?>; color: white;">
                    <?php echo $level_badge; ?>
                </div>
            </div>
            <div class="course-info">
                <h1><?php echo htmlspecialchars($playlist['name']); ?></h1>
                <div class="course-meta">
                    <span class="course-meta-item"><i class="fas fa-clock"></i> 120 horas</span>
                    <span class="course-meta-item"><i class="fas fa-signal"></i> Nivel <?php echo $level_name; ?></span>
                </div>
                <div class="course-rating">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span>4.5 (128 reseñas)</span>
                </div>
                <p class="course-description">
                    <?php echo nl2br(htmlspecialchars($playlist['description'])); ?>
                </p>
                <div class="course-price-info">
                    <span class="current-price">$<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?></span>
                    <?php if ($discount_percentage > 0): ?>
                        <span class="original-price">$<?php echo htmlspecialchars(number_format($original_price, 2)); ?></span>
                        <span class="discount-percentage">-<?php echo $discount_percentage; ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="limited-offer">
                    <i class="fas fa-clock"></i> Oferta por tiempo limitado! Termina en 3 días
                </div>
                <div class="course-actions">
                    <?php if (!isset($_SESSION['cart'][$playlist['id']])): ?>
                        <a href="cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-primary">
                            <i class="fas fa-shopping-cart"></i> Añadir al Carrito
                        </a>
                    <?php else: ?>
                        <button class="btn-primary" disabled style="opacity: 0.6;">
                            <i class="fas fa-shopping-cart"></i> Ya en el Carrito
                        </button>
                    <?php endif; ?>
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
