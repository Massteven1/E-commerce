<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($playlist['name']) ? htmlspecialchars($playlist['name']) : 'Playlist'; ?></title>
    <link rel="stylesheet" href="/E-commerce/public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-circle" style="background-color: var(--primary-color);">V</div>
                <span style="margin-left: 10px; font-weight: 600;">Video Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="courses.php?controller=playlist&action=index">Inicio</a></li>
                    <li><a href="#">Configuración</a></li>
                    <li class="logout"><i class="fas fa-sign-out-alt"></i></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1><?php echo isset($playlist['name']) ? htmlspecialchars($playlist['name']) : 'Playlist no encontrada'; ?></h1>
        <p><?php echo isset($playlist['description']) ? htmlspecialchars($playlist['description']) : 'Sin descripción'; ?></p>

        <?php if (!empty($videos)): ?>
            <h2>Videos</h2>
            <ul class="products-grid">
                <?php foreach ($videos as $video): ?>
                    <li class="product-card">
                        <video width="320" height="240" controls>
                            <source src="<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
                            Tu navegador no soporta el elemento de video.
                        </video>
                        <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                        <p><?php echo htmlspecialchars($video['description']); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay videos en esta playlist.</p>
        <?php endif; ?>
    </div>

    <div class="back-to-top">
        <a href="courses.php?controller=playlist&action=index"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>