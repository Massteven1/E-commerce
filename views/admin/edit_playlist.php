<?php
// Verificar autenticación y permisos de administrador
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../helpers/SecurityHelper.php';

use Controllers\AuthController;
use Helpers\SecurityHelper;

if (!AuthController::isAuthenticated() || !AuthController::isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

// Verificar que tenemos los datos del playlist
if (!isset($playlist) || !$playlist) {
    header('Location: courses.php');
    exit();
}

// Generar token CSRF si no existe
if (!isset($csrfToken)) {
    $csrfToken = SecurityHelper::generateCSRFToken();
}

// Obtener mensaje flash
$flash_message = AuthController::getFlashMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso - Panel de Administración</title>
    <link rel="stylesheet" href="../../public/css/admin/admin-base.css">
    <link rel="stylesheet" href="../../public/css/admin/sidebar.css">
    <link rel="stylesheet" href="../../public/css/admin/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="form-container">
            <div class="form-header">
                <h1>Editar Curso</h1>
                <p>Modifica la información del curso</p>
            </div>

            <!-- Mostrar mensajes flash -->
            <?php if ($flash_message): ?>
                <div class="alert alert-<?php echo $flash_message['type']; ?>">
                    <?php echo htmlspecialchars($flash_message['message']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="../../controllers/PlaylistController.php?action=update" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($playlist['id']); ?>">
                
                <div class="form-group">
                    <label for="name" class="form-label required">Nombre del Curso</label>
                    <input type="text" id="name" name="name" class="form-input" 
                           value="<?php echo htmlspecialchars($playlist['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label required">Descripción</label>
                    <textarea id="description" name="description" class="form-textarea" rows="4" required><?php echo htmlspecialchars($playlist['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-grid cols-2">
                    <div class="form-group">
                        <label for="level" class="form-label required">Nivel</label>
                        <select id="level" name="level" class="form-select" required>
                            <option value="A1" <?php echo ($playlist['level'] ?? '') === 'A1' ? 'selected' : ''; ?>>A1 - Principiante</option>
                            <option value="A2" <?php echo ($playlist['level'] ?? '') === 'A2' ? 'selected' : ''; ?>>A2 - Básico</option>
                            <option value="B1" <?php echo ($playlist['level'] ?? '') === 'B1' ? 'selected' : ''; ?>>B1 - Intermedio</option>
                            <option value="B2" <?php echo ($playlist['level'] ?? '') === 'B2' ? 'selected' : ''; ?>>B2 - Intermedio Alto</option>
                            <option value="C1" <?php echo ($playlist['level'] ?? '') === 'C1' ? 'selected' : ''; ?>>C1 - Avanzado</option>
                            <option value="C2" <?php echo ($playlist['level'] ?? '') === 'C2' ? 'selected' : ''; ?>>C2 - Experto</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price" class="form-label required">Precio (USD)</label>
                        <input type="number" id="price" name="price" class="form-input" min="0" step="0.01" 
                               value="<?php echo htmlspecialchars($playlist['price'] ?? '0'); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="cover_image" class="form-label">Imagen del Curso</label>
                    <div class="form-file-input">
                        <input type="file" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png">
                        <label for="cover_image" class="form-file-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Seleccionar imagen</span>
                        </label>
                    </div>
                    <small class="form-help">Formatos soportados: JPG, JPEG, PNG (Máximo 5MB). Deja vacío para mantener la imagen actual.</small>
                    
                    <?php if (!empty($playlist['cover_image'])): ?>
                        <div class="current-image">
                            <p><strong>Imagen actual:</strong></p>
                            <img src="../../<?php echo htmlspecialchars($playlist['cover_image']); ?>" 
                                 alt="Imagen actual del curso">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <a href="courses.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Curso
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
