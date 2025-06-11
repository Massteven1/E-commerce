<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        if (isset($playlist) && is_array($playlist) && isset($playlist['name'])) {
            echo htmlspecialchars($playlist['name']);
        } else {
            echo "Videos";
        }
        ?>
    </title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

    <header>
        <div class="container">
            <a href="#" class="logo">
                <div class="logo-circle" style="background-color: var(--primary-color);">
                    <i class="fas fa-video"></i>
                </div>
                <span>Video Admin</span>
            </a>
            <div class="search-bar">
                <input type="text" placeholder="Buscar listas o videos...">
                <i class="fas fa-search"></i>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php?controller=playlist&action=index">Inicio</a></li>
                    <li><a href="#">Configuración</a></li>
                    <li class="logout"><a href="#"><i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="playlist-view">
        <div class="container">
            <div class="playlist-header">
                <h1>
                    <?php
                    if (isset($playlist) && is_array($playlist) && isset($playlist['name'])) {
                        echo htmlspecialchars($playlist['name']);
                    } else {
                        echo "Videos";
                    }
                    ?>
                </h1>
                <p>
                    <?php
                    if (isset($playlist) && is_array($playlist) && isset($playlist['description'])) {
                        echo htmlspecialchars($playlist['description']);
                    } else {
                        echo "Descripción no disponible.";
                    }
                    ?>
                </p>
                <a href="index.php?controller=playlist&action=index" class="btn-secondary">Volver al Panel</a>
            </div>

            <div class="video-list">
                <h2>Videos en esta lista</h2>
                <?php if (empty($videos)): ?>
                    <p>No hay videos en esta lista.</p>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($videos as $video): ?>
                            <div class="product-card">
                                <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                                <video controls width="320" height="240">
                                    <source src="<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
                                    Tu navegador no soporta la reproducción de videos.
                                </video>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2024 Panel de Administración</p>
        </div>
    </footer>

    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

</body>
</html>
