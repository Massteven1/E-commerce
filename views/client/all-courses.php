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

// Organizar cursos por nivel
$coursesByLevel = [];
foreach ($playlists as $playlist) {
    $level = $playlist['level'];
    if (!isset($coursesByLevel[$level])) {
        $coursesByLevel[$level] = [];
    }
    $coursesByLevel[$level][] = $playlist;
}

// Definir el orden de los niveles y sus colores
$levelOrder = ['A1', 'A2', 'B1', 'B2', 'C1', 'mixto'];
$levelColors = [
    'A1' => 'var(--orange-color)',
    'A2' => 'var(--red-color)',
    'B1' => 'var(--blue-color)',
    'B2' => 'var(--teal-color)',
    'C1' => 'var(--purple-color)',
    'mixto' => 'var(--primary-color)'
];
$levelNames = [
    'A1' => 'Básico',
    'A2' => 'Pre Intermedio',
    'B1' => 'Intermedio',
    'B2' => 'Intermedio Alto',
    'C1' => 'Avanzado',
    'mixto' => 'Mixto'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos los Cursos - El Profesor Hernán</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="../../public/css/course-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .all-courses-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .all-courses-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .all-courses-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .level-section {
            padding: 40px 0;
            border-bottom: 1px solid #eee;
        }
        
        .level-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
        }
        
        .level-badge-large {
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 1.3rem;
            font-weight: bold;
            color: white;
            min-width: 70px;
            text-align: center;
        }
        
        .level-info h2 {
            font-size: 2rem;
            margin: 0;
            color: var(--text-color);
        }
        
        .level-info p {
            font-size: 1rem;
            color: var(--text-light);
            margin: 5px 0 0 0;
        }
        
        .courses-grid-level {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .course-card-detailed {
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: fit-content;
        }
        
        .course-card-detailed:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }
        
        .course-image-wrapper {
            position: relative;
            height: 140px;
            overflow: hidden;
        }
        
        .course-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .course-level-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
        }
        
        .course-content {
            padding: 18px;
        }
        
        .course-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
            line-height: 1.3;
        }
        
        .course-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .course-title a:hover {
            color: var(--primary-color);
        }
        
        .course-description {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8em;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .course-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .course-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.85rem;
            color: var(--text-light);
        }
        
        .stars {
            color: #ffc107;
        }
        
        .course-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-view-course {
            flex: 1;
            padding: 10px 12px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        
        .btn-view-course:hover {
            background: var(--primary-dark);
        }
        
        .btn-add-cart {
            padding: 10px 15px;
            background: var(--secondary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-add-cart:hover {
            background: var(--secondary-dark);
        }
        
        .btn-add-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .no-courses {
            text-align: center;
            padding: 40px;
            color: var(--text-light);
        }
        
        .back-to-home {
            text-align: center;
            padding: 40px 0;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        
        .btn-back:hover {
            background: var(--primary-dark);
        }
    </style>
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
                    <li><a href="all-courses.php" class="active">Cursos</a></li>
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

    <!-- Hero Section -->
    <section class="all-courses-hero">
        <div class="container">
            <h1>Todos Nuestros Cursos</h1>
            <p>Descubre nuestra colección completa de cursos de inglés organizados por niveles. Desde principiante hasta avanzado, tenemos el curso perfecto para ti.</p>
        </div>
    </section>

    <!-- Courses by Level -->
    <?php if (!empty($coursesByLevel)): ?>
        <?php foreach ($levelOrder as $level): ?>
            <?php if (isset($coursesByLevel[$level]) && !empty($coursesByLevel[$level])): ?>
                <section class="level-section">
                    <div class="container">
                        <div class="level-header">
                            <div class="level-badge-large" style="background-color: <?php echo $levelColors[$level]; ?>;">
                                <?php echo $level; ?>
                            </div>
                            <div class="level-info">
                                <h2>Nivel <?php echo $levelNames[$level]; ?></h2>
                                <p><?php echo count($coursesByLevel[$level]); ?> curso<?php echo count($coursesByLevel[$level]) > 1 ? 's' : ''; ?> disponible<?php echo count($coursesByLevel[$level]) > 1 ? 's' : ''; ?></p>
                            </div>
                        </div>
                        
                        <div class="courses-grid-level">
                            <?php foreach ($coursesByLevel[$level] as $playlist): ?>
                                <div class="course-card-detailed">
                                    <div class="course-image-wrapper">
                                        <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>">
                                            <?php if (!empty($playlist['cover_image'])): ?>
                                                <img src="../../<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="<?php echo htmlspecialchars($playlist['name']); ?>">
                                            <?php else: ?>
                                                <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                                            <?php endif; ?>
                                        </a>
                                        <div class="course-level-badge" style="background-color: <?php echo $levelColors[$level]; ?>;">
                                            <?php echo $level; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="course-content">
                                        <h3 class="course-title">
                                            <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>">
                                                <?php echo htmlspecialchars($playlist['name']); ?>
                                            </a>
                                        </h3>
                                        
                                        <p class="course-description">
                                            <?php echo htmlspecialchars($playlist['description'] ?: 'Curso completo de inglés diseñado para llevarte al siguiente nivel en tu aprendizaje del idioma.'); ?>
                                        </p>
                                        
                                        <div class="course-meta">
                                            <div class="course-price">
                                                $<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?>
                                            </div>
                                            <div class="course-rating">
                                                <div class="stars">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star-half-alt"></i>
                                                </div>
                                                <span>4.5</span>
                                            </div>
                                        </div>
                                        
                                        <div class="course-actions">
                                            <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-view-course">
                                                Ver Detalles
                                            </a>
                                            <?php if (!isset($_SESSION['cart'][$playlist['id']])): ?>
                                                <a href="cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-add-cart">
                                                    <i class="fas fa-cart-plus"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn-add-cart" disabled title="Ya en el carrito">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="level-section">
            <div class="container">
                <div class="no-courses">
                    <h2>No hay cursos disponibles</h2>
                    <p>Pronto tendremos nuevos cursos disponibles para ti.</p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Back to Home -->
    <section class="back-to-home">
        <div class="container">
            <a href="home.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver al Inicio
            </a>
        </div>
    </section>

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

    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="../../auth/firebase-config.js"></script>
    <script src="../../auth/auth.js"></script>
</body>
</html>
