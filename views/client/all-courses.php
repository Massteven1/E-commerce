<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/UserCourse.php'; // Nuevo
require_once __DIR__ . '/../../controllers/AuthController.php'; // Nuevo
require_once __DIR__ . '/../../controllers/CartController.php'; // Para el carrito

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);
$userCourseModel = new UserCourse($db); // Nuevo

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

$isLoggedIn = AuthController::isAuthenticated(); // Nuevo
$currentUserId = $isLoggedIn ? AuthController::getCurrentUser()['id'] : null; // Nuevo
?>

<section class="all-course-area section--padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-heading text-center">
                    <h2 class="sec__title">Todos Nuestros Cursos</h2>
                    <p class="sec__desc">Elige el curso que mejor se adapte a tus necesidades.</p>
                </div>
            </div>
        </div>
        <?php foreach ($playlistsByLevel as $level => $playlists): ?>
            <?php if (!empty($playlists)): ?>
                <div class="row mt-5">
                    <div class="col-lg-12">
                        <h3>Nivel <?php echo htmlspecialchars($level); ?></h3>
                    </div>
                </div>
                <div class="row mt-3">
                    <?php foreach ($playlists as $playlist): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="all-course-item">
                                <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="all-course-img">
                                    <img src="<?php echo htmlspecialchars($playlist['image_url']); ?>" alt="course">
                                </a>
                                <div class="all-course-content">
                                    <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="all-course-title">
                                        <h4><?php echo htmlspecialchars($playlist['title']); ?></h4>
                                    </a>
                                    <p><?php echo htmlspecialchars($playlist['description']); ?></p>
                                </div>
                                <?php
                                $hasAccess = false;
                                if ($isLoggedIn && $currentUserId) {
                                    $hasAccess = $userCourseModel->hasAccess($currentUserId, $playlist['id']);
                                }
                                ?>
                                <div class="all-course-overlay">
                                    <?php if ($hasAccess): ?>
                                        <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-overlay-view"><i class="fas fa-play-circle"></i> Acceder</a>
                                    <?php else: ?>
                                        <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-overlay-view"><i class="fas fa-eye"></i> Ver Detalles</a>
                                        <a href="cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-overlay-add"><i class="fas fa-shopping-cart"></i> Añadir al Carrito</a>
                                    <?php endif; ?>
                                </div>
                                <div class="all-course-bottom-details">
                                    <div class="all-course-price">$<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?></div>
                                    <?php if ($hasAccess): ?>
                                        <a href="course-detail.php?id=<?php echo htmlspecialchars($playlist['id']); ?>" class="all-add-to-cart-btn">Acceder</a>
                                    <?php else: ?>
                                        <a href="cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="all-add-to-cart-btn">Añadir al Carrito</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
