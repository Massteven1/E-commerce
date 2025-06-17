<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/Video.php';
require_once __DIR__ . '/../../models/UserCourse.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);
$videoModel = new Video($db);
$userCourseModel = new UserCourse($db);

$playlist_id = $_GET['id'] ?? null;
$playlist = null;
$videos = [];

if ($playlist_id) {
    $playlist = $playlistModel->readOne($playlist_id);
    if ($playlist) {
        $videos = $videoModel->readByPlaylist($playlist_id);
    }
}

$isLoggedIn = AuthController::isAuthenticated();
$currentUserId = $isLoggedIn ? AuthController::getCurrentUser()['id'] : null;
$hasAccess = false;
if ($isLoggedIn && $currentUserId && $playlist) {
    $hasAccess = $userCourseModel->hasAccess($currentUserId, $playlist['id']);
}

// Establecer el título de la página para el header
$pageTitle = $playlist ? htmlspecialchars($playlist['name']) . ' - Detalles del Curso' : 'Curso No Encontrado';

// Incluir el header
include __DIR__ . '/../inc/header.php';
?>

<main class="container course-detail-page">
    <?php if ($playlist): ?>
        <section class="course-header-section">
            <div class="course-header-content">
                <h1 class="course-title"><?php echo htmlspecialchars($playlist['name']); ?></h1>
                <p class="course-description"><?php echo htmlspecialchars($playlist['description']); ?></p>
                <div class="course-meta">
                    <span class="level-badge"><?php echo htmlspecialchars($playlist['level']); ?></span>
                    <span class="course-price">$<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?></span>
                </div>
                <div class="course-actions">
                    <?php if ($hasAccess): ?>
                        <a href="#course-curriculum" class="btn-primary add-to-cart-btn">
                            <i class="fas fa-play-circle"></i> Acceder al Curso
                        </a>
                        <span class="access-status"><i class="fas fa-check-circle"></i> Ya tienes acceso a este curso</span>
                    <?php else: ?>
                        <a href="/views/client/cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" 
                           class="btn-primary add-to-cart-btn">
                            <i class="fas fa-shopping-cart"></i> Añadir al Carrito
                        </a>
                        <?php if (!$isLoggedIn): ?>
                            <p class="login-prompt">
                                <i class="fas fa-info-circle"></i> 
                                <a href="/login.php">Inicia sesión</a> para comprar este curso
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="course-header-image">
                <?php if (!empty($playlist['cover_image'])): ?>
                    <img src="<?php echo htmlspecialchars($playlist['cover_image']); ?>" 
                         alt="<?php echo htmlspecialchars($playlist['name']); ?>" 
                         class="course-cover-image">
                <?php else: ?>
                    <div class="placeholder-image">
                        <i class="fas fa-image"></i>
                        <span>No Image</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="course-curriculum" class="course-curriculum-section">
            <h2 class="section-title">Contenido del Curso</h2>
            <?php if (empty($videos)): ?>
                <div class="info-message">
                    <i class="fas fa-info-circle"></i> No hay videos disponibles para este curso.
                </div>
            <?php else: ?>
                <div class="curriculum-list">
                    <?php foreach ($videos as $index => $video): ?>
                        <div class="curriculum-item <?php echo $hasAccess ? '' : 'locked'; ?>">
                            <div class="video-info">
                                <h3 class="video-title">
                                    <span class="video-number"><?php echo $index + 1; ?>.</span>
                                    <?php echo htmlspecialchars($video['title']); ?>
                                </h3>
                                <?php if (!empty($video['description'])): ?>
                                    <p class="video-description"><?php echo htmlspecialchars($video['description']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($video['duration'])): ?>
                                    <span class="video-duration">
                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($video['duration']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="video-actions">
                                <?php if ($hasAccess): ?>
                                    <a href="/views/client/watch.php?id=<?php echo htmlspecialchars($video['id']); ?>" 
                                       class="btn-secondary">
                                        <i class="fas fa-play"></i> Ver Video
                                    </a>
                                <?php else: ?>
                                    <span class="locked-status">
                                        <i class="fas fa-lock"></i> Bloqueado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <div class="error-message">
            <h4><i class="fas fa-exclamation-triangle"></i> Curso No Encontrado</h4>
            <p>El curso que buscas no existe o ha sido eliminado.</p>
            <a href="/views/client/all-courses.php" class="btn-primary">Ver Todos los Cursos</a>
        </div>
    <?php endif; ?>
</main>

<?php
// Incluir el footer
include __DIR__ . '/../inc/footer.php';
?>
