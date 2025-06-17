<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>
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

/* Custom Video Controls */
.custom-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    padding: 20px 15px 15px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 10;
    display: none; /* Oculto por defecto hasta que el video esté listo */
}

.video-container:hover .custom-controls {
    opacity: 1;
}

.custom-controls.ready {
    display: block;
}

.progress-container {
    width: 100%;
    height: 6px;
    background-color: rgba(255,255,255,0.3);
    border-radius: 3px;
    margin-bottom: 15px;
    cursor: pointer;
    position: relative;
}

.progress-bar {
    height: 100%;
    background-color: var(--primary-color);
    border-radius: 3px;
    width: 0%;
    transition: width 0.1s ease;
}

.buffer-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background-color: rgba(255,255,255,0.5);
    border-radius: 3px;
    width: 0%;
}

.controls-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
}

.controls-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.controls-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.control-btn {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.control-btn:hover {
    background-color: rgba(255,255,255,0.2);
    transform: scale(1.1);
}

.control-btn.play-pause {
    font-size: 24px;
}

.time-display {
    font-size: 14px;
    font-weight: 500;
    min-width: 120px;
}

.volume-container {
    display: flex;
    align-items: center;
    gap: 8px;
}

.volume-slider {
    width: 80px;
    height: 4px;
    background-color: rgba(255,255,255,0.3);
    border-radius: 2px;
    outline: none;
    cursor: pointer;
}

.volume-slider::-webkit-slider-thumb {
    appearance: none;
    width: 14px;
    height: 14px;
    background-color: var(--primary-color);
    border-radius: 50%;
    cursor: pointer;
}

.volume-slider::-moz-range-thumb {
    width: 14px;
    height: 14px;
    background-color: var(--primary-color);
    border-radius: 50%;
    cursor: pointer;
    border: none;
}

/* Loading Spinner */
.loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    border: 4px solid rgba(255,255,255,0.3);
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 5;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Loading Overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0,0,0,0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    z-index: 15;
}

.loading-text {
    margin-top: 20px;
    font-size: 16px;
    font-weight: 500;
}

.loading-progress {
    margin-top: 10px;
    width: 200px;
    height: 4px;
    background-color: rgba(255,255,255,0.3);
    border-radius: 2px;
    overflow: hidden;
}

.loading-progress-bar {
    height: 100%;
    background-color: var(--primary-color);
    width: 0%;
    transition: width 0.3s ease;
}

