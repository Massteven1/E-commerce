<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo isset($playlist['name']) ? htmlspecialchars($playlist['name']) : 'Playlist'; ?></title>
  <link rel="stylesheet" href="../../public/css/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
      .video-thumbnail {
          position: relative;
          cursor: pointer;
          overflow: hidden;
          border-radius: 8px;
          display: block;
      }
      .video-thumbnail img {
          width: 100%;
          height: auto;
          transition: transform 0.3s ease;
      }
      .video-thumbnail:hover img {
          transform: scale(1.05);
      }
      .play-button {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          width: 60px;
          height: 60px;
          background-color: rgba(138, 86, 226, 0.8);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: all 0.3s ease;
      }
      .play-button i {
          color: white;
          font-size: 24px;
          margin-left: 4px;
      }
      .video-thumbnail:hover .play-button {
          background-color: var(--primary-color);
          transform: translate(-50%, -50%) scale(1.1);
      }
      .video-duration {
          position: absolute;
          bottom: 10px;
          right: 10px;
          background-color: rgba(0, 0, 0, 0.7);
          color: white;
          padding: 2px 6px;
          border-radius: 4px;
          font-size: 12px;
      }
      .video-title {
          font-weight: 600;
          margin-top: 10px;
          margin-bottom: 5px;
      }
      .video-description {
          color: var(--dark-gray);
          font-size: 0.9em;
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;
          text-overflow: ellipsis;
      }
  </style>
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

    <div class="container">
        <div class="checkout-section">
            <a href="index.php?controller=playlist&action=index" style="display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; color: var(--primary-color); font-weight: 500;">
                <i class="fas fa-arrow-left"></i> Volver a Listas
            </a>
            <?php if (isset($playlist['cover_image']) && !empty($playlist['cover_image'])): ?>
                <img src="../../<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="Portada de la lista" style="max-width: 300px; height: auto; margin: 0 auto 20px; display: block; border-radius: 10px; box-shadow: var(--shadow);">
            <?php else: ?>
                <p style="text-align: center; color: var(--dark-gray);">Sin imagen de portada</p>
            <?php endif; ?>
            <h1 style="margin-top: 20px; text-align: center;"><?php echo isset($playlist['name']) ? htmlspecialchars($playlist['name']) : 'Playlist no encontrada'; ?></h1>
            <p style="text-align: center; color: var(--dark-gray);"><?php echo isset($playlist['description']) ? htmlspecialchars($playlist['description']) : 'Sin descripción'; ?></p>
            <p style="text-align: center; color: var(--primary-color); font-weight: 600; font-size: 1.2rem;">Precio: $<?php echo isset($playlist['price']) ? htmlspecialchars(number_format($playlist['price'], 2)) : 'N/A'; ?></p>
        </div>

        <?php if (!empty($videos)): ?>
            <div class="checkout-section">
                <h2>Videos</h2>
                <ul class="products-grid">
                    <?php foreach ($videos as $video): ?>
                        <li class="product-card">
                            <a href="index.php?controller=video&action=view_video&id=<?php echo htmlspecialchars($video['id']); ?>" class="video-thumbnail">
                                <?php if (!empty($video['thumbnail_image'])): ?>
                                    <img src="../../<?php echo htmlspecialchars($video['thumbnail_image']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>">
                                <?php else: ?>
                                    <img src="https://i.imgur.com/xdbHo4E.png" alt="Miniatura por defecto">
                                <?php endif; ?>
                                <div class="play-button">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div class="video-duration">3:45</div>
                            </a>
                            <div class="product-details">
                                <h4 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h4>
                                <p class="video-description"><?php echo htmlspecialchars($video['description']); ?></p>
                                <div class="product-bottom-details">
                                    <div class="product-links">
                                        <a href="index.php?controller=video&action=edit_video&id=<?php echo htmlspecialchars($video['id']); ?>" title="Editar Video"><i class="fa fa-edit"></i></a>
                                        <a href="index.php?controller=video&action=view_video&id=<?php echo htmlspecialchars($video['id']); ?>" title="Ver Video"><i class="fa fa-eye"></i></a>
                                        <a href="index.php?controller=video&action=delete_video&id=<?php echo htmlspecialchars($video['id']); ?>" title="Eliminar Video" onclick="return confirm('¿Estás seguro de que quieres eliminar este video? Esta acción es irreversible.');"><i class="fa fa-trash-alt" style="color: var(--red-color);"></i></a>
                                    </div>
                                </div>
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
        <a href="index.php?controller=playlist&action=index"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>
