<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($video['title']); ?> - Video</title>
    <link rel="stylesheet" href="../../public/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .video-container {
            position: relative;
            width: 100%;
            background-color: #000;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .video-player {
            width: 100%;
            display: block;
        }
        .video-info {
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        .video-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .video-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: var(--dark-gray);
            font-size: 0.9rem;
        }
        .video-description {
            color: var(--text-color);
            line-height: 1.6;
        }
        .related-videos {
            margin-top: 30px;
        }
        .related-videos h3 {
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        .related-video-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--medium-gray);
        }
        .related-video-item:last-child {
            border-bottom: none;
        }
        .related-thumbnail {
            width: 120px;
            height: 68px;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .related-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .related-play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 30px;
            height: 30px;
            background-color: rgba(138, 86, 226, 0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .related-play-button i {
            color: white;
            font-size: 12px;
            margin-left: 2px;
        }
        .related-video-info {
            flex-grow: 1;
        }
        .related-video-title {
            font-weight: 600;
            margin-bottom: 5px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .related-video-duration {
            color: var(--dark-gray);
            font-size: 0.8rem;
        }
        .video-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .control-button {
            background-color: var(--light-gray);
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        .control-button:hover {
            background-color: var(--medium-gray);
        }
        .control-button i {
            font-size: 14px;
        }
        .video-layout {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 20px;
        }
        @media (max-width: 992px) {
            .video-layout {
                grid-template-columns: 1fr;
            }
        }
        .minimized {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            overflow: hidden;
        }
        .minimized .video-player {
            height: 169px; /* 16:9 ratio for 300px width */
        }
        .minimize-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .video-container:hover .minimize-overlay {
            opacity: 1;
        }
    </style>
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
        <div class="video-controls">
            <a href="courses.php?controller=video&action=view_playlist&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="control-button">
                <i class="fas fa-arrow-left"></i> Volver a la playlist
            </a>
            <button id="toggleMinimize" class="control-button">
                <i class="fas fa-compress"></i> Minimizar video
            </button>
        </div>

        <div class="video-layout">
            <div class="main-content">
                <div id="videoContainer" class="video-container">
                    <video id="videoPlayer" class="video-player" controls autoplay>
                        <source src="/<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
                        Tu navegador no soporta el elemento de video.
                    </video>
                    <div class="minimize-overlay" title="Minimizar">
                        <i class="fas fa-compress"></i>
                    </div>
                </div>

                <div class="video-info">
                    <h1 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h1>
                    <div class="video-meta">
                        <span>Playlist: <?php echo htmlspecialchars($playlist['name']); ?></span>
                        <span>Subido: <?php echo date('d/m/Y', strtotime($video['created_at'])); ?></span>
                    </div>
                    <p class="video-description"><?php echo htmlspecialchars($video['description']); ?></p>
                </div>

                <div class="comments-section" style="background-color: #fff; border-radius: 10px; padding: 20px; box-shadow: var(--shadow);">
                    <h3>Comentarios</h3>
                    <p style="color: var(--dark-gray);">Los comentarios se implementarán próximamente.</p>
                </div>
            </div>

            <div class="sidebar">
                <div class="related-videos">
                    <h3>Videos relacionados</h3>
                    <?php if (!empty($related_videos)): ?>
                        <?php foreach ($related_videos as $related): ?>
                            <a href="courses.php?controller=video&action=view_video&id=<?php echo htmlspecialchars($related['id']); ?>" class="related-video-item">
                                <div class="related-thumbnail">
                                    <?php if (!empty($related['thumbnail_image'])): ?>
                                        <img src="/<?php echo htmlspecialchars($related['thumbnail_image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                                    <?php else: ?>
                                        <img src="https://i.imgur.com/xdbHo4E.png" alt="Miniatura por defecto">
                                    <?php endif; ?>
                                    <div class="related-play-button">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </div>
                                <div class="related-video-info">
                                    <h4 class="related-video-title"><?php echo htmlspecialchars($related['title']); ?></h4>
                                    <span class="related-video-duration">3:45</span> <!-- Duración simulada -->
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--dark-gray);">No hay videos relacionados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const videoContainer = document.getElementById('videoContainer');
            const videoPlayer = document.getElementById('videoPlayer');
            const toggleButton = document.getElementById('toggleMinimize');
            const minimizeOverlay = document.querySelector('.minimize-overlay');
            let isMinimized = false;

            function toggleMinimize() {
                if (isMinimized) {
                    videoContainer.classList.remove('minimized');
                    toggleButton.innerHTML = '<i class="fas fa-compress"></i> Minimizar video';
                } else {
                    videoContainer.classList.add('minimized');
                    toggleButton.innerHTML = '<i class="fas fa-expand"></i> Maximizar video';
                }
                isMinimized = !isMinimized;
            }

            toggleButton.addEventListener('click', toggleMinimize);
            minimizeOverlay.addEventListener('click', toggleMinimize);

            const links = document.querySelectorAll('.related-video-item');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    sessionStorage.setItem('lastVideoTime', videoPlayer.currentTime);
                });
            });
        });
    </script>

    <div class="back-to-top">
        <a href="#"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>
