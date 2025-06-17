<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/Video.php';
require_once __DIR__ . '/../../models/UserCourse.php'; // Nuevo
require_once __DIR__ . '/../../controllers/AuthController.php'; // Nuevo

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);
$videoModel = new Video($db);
$userCourseModel = new UserCourse($db); // Nuevo

$playlist_id = $_GET['id'] ?? null;
$playlist = null;
$videos = [];

if ($playlist_id) {
    $playlist = $playlistModel->readOne($playlist_id);
    if ($playlist) {
        $videos = $videoModel->readByPlaylist($playlist_id);
    }
}

$isLoggedIn = AuthController::isAuthenticated(); // Nuevo
$currentUserId = $isLoggedIn ? AuthController::getCurrentUser()['id'] : null; // Nuevo
$hasAccess = false; // Nuevo
if ($isLoggedIn && $currentUserId && $playlist) { // Nuevo
    $hasAccess = $userCourseModel->hasAccess($currentUserId, $playlist['id']); // Nuevo
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($playlist['title'] ?? 'Course Detail'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/course-detail.css">
</head>
<body>

<?php include __DIR__ . '/../inc/header.php'; ?>

<main class="container course-detail">
    <div class="course-header">
        <h1><?php echo htmlspecialchars($playlist['title'] ?? 'Course Not Found'); ?></h1>
        <p><?php echo htmlspecialchars($playlist['description'] ?? ''); ?></p>
        <div class="course-actions">
            <?php if ($hasAccess): ?>
                <a href="#course-curriculum" class="btn-primary add-to-cart-btn">
                    <i class="fas fa-play-circle"></i> Acceder al Curso
                </a>
            <?php else: ?>
                <a href="cart.php?action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-primary add-to-cart-btn">
                    <i class="fas fa-shopping-cart"></i> Añadir al Carrito
                </a>
            <?php endif; ?>
        </div>
    </div>

    <section id="course-curriculum" class="course-curriculum">
        <h2>Currículum del Curso</h2>
        <?php if (empty($videos)): ?>
            <p>No hay videos disponibles para este curso.</p>
        <?php else: ?>
            <?php foreach ($videos as $video): ?>
                <div class="curriculum-item <?php echo $hasAccess ? '' : 'locked'; ?>">
                    <span class="item-title"><?php echo htmlspecialchars($video['title']); ?></span>
                    <?php if ($hasAccess): ?>
                        <a href="watch.php?id=<?php echo htmlspecialchars($video['id']); ?>" class="btn-secondary">
                            <i class="fas fa-play"></i> Ver video
                        </a>
                    <?php else: ?>
                        <span class="locked-icon"><i class="fas fa-lock"></i></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../inc/footer.php'; ?>

</body>
</html>