/* Resto de estilos existentes... */
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
    height: 169px;
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
                    <li><a href="index.php?controller=admin&action=dashboard">Dashboard</a></li>
                    <li><a href="index.php?controller=playlist&action=index">Cursos</a></li>
                    <li><a href="#">Configuración</a></li>
                    <li class="logout" id="logoutBtn"><i class="fas fa-sign-out-alt"></i></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="video-controls">
            <a href="index.php?controller=video&action=view_playlist&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="control-button">
                <i class="fas fa-arrow-left"></i> Volver a la playlist
            </a>
            <button id="toggleMinimize" class="control-button">
                <i class="fas fa-compress"></i> Minimizar video
            </button>
        </div>

        <div class="video-layout">
            <div class="main-content">
                <div id="videoContainer" class="video-container">
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Cargando video...</div>
        <div class="loading-progress">
            <div id="loadingProgressBar" class="loading-progress-bar"></div>
        </div>
    </div>
    
    <!-- Video Element -->
    <video id="videoPlayer" class="video-player" preload="metadata" muted>
        <source src="../../<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>
    
    <!-- Custom Controls -->
    <div id="customControls" class="custom-controls">
        <!-- Progress Bar -->
        <div id="progressContainer" class="progress-container">
            <div id="bufferBar" class="buffer-bar"></div>
            <div id="progressBar" class="progress-bar"></div>
        </div>
        
        <!-- Controls Row -->
        <div class="controls-row">
            <div class="controls-left">
                <button id="playPauseBtn" class="control-btn play-pause">
                    <i class="fas fa-play"></i>
                </button>
                <button id="rewindBtn" class="control-btn" title="Retroceder 10s">
                    <i class="fas fa-backward"></i>
                </button>
                <button id="forwardBtn" class="control-btn" title="Adelantar 10s">
                    <i class="fas fa-forward"></i>
                </button>
                <div id="timeDisplay" class="time-display">0:00 / 0:00</div>
            </div>
            <div class="controls-right">
                <div class="volume-container">
                    <button id="muteBtn" class="control-btn">
                        <i class="fas fa-volume-up"></i>
                    </button>
                    <input type="range" id="volumeSlider" class="volume-slider" min="0" max="1" step="0.1" value="1">
                </div>
                <button id="fullscreenBtn" class="control-btn">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </div>
    </div>
    
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
                            <a href="index.php?controller=video&action=view_video&id=<?php echo htmlspecialchars($related['id']); ?>" class="related-video-item">
                                <div class="related-thumbnail">
                                    <?php if (!empty($related['thumbnail_image'])): ?>
                                        <!-- Corregir la ruta: usar ruta relativa desde la vista -->
                                        <img src="../../<?php echo htmlspecialchars($related['thumbnail_image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                                    <?php else: ?>
                                        <img src="https://i.imgur.com/xdbHo4E.png" alt="Miniatura por defecto">
                                    <?php endif; ?>
                                    <div class="related-play-button">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </div>
                                <div class="related-video-info">
                                    <h4 class="related-video-title"><?php echo htmlspecialchars($related['title']); ?></h4>
                                    <span class="related-video-duration">3:45</span>
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
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingProgressBar = document.getElementById('loadingProgressBar');
    const customControls = document.getElementById('customControls');
    const toggleButton = document.getElementById('toggleMinimize');
    const minimizeOverlay = document.querySelector('.minimize-overlay');
    
    // Control elements
    const playPauseBtn = document.getElementById('playPauseBtn');
    const rewindBtn = document.getElementById('rewindBtn');
    const forwardBtn = document.getElementById('forwardBtn');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const bufferBar = document.getElementById('bufferBar');
    const timeDisplay = document.getElementById('timeDisplay');
    const muteBtn = document.getElementById('muteBtn');
    const volumeSlider = document.getElementById('volumeSlider');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    
    let isMinimized = false;
    let isVideoReady = false;
    let controlsTimeout;
    let videoDuration = 0;
    let loadingCheckInterval;

    // Función para forzar la carga completa del video
    function forceVideoLoad() {
        return new Promise((resolve, reject) => {
            console.log('Iniciando carga forzada del video...');
            
            // Configurar el video para carga completa
            videoPlayer.preload = 'auto';
            videoPlayer.load();
            
            let attempts = 0;
            const maxAttempts = 100; // 10 segundos máximo
            
            const checkLoading = () => {
                attempts++;
                console.log(`Intento ${attempts}: readyState=${videoPlayer.readyState}, duration=${videoPlayer.duration}`);
                
                // Verificar si tenemos la duración y el video está listo
                if (videoPlayer.duration && videoPlayer.duration > 0 && !isNaN(videoPlayer.duration)) {
                    videoDuration = videoPlayer.duration;
                    console.log('✅ Video cargado exitosamente, duración:', videoDuration);
                    clearInterval(loadingCheckInterval);
                    resolve(videoDuration);
                    return;
                }
                
                // Si hemos intentado demasiadas veces, resolver con lo que tenemos
                if (attempts >= maxAttempts) {
                    console.log('⚠️ Tiempo límite alcanzado, usando duración disponible');
                    videoDuration = videoPlayer.duration || 0;
                    clearInterval(loadingCheckInterval);
                    resolve(videoDuration);
                    return;
                }
                
                // Actualizar progreso de carga
                if (videoPlayer.buffered.length > 0) {
                    const bufferedEnd = videoPlayer.buffered.end(videoPlayer.buffered.length - 1);
                    const currentDuration = videoPlayer.duration || 1;
                    const bufferedPercent = Math.min(100, (bufferedEnd / currentDuration) * 100);
                    loadingProgressBar.style.width = bufferedPercent + '%';
                }
            };
            
            // Verificar cada 100ms
            loadingCheckInterval = setInterval(checkLoading, 100);
            
            // También escuchar eventos del video
            const onLoadedData = () => {
                if (videoPlayer.duration && videoPlayer.duration > 0) {
                    videoDuration = videoPlayer.duration;
                    console.log('✅ Evento loadeddata: duración obtenida', videoDuration);
                    clearInterval(loadingCheckInterval);
                    videoPlayer.removeEventListener('loadeddata', onLoadedData);
                    resolve(videoDuration);
                }
            };
            
            videoPlayer.addEventListener('loadeddata', onLoadedData);
            
            // Timeout de seguridad
            setTimeout(() => {
                if (!isVideoReady) {
                    console.log('⚠️ Timeout de seguridad activado');
                    videoDuration = videoPlayer.duration || 0;
                    clearInterval(loadingCheckInterval);
                    videoPlayer.removeEventListener('loadeddata', onLoadedData);
                    resolve(videoDuration);
                }
            }, 15000); // 15 segundos máximo
        });
    }

    // Inicializar la carga del video
    async function initializeVideo() {
        try {
            showLoading();
            await forceVideoLoad();
            hideLoading();
            setupVideoControls();
        } catch (error) {
            console.error('Error cargando el video:', error);
            hideLoading();
            setupVideoControls(); // Intentar configurar controles de todos modos
        }
    }

    function showLoading() {
        loadingOverlay.style.display = 'flex';
        customControls.classList.remove('ready');
        isVideoReady = false;
    }

    function hideLoading() {
        loadingOverlay.style.display = 'none';
        customControls.classList.add('ready');
        isVideoReady = true;
        updateTimeDisplay();
        console.log('🎉 Video listo para reproducir');
    }

    function setupVideoControls() {
        // Configurar volumen inicial
        videoPlayer.volume = 1;
        videoPlayer.muted = false;
        volumeSlider.value = 1;
        updateVolumeIcon();
    }

    // Format time helper
    function formatTime(seconds) {
        if (!seconds || isNaN(seconds) || seconds < 0) return '0:00';
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    // Update time display
    function updateTimeDisplay() {
        const currentTime = videoPlayer.currentTime || 0;
        const duration = videoDuration || 0;
        timeDisplay.textContent = `${formatTime(currentTime)} / ${formatTime(duration)}`;
    }

    // Update progress bar
    function updateProgressBar() {
        const currentTime = videoPlayer.currentTime || 0;
        const duration = videoDuration || 0;
        if (duration > 0) {
            const progressPercent = (currentTime / duration) * 100;
            progressBar.style.width = progressPercent + '%';
        }
    }

    // Update buffer bar
    function updateBufferBar() {
        if (videoPlayer.buffered.length > 0 && videoDuration > 0) {
            const bufferedEnd = videoPlayer.buffered.end(videoPlayer.buffered.length - 1);
            const bufferedPercent = (bufferedEnd / videoDuration) * 100;
            bufferBar.style.width = bufferedPercent + '%';
        }
    }

    // Play/Pause functionality
    playPauseBtn.addEventListener('click', function() {
        if (!isVideoReady) return;
        
        if (videoPlayer.paused) {
            videoPlayer.play().then(() => {
                playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
            }).catch(e => console.error('Error al reproducir:', e));
        } else {
            videoPlayer.pause();
            playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
        }
    });

    // Rewind 10 seconds
    rewindBtn.addEventListener('click', function() {
        if (!isVideoReady) return;
        videoPlayer.currentTime = Math.max(0, videoPlayer.currentTime - 10);
    });

    // Forward 10 seconds
    forwardBtn.addEventListener('click', function() {
        if (!isVideoReady) return;
        videoPlayer.currentTime = Math.min(videoDuration, videoPlayer.currentTime + 10);
    });

    // Progress bar click
    progressContainer.addEventListener('click', function(e) {
        if (!isVideoReady || videoDuration <= 0) return;
        
        const rect = progressContainer.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const width = rect.width;
        const clickPercent = clickX / width;
        const newTime = clickPercent * videoDuration;
        videoPlayer.currentTime = newTime;
    });

    // Volume control
    volumeSlider.addEventListener('input', function() {
        videoPlayer.volume = volumeSlider.value;
        videoPlayer.muted = false;
        updateVolumeIcon();
    });

    // Mute/Unmute
    muteBtn.addEventListener('click', function() {
        videoPlayer.muted = !videoPlayer.muted;
        updateVolumeIcon();
    });

    function updateVolumeIcon() {
        const icon = muteBtn.querySelector('i');
        if (videoPlayer.muted || videoPlayer.volume === 0) {
            icon.className = 'fas fa-volume-mute';
        } else if (videoPlayer.volume < 0.5) {
            icon.className = 'fas fa-volume-down';
        } else {
            icon.className = 'fas fa-volume-up';
        }
    }

    // Fullscreen
    fullscreenBtn.addEventListener('click', function() {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            videoContainer.requestFullscreen();
        }
    });

    // Video event listeners
    videoPlayer.addEventListener('timeupdate', function() {
        if (isVideoReady) {
            updateProgressBar();
            updateTimeDisplay();
        }
    });

    videoPlayer.addEventListener('progress', function() {
        updateBufferBar();
    });

    videoPlayer.addEventListener('play', function() {
        playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
    });

    videoPlayer.addEventListener('pause', function() {
        playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (!isVideoReady) return;
        
        switch(e.code) {
            case 'Space':
                e.preventDefault();
                playPauseBtn.click();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                rewindBtn.click();
                break;
            case 'ArrowRight':
                e.preventDefault();
                forwardBtn.click();
                break;
            case 'ArrowUp':
                e.preventDefault();
                videoPlayer.volume = Math.min(1, videoPlayer.volume + 0.1);
                volumeSlider.value = videoPlayer.volume;
                updateVolumeIcon();
                break;
            case 'ArrowDown':
                e.preventDefault();
                videoPlayer.volume = Math.max(0, videoPlayer.volume - 0.1);
                volumeSlider.value = videoPlayer.volume;
                updateVolumeIcon();
                break;
            case 'KeyM':
                e.preventDefault();
                muteBtn.click();
                break;
            case 'KeyF':
                e.preventDefault();
                fullscreenBtn.click();
                break;
        }
    });

    // Auto-hide controls
    function showControls() {
        if (isVideoReady) {
            customControls.style.opacity = '1';
            clearTimeout(controlsTimeout);
            controlsTimeout = setTimeout(hideControls, 3000);
        }
    }

    function hideControls() {
        if (!videoPlayer.paused && isVideoReady) {
            customControls.style.opacity = '0';
        }
    }

    videoContainer.addEventListener('mousemove', showControls);
    videoContainer.addEventListener('mouseleave', hideControls);

    // Minimize functionality
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

    // Save video progress
    const links = document.querySelectorAll('.related-video-item');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            sessionStorage.setItem('lastVideoTime', videoPlayer.currentTime);
        });
    });

    // Initialize video when page loads
    initializeVideo();
});
    </script>

    <script src="../../auth/firebase-config.js"></script>
    <script src="../../auth/auth.js"></script>
    <div class="back-to-top">
        <a href="#"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>
