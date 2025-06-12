<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($playlist['name']) ? htmlspecialchars($playlist['name']) : 'Playlist'; ?></title>
    <!-- Enlace al nuevo archivo de estilos de administrador -->
    <link rel="stylesheet" href="../../public/css/admin.css">
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
                    <li><a href="courses.php?controller=admin&action=dashboard">Dashboard</a></li>
                    <li><a href="courses.php?controller=playlist&action=index">Cursos</a></li>
                    <li><a href="#">Configuración</a></li>
                    <li class="logout"><i class="fas fa-sign-out-alt"></i></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="checkout-section">
            <a href="courses.php?controller=playlist&action=index" style="display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; color: var(--primary-color); font-weight: 500;">
                <i class="fas fa-arrow-left"></i> Volver a Listas
            </a>
            <?php if (isset($playlist['cover_image']) && !empty($playlist['cover_image'])): ?>
                <!-- La ruta de la imagen debe ser relativa a la raíz del servidor web -->
                <img src="/<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="Portada de la lista" style="max-width: 300px; height: auto; margin: 0 auto 20px; display: block; border-radius: 10px; box-shadow: var(--shadow);">
            <?php else: ?>
                <p style="text-align: center; color: var(--dark-gray);">Sin imagen de portada</p>
            <?php endif; ?>
            <h1 style="margin-top: 20px; text-align: center;"><?php echo isset($playlist['name']) ? htmlspecialchars($playlist['name']) : 'Playlist no encontrada'; ?></h1>
            <p style="text-align: center; color: var(--dark-gray;"><?php echo isset($playlist['description']) ? htmlspecialchars($playlist['description']) : 'Sin descripción'; ?></p>
        </div>

        <?php if (!empty($videos)): ?>
            <div class="checkout-section">
                <h2>Videos</h2>
                <ul class="products-grid">
                    <?php foreach ($videos as $video): ?>
                        <li class="product-card">
                            <div class="product-tumb" style="height: auto;">
                                <!-- La ruta del video debe ser relativa a la raíz del servidor web -->
                                <video width="100%" controls style="border-radius: 8px;">
                                    <source src="/<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
                                    {"Tu navegador no soporta el elemento de video."}
                                </video>
                            </div>
                            <div class="product-details">
                                <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                                <p><?php echo htmlspecialchars($video['description']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--dark-gray);">No hay videos en esta playlist.</p>
        <?php endif; ?>
    </div>

    <div class="back-to-top">
        <a href="courses.php?controller=playlist&action=index"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>
