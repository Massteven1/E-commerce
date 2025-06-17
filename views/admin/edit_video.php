<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Video: <?php echo htmlspecialchars($video['title']); ?></title>
    <link rel="stylesheet" href="../../public/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-circle" style="background-color: var(--primary-color);">V</div>
                <span>Video Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php?controller=admin&action=dashboard">Dashboard</a></li>
                    <li><a href="index.php?controller=playlist&action=index">Cursos</a></li>
                    <li><a href="#">Configuración</a></li>
                </ul>
            </nav>
          <a href="logout.php" class="logout" id="logoutBtn">
              <i class="fas fa-sign-out-alt"></i>
          </a>
        </div>
    </header>

    <div class="banner">
        <div class="container">
            <h1>Editar Video</h1>
            <p>Modifica los detalles del video "<?php echo htmlspecialchars($video['title']); ?>".</p>
        </div>
    </div>

    <div class="container">
        <div class="checkout-section">
            <h2>Formulario de Edición</h2>
            <form action="index.php?controller=video&action=update_video" method="post" enctype="multipart/form-data" class="form-row">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($video['id']); ?>">

                <input type="text" name="title" placeholder="Título del Video" value="<?php echo htmlspecialchars($video['title']); ?>" required>
                <textarea name="description" placeholder="Descripción"><?php echo htmlspecialchars($video['description']); ?></textarea>
                
                <select name="playlist_id" required>
                    <option value="">Seleccionar Lista</option>
                    <?php if (!empty($playlists)): ?>
                        <?php foreach ($playlists as $playlist_option): ?>
                            <option value="<?php echo htmlspecialchars($playlist_option['id']); ?>" <?php echo ($playlist_option['id'] == $video['playlist_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($playlist_option['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="thumbnail_image">Miniatura del Video (Imagen de Portada)</label>
                    <?php if (!empty($video['thumbnail_image'])): ?>
                        <img src="../../<?php echo htmlspecialchars($video['thumbnail_image']); ?>" alt="Miniatura actual" style="max-width: 200px; height: auto; display: block; margin-top: 10px; border-radius: 5px;">
                    <?php else: ?>
                        <p>No hay miniatura actual.</p>
                    <?php endif; ?>
                    <input type="file" id="thumbnail_image" name="thumbnail_image" accept="image/jpeg,image/png">
                    <p style="font-size: 0.9em; color: var(--dark-gray); margin-top: 5px;">Sube una nueva imagen (JPG, JPEG, PNG) para la portada del video. Deja vacío para mantener la actual.</p>
                </div>

                <button type="submit" class="btn-primary" style="grid-column: 1 / -1;">Actualizar Video</button>
            </form>
        </div>
    </div>

    <div class="back-to-top">
        <a href="index.php?controller=video&action=view_playlist&id=<?php echo htmlspecialchars($video['playlist_id']); ?>"><i class="fas fa-arrow-up"></i></a>
    </div>

    <script src="../../auth/firebase-config.js"></script>
    <script src="../../auth/auth.js"></script>
</body>
</html>
