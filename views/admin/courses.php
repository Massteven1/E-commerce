<?php
// Verificar autenticación
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/upload.php';
use Controllers\AuthController;

if (!AuthController::isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

// Obtener cursos
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/Video.php';
use Config\Database;
use Models\Playlist;
use Models\Video;

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);
$videoModel = new Video($db);

// Crear directorios de upload
UploadConfig::createDirectories();

// Manejar acciones
$action = $_GET['sub_action'] ?? '';
$courseId = $_GET['course_id'] ?? '';

// Manejar solicitud AJAX para obtener datos del curso
if ($action === 'get_course' && $courseId) {
    header('Content-Type: application/json');
    $course = $playlistModel->findById($courseId);
    if ($course) {
        echo json_encode($course);
    } else {
        echo json_encode(['error' => 'Curso no encontrado']);
    }
    exit();
}

// Manejar subida de video
if ($action === 'upload_video' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $playlistId = $_POST['playlist_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    error_log("=== SUBIENDO VIDEO ===");
    error_log("Playlist ID: $playlistId, Title: $title");
    error_log("FILES: " . print_r($_FILES, true));
    
    if (!empty($playlistId) && !empty($title) && isset($_FILES['video_file'])) {
        try {
            // Manejar subida del archivo de video
            $videoFile = '';
            if ($_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                error_log("Procesando archivo de video...");
                $videoFile = UploadConfig::handleVideoUpload($_FILES['video_file'], 'videos');
                error_log("Video subido: $videoFile");
            } else {
                throw new Exception("Error al subir el archivo de video. Código de error: " . $_FILES['video_file']['error']);
            }
            
            // Manejar subida de miniatura del video (opcional)
            $thumbnailImage = '';
            if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
                error_log("Procesando miniatura del video...");
                $thumbnailImage = UploadConfig::handleImageUpload($_FILES['thumbnail_image'], 'video_thumbnails');
                error_log("Miniatura subida: $thumbnailImage");
            }
            
            // Asignar valores al modelo de video
            $videoModel->playlist_id = $playlistId;
            $videoModel->title = $title;
            $videoModel->description = $description;
            $videoModel->file_path = $videoFile;
            $videoModel->thumbnail_image = $thumbnailImage;
            
            error_log("Creando video en BD...");
            if ($videoModel->create()) {
                $success_message = "Video agregado exitosamente al curso";
                error_log("SUCCESS: Video creado con ID " . $videoModel->id);
            } else {
                $error_message = "Error al agregar el video a la base de datos";
                error_log("ERROR: No se pudo crear el video en BD");
                // Limpiar archivos si falla la BD
                if ($videoFile && file_exists(__DIR__ . '/../../' . $videoFile)) {
                    unlink(__DIR__ . '/../../' . $videoFile);
                }
                if ($thumbnailImage && file_exists(__DIR__ . '/../../' . $thumbnailImage)) {
                    unlink(__DIR__ . '/../../' . $thumbnailImage);
                }
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
            error_log("EXCEPTION: " . $e->getMessage());
            // Limpiar archivos si hay error
            if (isset($videoFile) && $videoFile && file_exists(__DIR__ . '/../../' . $videoFile)) {
                unlink(__DIR__ . '/../../' . $videoFile);
            }
            if (isset($thumbnailImage) && $thumbnailImage && file_exists(__DIR__ . '/../../' . $thumbnailImage)) {
                unlink(__DIR__ . '/../../' . $thumbnailImage);
            }
        }
    } else {
        $error_message = "El título y el archivo de video son requeridos";
        error_log("ERROR: Campos requeridos faltantes");
    }
}

// Manejar eliminación de video
if ($action === 'delete_video' && isset($_GET['video_id'])) {
    $videoId = $_GET['video_id'];
    try {
        // Obtener video para eliminar archivos
        $video = $videoModel->readOne($videoId);
        if ($video) {
            // Eliminar archivo de video
            if ($video['file_path'] && file_exists(__DIR__ . '/../../' . $video['file_path'])) {
                unlink(__DIR__ . '/../../' . $video['file_path']);
            }
            // Eliminar miniatura
            if ($video['thumbnail_image'] && file_exists(__DIR__ . '/../../' . $video['thumbnail_image'])) {
                unlink(__DIR__ . '/../../' . $video['thumbnail_image']);
            }
            
            if ($videoModel->delete($videoId)) {
                $success_message = "Video eliminado exitosamente";
            } else {
                $error_message = "Error al eliminar el video";
            }
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'upload_video') {
    if ($action === 'create') {
        $title = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $level = $_POST['level'] ?? 'A1';
        $price = floatval($_POST['price'] ?? 0);
        
        if (!empty($title) && !empty($description)) {
            try {
                // Manejar subida de imagen
                $thumbnail = '';
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                    $thumbnail = UploadConfig::handleImageUpload($_FILES['thumbnail'], 'thumbnails');
                }
                
                // Asignar valores al modelo
                $playlistModel->title = $title;
                $playlistModel->description = $description;
                $playlistModel->level = $level;
                $playlistModel->price = $price;
                $playlistModel->thumbnail = $thumbnail;
                
                if ($playlistModel->create()) {
                    $success_message = "Curso creado exitosamente";
                } else {
                    $error_message = "Error al crear el curso en la base de datos";
                }
            } catch (Exception $e) {
                $error_message = "Error: " . $e->getMessage();
            }
        } else {
            $error_message = "El nombre y la descripción son requeridos";
        }
    } elseif ($action === 'edit' && $courseId) {
        $title = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $level = $_POST['level'] ?? 'A1';
        $price = floatval($_POST['price'] ?? 0);
        
        if (!empty($title) && !empty($description)) {
            try {
                // Obtener curso actual
                $currentCourse = $playlistModel->findById($courseId);
                $thumbnail = $currentCourse['thumbnail'] ?? '';
                
                // Manejar nueva imagen si se subió
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                    // Eliminar imagen anterior si existe
                    if ($thumbnail && file_exists(__DIR__ . '/../../' . $thumbnail)) {
                        unlink(__DIR__ . '/../../' . $thumbnail);
                    }
                    $thumbnail = UploadConfig::handleImageUpload($_FILES['thumbnail'], 'thumbnails');
                }
                
                // Asignar valores al modelo
                $playlistModel->id = $courseId;
                $playlistModel->title = $title;
                $playlistModel->description = $description;
                $playlistModel->level = $level;
                $playlistModel->price = $price;
                $playlistModel->thumbnail = $thumbnail;
                
                if ($playlistModel->update()) {
                    $success_message = "Curso actualizado exitosamente";
                } else {
                    $error_message = "Error al actualizar el curso";
                }
            } catch (Exception $e) {
                $error_message = "Error: " . $e->getMessage();
            }
        } else {
            $error_message = "El nombre y la descripción son requeridos";
        }
    }
}

// Manejar eliminación de curso
if ($action === 'delete' && $courseId) {
    try {
        // Obtener curso para eliminar imagen
        $course = $playlistModel->findById($courseId);
        if ($course && $course['thumbnail'] && file_exists(__DIR__ . '/../../' . $course['thumbnail'])) {
            unlink(__DIR__ . '/../../' . $course['thumbnail']);
        }
        
        // Eliminar videos del curso
        $videos = $videoModel->readByPlaylist($courseId);
        foreach ($videos as $video) {
            if ($video['file_path'] && file_exists(__DIR__ . '/../../' . $video['file_path'])) {
                unlink(__DIR__ . '/../../' . $video['file_path']);
            }
            if ($video['thumbnail_image'] && file_exists(__DIR__ . '/../../' . $video['thumbnail_image'])) {
                unlink(__DIR__ . '/../../' . $video['thumbnail_image']);
            }
        }
        $videoModel->deleteByPlaylist($courseId);
        
        $playlistModel->id = $courseId;
        if ($playlistModel->delete()) {
            $success_message = "Curso eliminado exitosamente";
        } else {
            $error_message = "Error al eliminar el curso";
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

$courses = $playlistModel->readAll();
$currentUser = AuthController::getCurrentUser();

// Obtener videos para vista de playlist
$playlistVideos = [];
if ($action === 'view_videos' && $courseId) {
    $playlistVideos = $videoModel->readByPlaylist($courseId);
    $viewPlaylist = $playlistModel->findById($courseId);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - Admin Panel</title>
    <link rel="stylesheet" href="../../public/css/admin/admin-base.css">
    <link rel="stylesheet" href="../../public/css/admin/courses.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .upload-progress {
            display: none;
            margin-top: 10px;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            width: 0%;
            transition: width 0.3s ease;
        }
        .file-info {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            font-size: 0.9em;
        }
        .video-preview {
            margin-top: 10px;
            max-width: 300px;
        }
        .video-preview video {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-graduation-cap"></i> Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../../index.php?page=admin&action=dashboard" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="../../index.php?page=admin&action=users" class="nav-link">
                    <i class="fas fa-users"></i> Usuarios
                </a>
                <a href="../../index.php?page=admin&action=courses" class="nav-link active">
                    <i class="fas fa-book"></i> Cursos
                </a>
                <a href="../../index.php?page=admin&action=orders" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
                <a href="../../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-book"></i> Gestión de Cursos</h1>
                <div class="user-info">
                    <i class="fas fa-user"></i> Bienvenido, <?php echo htmlspecialchars($currentUser['first_name'] ?? 'Admin'); ?>
                </div>
            </header>

            <div class="courses-content">
                <!-- Mensajes -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <!-- Header con acciones -->
                <div class="courses-header">
                    <div class="courses-actions">
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus"></i> Crear Curso
                        </button>
                        <a href="../../index.php?page=admin&action=dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>

                <!-- Vista de videos de playlist -->
                <?php if ($action === 'view_videos' && isset($viewPlaylist)): ?>
                    <div class="admin-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                            <div>
                                <h2><i class="fas fa-video"></i> Videos del Curso: <?php echo htmlspecialchars($viewPlaylist['title']); ?></h2>
                                <p style="color: var(--dark-gray); margin: 0.5rem 0;">Nivel: <?php echo htmlspecialchars($viewPlaylist['level'] ?? 'General'); ?> | Precio: $<?php echo number_format($viewPlaylist['price'], 2); ?></p>
                            </div>
                            <div>
                                <button class="btn btn-primary" onclick="openVideoModal(<?php echo $courseId; ?>)" style="margin-right: 1rem;">
                                    <i class="fas fa-plus"></i> Agregar Video
                                </button>
                                <a href="../../index.php?page=admin&action=courses" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver a Cursos
                                </a>
                            </div>
                        </div>
                        
                        <?php if (!empty($playlistVideos)): ?>
                            <div class="videos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                                <?php foreach ($playlistVideos as $video): ?>
                                    <div class="video-card" style="background: white; border-radius: var(--border-radius-lg); overflow: hidden; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);">
                                        <div class="video-thumbnail" style="height: 180px; background: var(--light-gray); display: flex; align-items: center; justify-content: center; color: var(--dark-gray); position: relative;">
                                            <?php if (!empty($video['thumbnail_image'])): ?>
                                                <img src="../../<?php echo htmlspecialchars($video['thumbnail_image']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <i class="fas fa-play-circle" style="font-size: 3rem;"></i>
                                            <?php endif; ?>
                                            <div style="position: absolute; bottom: 5px; right: 5px; background: rgba(0,0,0,0.7); color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em;">
                                                <i class="fas fa-video"></i> MP4
                                            </div>
                                        </div>
                                        <div style="padding: 1.5rem;">
                                            <h4 style="margin: 0 0 0.5rem; color: var(--text-color);"><?php echo htmlspecialchars($video['title']); ?></h4>
                                            <p style="color: var(--dark-gray); font-size: 0.9rem; margin: 0 0 1rem;"><?php echo htmlspecialchars($video['description'] ?: 'Sin descripción'); ?></p>
                                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                <button class="btn btn-sm btn-info" onclick="playVideo('<?php echo htmlspecialchars($video['file_path']); ?>', '<?php echo htmlspecialchars($video['title']); ?>')">
                                                    <i class="fas fa-play"></i> Reproducir
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editVideo(<?php echo $video['id']; ?>)">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteVideo(<?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['title']); ?>')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 3rem; color: var(--dark-gray);">
                                <i class="fas fa-video" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <h3>No hay videos en este curso</h3>
                                <p>Comienza agregando el primer video a este curso.</p>
                                <button class="btn btn-primary" onclick="openVideoModal(<?php echo $courseId; ?>)">
                                    <i class="fas fa-plus"></i> Agregar Video
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Grid de cursos -->
                    <div class="courses-grid">
                        <?php if (!empty($courses)): ?>
                            <?php foreach ($courses as $course): ?>
                                <div class="course-card">
                                    <div class="course-image">
                                        <?php if (!empty($course['thumbnail'])): ?>
                                            <img src="../../<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="course-overlay">
                                            <div class="course-overlay-actions">
                                                <button class="overlay-btn edit" onclick="openEditModal(<?php echo $course['id']; ?>)" title="Editar Curso">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="overlay-btn view" onclick="viewVideos(<?php echo $course['id']; ?>)" title="Ver Videos">
                                                    <i class="fas fa-video"></i>
                                                </button>
                                                <button class="overlay-btn delete" onclick="deleteCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['title']); ?>')" title="Eliminar Curso">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="course-info">
                                        <div class="course-header">
                                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                            <span class="course-level"><?php echo htmlspecialchars($course['level'] ?? 'General'); ?></span>
                                        </div>
                                        <p class="course-description"><?php echo htmlspecialchars($course['description'] ?: 'Sin descripción'); ?></p>
                                        <div class="course-meta">
                                            <div class="course-price">$<?php echo number_format($course['price'], 2); ?></div>
                                            <div class="course-stats">
                                                <span class="stat-item">
                                                    <i class="fas fa-video"></i>
                                                    <?php 
                                                    $videoCount = count($videoModel->readByPlaylist($course['id']));
                                                    echo $videoCount;
                                                    ?> videos
                                                </span>
                                                <span class="stat-item">
                                                    <i class="fas fa-calendar"></i>
                                                    <?php echo date('d/m/Y', strtotime($course['created_at'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="course-actions">
                                            <button class="btn btn-sm btn-warning" onclick="openEditModal(<?php echo $course['id']; ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="viewVideos(<?php echo $course['id']; ?>)">
                                                <i class="fas fa-video"></i> Videos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-courses">
                                <i class="fas fa-book"></i>
                                <h3>No hay cursos disponibles</h3>
                                <p>Comienza creando tu primer curso</p>
                                <button class="btn btn-primary" onclick="openCreateModal()">
                                    <i class="fas fa-plus"></i> Crear Primer Curso
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal para crear/editar curso -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Crear Curso</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="courseForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="courseId" name="course_id">
                <input type="hidden" id="subAction" name="sub_action" value="create">
                
                <div class="form-group">
                    <label for="courseName" class="form-label">Nombre del Curso *</label>
                    <input type="text" id="courseName" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="courseDescription" class="form-label">Descripción *</label>
                    <textarea id="courseDescription" name="description" class="form-textarea" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="courseLevel" class="form-label">Nivel</label>
                    <select id="courseLevel" name="level" class="form-select">
                        <option value="A1">A1 - Principiante</option>
                        <option value="A2">A2 - Básico</option>
                        <option value="B1">B1 - Intermedio</option>
                        <option value="B2">B2 - Intermedio Alto</option>
                        <option value="C1">C1 - Avanzado</option>
                        <option value="C2">C2 - Experto</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="coursePrice" class="form-label">Precio (USD) *</label>
                    <input type="number" id="coursePrice" name="price" class="form-input" min="0" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="courseThumbnail" class="form-label">Miniatura del Curso</label>
                    <input type="file" id="courseThumbnail" name="thumbnail" class="form-input" accept="image/*">
                    <small class="form-help">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                    <div id="thumbnailPreview" class="thumbnail-preview"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <span id="submitText">Crear Curso</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para agregar video -->
    <div id="videoModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Video al Curso</h3>
                <button class="modal-close" onclick="closeVideoModal()">&times;</button>
            </div>
            <form id="videoForm" method="POST" enctype="multipart/form-data" action="../../index.php?page=admin&action=courses&sub_action=upload_video">
                <input type="hidden" id="videoPlaylistId" name="playlist_id">
                
                <div class="form-group">
                    <label for="videoTitle" class="form-label">Título del Video *</label>
                    <input type="text" id="videoTitle" name="title" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="videoDescription" class="form-label">Descripción</label>
                    <textarea id="videoDescription" name="description" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="videoFile" class="form-label">Archivo de Video * (MP4, AVI, MOV, WMV)</label>
                    <input type="file" id="videoFile" name="video_file" class="form-input" accept="video/mp4,video/avi,video/quicktime,video/x-msvideo" required>
                    <small class="form-help">Tamaño máximo: 100MB. Formatos soportados: MP4, AVI, MOV, WMV</small>
                    <div id="videoFileInfo" class="file-info" style="display: none;"></div>
                    <div id="videoPreview" class="video-preview" style="display: none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="videoThumbnail" class="form-label">Miniatura del Video (Opcional)</label>
                    <input type="file" id="videoThumbnail" name="thumbnail_image" class="form-input" accept="image/*">
                    <small class="form-help">Si no se proporciona, se usará una miniatura por defecto</small>
                    <div id="videoThumbnailPreview" class="thumbnail-preview"></div>
                </div>
                
                <div class="upload-progress" id="uploadProgress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div id="progressText">Subiendo video...</div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeVideoModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="submitVideoBtn">
                        <i class="fas fa-upload"></i> Subir Video
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para reproducir video -->
    <div id="playVideoModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title" id="playVideoTitle">Reproducir Video</h3>
                <button class="modal-close" onclick="closePlayVideoModal()">&times;</button>
            </div>
            <div class="modal-body">
                <video id="videoPlayer" controls style="width: 100%; height: auto;">
                    Tu navegador no soporta la reproducción de video.
                </video>
            </div>
        </div>
    </div>

    <script>
        // Funciones del modal de curso
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Crear Curso';
            document.getElementById('subAction').value = 'create';
            document.getElementById('submitText').textContent = 'Crear Curso';
            document.getElementById('courseForm').action = '../../index.php?page=admin&action=courses&sub_action=create';
            document.getElementById('courseForm').reset();
            document.getElementById('thumbnailPreview').innerHTML = '';
            document.getElementById('courseModal').classList.add('active');
        }

        function openEditModal(courseId) {
            // Obtener datos del curso via AJAX
            fetch(`../../index.php?page=admin&action=courses&sub_action=get_course&course_id=${courseId}`)
                .then(response => response.json())
                .then(course => {
                    if (course.error) {
                        alert('Error al cargar los datos del curso: ' + course.error);
                        return;
                    }
                    
                    document.getElementById('modalTitle').textContent = 'Editar Curso';
                    document.getElementById('subAction').value = 'edit';
                    document.getElementById('courseId').value = courseId;
                    document.getElementById('courseName').value = course.title || '';
                    document.getElementById('courseDescription').value = course.description || '';
                    document.getElementById('courseLevel').value = course.level || 'A1';
                    document.getElementById('coursePrice').value = course.price || 0;
                    document.getElementById('submitText').textContent = 'Actualizar Curso';
                    document.getElementById('courseForm').action = `../../index.php?page=admin&action=courses&sub_action=edit&course_id=${courseId}`;
                    
                    // Mostrar miniatura actual si existe
                    const preview = document.getElementById('thumbnailPreview');
                    if (course.thumbnail) {
                        preview.innerHTML = `<img src="../../${course.thumbnail}" alt="Miniatura actual" style="max-width: 200px; max-height: 150px; border-radius: 8px;">`;
                    } else {
                        preview.innerHTML = '';
                    }
                    
                    document.getElementById('courseModal').classList.add('active');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del curso');
                });
        }

        function closeModal() {
            document.getElementById('courseModal').classList.remove('active');
        }

        // Funciones del modal de video
        function openVideoModal(courseId) {
            document.getElementById('videoPlaylistId').value = courseId;
            document.getElementById('videoForm').reset();
            document.getElementById('videoThumbnailPreview').innerHTML = '';
            document.getElementById('videoFileInfo').style.display = 'none';
            document.getElementById('videoPreview').style.display = 'none';
            document.getElementById('uploadProgress').style.display = 'none';
            document.getElementById('videoModal').classList.add('active');
        }

        function closeVideoModal() {
            document.getElementById('videoModal').classList.remove('active');
        }

        // Función para reproducir video
        function playVideo(filePath, title) {
            document.getElementById('playVideoTitle').textContent = title;
            document.getElementById('videoPlayer').src = '../../' + filePath;
            document.getElementById('playVideoModal').classList.add('active');
        }

        function closePlayVideoModal() {
            document.getElementById('playVideoModal').classList.remove('active');
            document.getElementById('videoPlayer').pause();
            document.getElementById('videoPlayer').src = '';
        }

        // Preview de imagen para curso
        document.getElementById('courseThumbnail').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('thumbnailPreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px;">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        // Preview de archivo de video
        document.getElementById('videoFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const fileInfo = document.getElementById('videoFileInfo');
            const videoPreview = document.getElementById('videoPreview');
            
            if (file) {
                // Mostrar información del archivo
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                fileInfo.innerHTML = `
                    <strong>Archivo seleccionado:</strong><br>
                    Nombre: ${file.name}<br>
                    Tamaño: ${fileSize} MB<br>
                    Tipo: ${file.type}
                `;
                fileInfo.style.display = 'block';
                
                // Crear preview del video
                const videoURL = URL.createObjectURL(file);
                videoPreview.innerHTML = `<video controls><source src="${videoURL}" type="${file.type}">Tu navegador no soporta la reproducción de video.</video>`;
                videoPreview.style.display = 'block';
            } else {
                fileInfo.style.display = 'none';
                videoPreview.style.display = 'none';
            }
        });

        // Preview de imagen para video
        document.getElementById('videoThumbnail').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('videoThumbnailPreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px;">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        // Manejar envío del formulario de video con progreso
        document.getElementById('videoForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('videoFile');
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size;
                const maxSize = 100 * 1024 * 1024; // 100MB
                
                if (fileSize > maxSize) {
                    e.preventDefault();
                    alert('El archivo es demasiado grande. El tamaño máximo permitido es 100MB.');
                    return;
                }
                
                // Mostrar barra de progreso
                document.getElementById('uploadProgress').style.display = 'block';
                document.getElementById('submitVideoBtn').disabled = true;
                document.getElementById('submitVideoBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
            }
        });

        // Funciones de gestión
        function viewVideos(courseId) {
            window.location.href = `../../index.php?page=admin&action=courses&sub_action=view_videos&course_id=${courseId}`;
        }

        function deleteCourse(courseId, courseName) {
            if (confirm(`¿Estás seguro de que deseas eliminar el curso "${courseName}"? Esta acción eliminará también todos los videos del curso y no se puede deshacer.`)) {
                window.location.href = `../../index.php?page=admin&action=courses&sub_action=delete&course_id=${courseId}`;
            }
        }

        function editVideo(videoId) {
            alert('Función de editar video - ID: ' + videoId + '\n(Esta funcionalidad se implementará próximamente)');
        }

        function deleteVideo(videoId, videoTitle) {
            if (confirm(`¿Estás seguro de eliminar el video "${videoTitle}"? Esta acción no se puede deshacer.`)) {
                window.location.href = `../../index.php?page=admin&action=courses&sub_action=delete_video&video_id=${videoId}`;
            }
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const courseModal = document.getElementById('courseModal');
            const videoModal = document.getElementById('videoModal');
            const playVideoModal = document.getElementById('playVideoModal');
            
            if (event.target === courseModal) {
                closeModal();
            }
            if (event.target === videoModal) {
                closeVideoModal();
            }
            if (event.target === playVideoModal) {
                closePlayVideoModal();
            }
        }
    </script>
</body>
</html>
