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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso - Panel de Administración</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-light: #dbeafe;
            --secondary-color: #6b7280;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --white: #ffffff;
            --light-bg: #f8fafc;
            --text-color: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .admin-body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 0;
        }

        .admin-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
        }

        .form-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 2rem;
            max-width: 800px;
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-header h1 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 0 0 0.5rem 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .form-label.required::after {
            content: ' *';
            color: var(--danger-color);
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-help {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .current-image {
            margin-top: 0.5rem;
        }

        .current-image img {
            max-width: 200px;
            height: auto;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        @media (max-width: 768px) {
            .admin-content {
                margin-left: 0;
                padding: 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
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
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['flash_type']; ?>">
                    <?php 
                    echo htmlspecialchars($_SESSION['flash_message']);
                    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
                    ?>
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
                
                <div class="form-row">
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
                    <input type="file" id="cover_image" name="cover_image" class="form-input" accept=".jpg,.jpeg,.png">
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
