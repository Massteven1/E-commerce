<?php
// Verificar autenticación y permisos de administrador
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/AuthController.php';
use Controllers\AuthController;

if (!AuthController::isAuthenticated() || !AuthController::isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

// Obtener playlists disponibles
$playlists = $playlists ?? [];
$csrfToken = $csrfToken ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Video - Panel de Administración</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Subir Video</h1>
            <div class="header-actions">
                <a href="index.php?controller=playlist&action=index" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Cursos
                </a>
            </div>
        </div>

        <!-- Mostrar mensajes flash -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_type']; ?>">
                <?php 
                echo $_SESSION['flash_message'];
                unset($_SESSION['flash_message'], $_SESSION['flash_type']);
                ?>
            </div>
        <?php endif; ?>

        <div class="upload-container">
            <div class="upload-card">
                <div class="upload-header">
                    <i class="fas fa-video"></i>
                    <h2>Subir Nuevo Video</h2>
                    <p>Selecciona un archivo de video y completa la información</p>
                </div>

                <form action="index.php?controller=video&action=upload" method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <!-- Selección de archivo de video -->
                    <div class="form-group">
                        <label for="video" class="form-label required">
                            <i class="fas fa-video"></i> Archivo de Video
                        </label>
                        <div class="file-upload-area" id="videoUploadArea">
                            <input type="file" id="video" name="video" accept=".mp4,.avi,.mov,.wmv" required class="file-input">
                            <div class="file-upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Arrastra tu video aquí o <span class="upload-link">haz clic para seleccionar</span></p>
                                <small>Formatos soportados: MP4, AVI, MOV, WMV (Máximo 100MB)</small>
                            </div>
                        </div>
                        <div class="file-preview" id="videoPreview" style="display: none;">
                            <div class="file-info">
                                <i class="fas fa-video"></i>
                                <span class="file-name"></span>
                                <span class="file-size"></span>
                                <button type="button" class="remove-file" onclick="removeFile('video')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Título del video -->
                    <div class="form-group">
                        <label for="title" class="form-label required">
                            <i class="fas fa-heading"></i> Título del Video
                        </label>
                        <input type="text" id="title" name="title" class="form-input" required 
                               placeholder="Ingresa el título del video">
                    </div>

                    <!-- Descripción -->
                    <div class="form-group">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left"></i> Descripción
                        </label>
                        <textarea id="description" name="description" class="form-textarea" rows="4" 
                                  placeholder="Describe el contenido del video (opcional)"></textarea>
                    </div>

                    <!-- Selección de playlist -->
                    <div class="form-group">
                        <label for="playlist_id" class="form-label required">
                            <i class="fas fa-list"></i> Curso
                        </label>
                        <select id="playlist_id" name="playlist_id" class="form-select" required>
                            <option value="">Selecciona un curso</option>
                            <?php foreach ($playlists as $playlist): ?>
                                <option value="<?php echo $playlist['id']; ?>">
                                    <?php echo htmlspecialchars($playlist['name']); ?> 
                                    (<?php echo htmlspecialchars($playlist['level']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Miniatura (opcional) -->
                    <div class="form-group">
                        <label for="thumbnail_image" class="form-label">
                            <i class="fas fa-image"></i> Miniatura (Opcional)
                        </label>
                        <div class="file-upload-area" id="thumbnailUploadArea">
                            <input type="file" id="thumbnail_image" name="thumbnail_image" accept=".jpg,.jpeg,.png" class="file-input">
                            <div class="file-upload-content">
                                <i class="fas fa-image"></i>
                                <p>Arrastra una imagen aquí o <span class="upload-link">haz clic para seleccionar</span></p>
                                <small>Formatos: JPG, JPEG, PNG (Máximo 5MB)</small>
                            </div>
                        </div>
                        <div class="file-preview" id="thumbnailPreview" style="display: none;">
                            <div class="file-info">
                                <i class="fas fa-image"></i>
                                <span class="file-name"></span>
                                <span class="file-size"></span>
                                <button type="button" class="remove-file" onclick="removeFile('thumbnail')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="image-preview">
                                <img src="/placeholder.svg" alt="Vista previa" class="preview-image">
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-upload"></i> Subir Video
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Manejo de archivos drag & drop
        function setupFileUpload(inputId, areaId, previewId) {
            const input = document.getElementById(inputId);
            const area = document.getElementById(areaId);
            const preview = document.getElementById(previewId);

            // Drag & Drop events
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('drag-over');
            });

            area.addEventListener('dragleave', () => {
                area.classList.remove('drag-over');
            });

            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    handleFileSelect(input, preview);
                }
            });

            // Click to select
            area.addEventListener('click', () => {
                input.click();
            });

            // File selection
            input.addEventListener('change', () => {
                handleFileSelect(input, preview);
            });
        }

        function handleFileSelect(input, preview) {
            const file = input.files[0];
            if (!file) return;

            const fileName = file.name;
            const fileSize = formatFileSize(file.size);
            
            preview.querySelector('.file-name').textContent = fileName;
            preview.querySelector('.file-size').textContent = fileSize;
            
            // Show preview for images
            if (file.type.startsWith('image/') && preview.querySelector('.image-preview')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.querySelector('.preview-image').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
            
            preview.style.display = 'block';
            input.closest('.file-upload-area').style.display = 'none';
        }

        function removeFile(type) {
            const input = document.getElementById(type === 'video' ? 'video' : 'thumbnail_image');
            const area = document.getElementById(type === 'video' ? 'videoUploadArea' : 'thumbnailUploadArea');
            const preview = document.getElementById(type === 'video' ? 'videoPreview' : 'thumbnailPreview');
            
            input.value = '';
            preview.style.display = 'none';
            area.style.display = 'block';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Initialize file uploads
        setupFileUpload('video', 'videoUploadArea', 'videoPreview');
        setupFileUpload('thumbnail_image', 'thumbnailUploadArea', 'thumbnailPreview');

        // Form validation
        document.querySelector('.upload-form').addEventListener('submit', function(e) {
            const video = document.getElementById('video').files[0];
            const title = document.getElementById('title').value.trim();
            const playlist = document.getElementById('playlist_id').value;

            if (!video) {
                e.preventDefault();
                alert('Por favor selecciona un archivo de video');
                return;
            }

            if (!title) {
                e.preventDefault();
                alert('Por favor ingresa un título para el video');
                return;
            }

            if (!playlist) {
                e.preventDefault();
                alert('Por favor selecciona un curso');
                return;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
            submitBtn.disabled = true;
        });
    </script>

    <style>
        .upload-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .upload-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .upload-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .upload-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .upload-header h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
        }

        .upload-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-label.required::after {
            content: ' *';
            color: var(--danger-color);
        }

        .form-label i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .file-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-area:hover,
        .file-upload-area.drag-over {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
        }

        .file-upload-content i {
            font-size: 2rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .upload-link {
            color: var(--primary-color);
            font-weight: 600;
        }

        .file-input {
            display: none;
        }

        .file-preview {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .file-info i {
            color: var(--primary-color);
        }

        .file-name {
            flex: 1;
            font-weight: 600;
        }

        .file-size {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .remove-file {
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-preview {
            margin-top: 1rem;
        }

        .preview-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            object-fit: cover;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .alert-error {
            background: var(--danger-light);
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        @media (max-width: 768px) {
            .upload-form {
                padding: 1rem;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
