<?php
// Verificar autenticación y permisos de administrador
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';

use Controllers\AuthController;
use Config\Database;
use Models\Playlist;

if (!AuthController::isAuthenticated() || !AuthController::isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

// Obtener playlists de forma segura
$playlists = [];
try {
    $database = new Database();
    $db = $database->getConnection();
    $playlistModel = new Playlist($db);
    $playlistsResult = $playlistModel->readAll();
    
    // Asegurar que sea un array
    if (is_array($playlistsResult)) {
        $playlists = $playlistsResult;
    } else {
        $playlists = [];
    }
} catch (Exception $e) {
    error_log("Error obteniendo playlists: " . $e->getMessage());
    $playlists = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos - Panel de Administración</title>
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

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .admin-header h1 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
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

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        .filters-section {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-box {
            display: flex;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px 0 0 8px;
            font-size: 1rem;
        }

        .search-btn {
            padding: 0.75rem 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .course-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .course-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-placeholder {
            width: 100%;
            height: 100%;
            background: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 3rem;
        }

        .course-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .course-card:hover .course-overlay {
            opacity: 1;
        }

        .course-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .action-btn.edit { background: var(--primary-color); }
        .action-btn.view { background: var(--info-color); }
        .action-btn.delete { background: var(--danger-color); }

        .course-content {
            padding: 1.5rem;
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .course-title {
            margin: 0;
            font-size: 1.25rem;
            color: var(--text-color);
            flex: 1;
        }

        .course-level {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-left: 1rem;
        }

        .course-description {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .course-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .course-price {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .stat-item {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .stat-item i {
            margin-right: 0.25rem;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        @media (max-width: 768px) {
            .admin-content {
                margin-left: 0;
                padding: 1rem;
            }

            .filters-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: none;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin-body">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Gestión de Cursos</h1>
            <div class="header-actions">
                <a href="../../controllers/PlaylistController.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Crear Curso
                </a>
                <a href="../../controllers/VideoController.php?action=upload" class="btn btn-secondary">
                    <i class="fas fa-video"></i> Subir Video
                </a>
            </div>
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

        <!-- Filtros y búsqueda -->
        <div class="filters-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar cursos..." class="search-input">
                <button class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="filter-group">
                <select id="levelFilter" class="filter-select">
                    <option value="">Todos los niveles</option>
                    <option value="A1">A1 - Principiante</option>
                    <option value="A2">A2 - Básico</option>
                    <option value="B1">B1 - Intermedio</option>
                    <option value="B2">B2 - Intermedio Alto</option>
                    <option value="C1">C1 - Avanzado</option>
                    <option value="C2">C2 - Experto</option>
                </select>
            </div>
        </div>

        <!-- Grid de cursos -->
        <div class="courses-grid" id="coursesGrid">
            <?php if (empty($playlists)): ?>
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <h3>No hay cursos disponibles</h3>
                    <p>Comienza creando tu primer curso</p>
                    <a href="../../controllers/PlaylistController.php?action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primer Curso
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($playlists as $playlist): ?>
                    <div class="course-card" data-level="<?php echo htmlspecialchars($playlist['level'] ?? ''); ?>" data-name="<?php echo htmlspecialchars(strtolower($playlist['name'] ?? '')); ?>">
                        <div class="course-image">
                            <?php if (!empty($playlist['cover_image'])): ?>
                                <img src="../../<?php echo htmlspecialchars($playlist['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($playlist['name'] ?? ''); ?>">
                            <?php else: ?>
                                <div class="course-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            <div class="course-overlay">
                                <div class="course-actions">
                                    <button class="action-btn edit" onclick="editCourse(<?php echo $playlist['id']; ?>)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn view" onclick="viewCourse(<?php echo $playlist['id']; ?>)" title="Ver Videos">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="deleteCourse(<?php echo $playlist['id']; ?>)" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="course-content">
                            <div class="course-header">
                                <h3 class="course-title"><?php echo htmlspecialchars($playlist['name'] ?? ''); ?></h3>
                                <span class="course-level"><?php echo htmlspecialchars($playlist['level'] ?? ''); ?></span>
                            </div>
                            
                            <p class="course-description">
                                <?php 
                                $description = $playlist['description'] ?? '';
                                echo htmlspecialchars(substr($description, 0, 100));
                                if (strlen($description) > 100) echo '...';
                                ?>
                            </p>
                            
                            <div class="course-footer">
                                <div class="course-price">
                                    $<?php echo number_format($playlist['price'] ?? 0, 2); ?>
                                </div>
                                <div class="course-stats">
                                    <span class="stat-item">
                                        <i class="fas fa-video"></i>
                                        0 videos
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Funciones de gestión de cursos
        function editCourse(id) {
            window.location.href = `../../controllers/PlaylistController.php?action=edit&id=${id}`;
        }

        function viewCourse(id) {
            window.location.href = `../../controllers/VideoController.php?action=view_playlist&id=${id}`;
        }

        function deleteCourse(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este curso? Esta acción no se puede deshacer.')) {
                window.location.href = `../../controllers/PlaylistController.php?action=delete&id=${id}`;
            }
        }

        // Filtros y búsqueda
        function filterCourses() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const levelFilter = document.getElementById('levelFilter').value;
            const courseCards = document.querySelectorAll('.course-card');

            courseCards.forEach(card => {
                const courseName = card.dataset.name || '';
                const courseLevel = card.dataset.level || '';
                
                const matchesSearch = !searchTerm || courseName.includes(searchTerm);
                const matchesLevel = !levelFilter || courseLevel === levelFilter;
                
                if (matchesSearch && matchesLevel) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Event listeners
        document.getElementById('searchInput').addEventListener('input', filterCourses);
        document.getElementById('levelFilter').addEventListener('change', filterCourses);
    </script>
</body>
</html>
