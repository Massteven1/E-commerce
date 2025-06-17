<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/UserCourse.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/CartController.php';

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);
$userCourseModel = new UserCourse($db);

$allPlaylists = $playlistModel->readAll();

$playlistsByLevel = [
    'A1' => [], 'A2' => [], 'B1' => [], 'B2' => [], 'C1' => [], 'Mixto' => []
];

foreach ($allPlaylists as $playlist) {
    $level = $playlist['level'] ?? 'Mixto';
    if (isset($playlistsByLevel[$level])) {
        $playlistsByLevel[$level][] = $playlist;
    } else {
        $playlistsByLevel['Mixto'][] = $playlist;
    }
}

$isLoggedIn = AuthController::isAuthenticated();
$currentUserId = $isLoggedIn ? AuthController::getCurrentUser()['id'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos Nuestros Cursos - E-commerce de Cursos de Inglés</title>
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Estilos CSS principales -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/styles.css">
    <!-- Estilos específicos de la página de detalles del curso (si aplica, aunque styles.css ya es general) -->
    <!-- <link rel="stylesheet" href="/public/css/course-detail.css"> -->
</head>
<body>
    <section class="all-courses-section">
        <div class="container">
            <h1 class="all-courses-section-title">Todos Nuestros Cursos</h1>
            <p class="section-subtitle">Elige el curso que mejor se adapte a tus necesidades.</p>

            <?php foreach ($playlistsByLevel as $level => $playlists): ?>
                <?php if (!empty($playlists)): ?>
                    <div class="level-section <?php echo strtolower($level); ?>">
                        <h2 class="level-section-title">Nivel <?php echo htmlspecialchars($level); ?></h2>
                        <div class="all-courses-grid">
                            <?php foreach ($playlists as $playlist): ?>
                                <div class="all-course-card">
                                    <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="all-course-tumb">
                                        <?php if (!empty($playlist['cover_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $playlist['cover_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="<?php echo htmlspecialchars($playlist['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-play-circle fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                    <div class="all-course-details">
                                        <span class="all-course-catagory"><?php echo htmlspecialchars($playlist['level']); ?></span>
                                        <h4><a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>"><?php echo htmlspecialchars($playlist['name']); ?></a></h4>
                                        <p><?php echo htmlspecialchars($playlist['description']); ?></p>
                                        <div class="all-course-bottom-details">
                                            <div class="all-course-price">$<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?></div>
                                            <?php
                                            $hasAccess = false;
                                            if ($isLoggedIn && $currentUserId) {
                                                $hasAccess = $userCourseModel->hasAccess($currentUserId, $playlist['id']);
                                            }
                                            ?>
                                            <?php if ($hasAccess): ?>
                                                <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="all-add-to-cart-btn">Acceder</a>
                                            <?php else: ?>
                                                <a href="cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="all-add-to-cart-btn">Añadir al Carrito</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="all-course-overlay">
                                        <?php if ($hasAccess): ?>
                                            <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-overlay-view"><i class="fas fa-play-circle"></i> Acceder</a>
                                        <?php else: ?>
                                            <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-overlay-view"><i class="fas fa-eye"></i> Ver Detalles</a>
                                            <a href="cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-overlay-add"><i class="fas fa-shopping-cart"></i> Añadir al Carrito</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
</body>
</html>
