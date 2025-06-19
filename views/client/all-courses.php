<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir dependencias necesarias
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';
require_once __DIR__ . '/../../models/UserCourse.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

use Config\Database;
use Models\Playlist;
use Models\UserCourse;
use Controllers\AuthController;

// Inicializar conexión a la base de datos
$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);
$userCourseModel = new UserCourse($db);

// Obtener todos los cursos
$playlists = [];
try {
    $playlistsResult = $playlistModel->readAll();
    $playlists = is_array($playlistsResult) ? $playlistsResult : [];
} catch (Exception $e) {
    error_log("Error obteniendo playlists: " . $e->getMessage());
    $playlists = [];
}

// Obtener cursos del usuario si está autenticado
$userCourses = [];
$userCourseIds = [];
if (AuthController::isAuthenticated()) {
    try {
        $currentUser = AuthController::getCurrentUser();
        $userCoursesResult = $userCourseModel->readByUserId($currentUser['id']);
        $userCourses = is_array($userCoursesResult) ? $userCoursesResult : [];
        $userCourseIds = array_column($userCourses, 'playlist_id');
    } catch (Exception $e) {
        error_log("Error obteniendo cursos del usuario: " . $e->getMessage());
        $userCourseIds = [];
    }
}

// Filtros
$levelFilter = $_GET['level'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Aplicar filtros
if ($levelFilter || $searchQuery) {
    $playlists = array_filter($playlists, function($playlist) use ($levelFilter, $searchQuery) {
        $matchesLevel = !$levelFilter || ($playlist['level'] ?? '') === $levelFilter;
        $matchesSearch = !$searchQuery || 
            stripos($playlist['name'] ?? '', $searchQuery) !== false || 
            stripos($playlist['description'] ?? '', $searchQuery) !== false;
        return $matchesLevel && $matchesSearch;
    });
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos los Cursos - El Profesor Hernán</title>
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

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .main-content {
            padding: 2rem 0;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .filters-section {
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        .filters-form {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            display: flex;
            flex: 1;
            min-width: 300px;
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

        .filter-group {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .courses-results {
            margin-bottom: 3rem;
        }

        .results-info {
            margin-bottom: 1.5rem;
            color: var(--text-muted);
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

        .course-level {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .owned-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--success-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .course-content {
            padding: 1.5rem;
        }

        .course-title a {
            color: var(--text-color);
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: block;
        }

        .course-title a:hover {
            color: var(--primary-color);
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

        .price {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .price.free {
            color: var(--success-color);
        }

        .course-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .notification {
            position: fixed;
            top: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .notification.success {
            background: var(--success-color);
        }

        .notification.error {
            background: var(--danger-color);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .filters-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: auto;
            }

            .filter-group {
                justify-content: center;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include_once  '../inc/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Header de la página -->
            <div class="page-header">
                <h1>Todos los Cursos</h1>
                <p>Descubre nuestra colección completa de cursos de inglés</p>
            </div>

            <!-- Filtros y búsqueda -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="search-box">
                        <input type="text" 
                               name="search" 
                               placeholder="Buscar cursos..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>"
                               class="search-input">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <div class="filter-group">
                        <select name="level" class="filter-select">
                            <option value="">Todos los niveles</option>
                            <option value="A1" <?php echo $levelFilter === 'A1' ? 'selected' : ''; ?>>A1 - Principiante</option>
                            <option value="A2" <?php echo $levelFilter === 'A2' ? 'selected' : ''; ?>>A2 - Básico</option>
                            <option value="B1" <?php echo $levelFilter === 'B1' ? 'selected' : ''; ?>>B1 - Intermedio</option>
                            <option value="B2" <?php echo $levelFilter === 'B2' ? 'selected' : ''; ?>>B2 - Intermedio Alto</option>
                            <option value="C1" <?php echo $levelFilter === 'C1' ? 'selected' : ''; ?>>C1 - Avanzado</option>
                            <option value="C2" <?php echo $levelFilter === 'C2' ? 'selected' : ''; ?>>C2 - Experto</option>
                        </select>
                        <button type="submit" class="filter-btn">Filtrar</button>
                    </div>
                </form>
            </div>

            <!-- Resultados -->
            <div class="courses-results">
                <div class="results-info">
                    <span><?php echo count($playlists); ?> curso(s) encontrado(s)</span>
                </div>

                <!-- Grid de cursos -->
                <div class="courses-grid">
                    <?php if (empty($playlists)): ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No se encontraron cursos</h3>
                            <p>Intenta ajustar tus filtros de búsqueda</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($playlists as $playlist): ?>
                            <?php 
                            $isOwned = in_array($playlist['id'], $userCourseIds);
                            $imageUrl = !empty($playlist['cover_image']) ? 
                                '../../' . $playlist['cover_image'] : 
                                '/placeholder.svg?height=200&width=300';
                            ?>
                            <div class="course-card">
                                <div class="course-image">
                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                         alt="<?php echo htmlspecialchars($playlist['name'] ?? ''); ?>">
                                    <div class="course-level"><?php echo htmlspecialchars($playlist['level'] ?? ''); ?></div>
                                    <?php if ($isOwned): ?>
                                        <div class="owned-badge">
                                            <i class="fas fa-check"></i> Adquirido
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="course-content">
                                    <h3 class="course-title">
                                        <a href="course-detail.php?id=<?php echo $playlist['id']; ?>">
                                            <?php echo htmlspecialchars($playlist['name'] ?? ''); ?>
                                        </a>
                                    </h3>
                                    
                                    <p class="course-description">
                                        <?php 
                                        $description = $playlist['description'] ?? '';
                                        echo htmlspecialchars(substr($description, 0, 100));
                                        if (strlen($description) > 100) echo '...';
                                        ?>
                                    </p>
                                    
                                    <div class="course-footer">
                                        <div class="course-price">
                                            <?php if (($playlist['price'] ?? 0) > 0): ?>
                                                <span class="price">$<?php echo number_format($playlist['price'], 2); ?></span>
                                            <?php else: ?>
                                                <span class="price free">Gratis</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="course-actions">
                                            <?php if ($isOwned): ?>
                                                <a href="course-detail.php?id=<?php echo $playlist['id']; ?>" 
                                                   class="btn btn-primary">
                                                    <i class="fas fa-play"></i> Continuar
                                                </a>
                                            <?php else: ?>
                                                <a href="course-detail.php?id=<?php echo $playlist['id']; ?>" 
                                                   class="btn btn-outline">Ver Detalles</a>
                                                <?php if (AuthController::isAuthenticated()): ?>
                                                    <button onclick="addToCart(<?php echo $playlist['id']; ?>)" 
                                                            class="btn btn-primary">
                                                        <i class="fas fa-cart-plus"></i> Agregar
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>

    <script>
    function addToCart(courseId) {
        // Mostrar indicador de carga
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Agregando...';
        button.disabled = true;

        // Crear FormData para enviar la solicitud
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('id', courseId);

        fetch('../../controllers/CartController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(data => {
            showNotification('Curso agregado al carrito exitosamente', 'success');
            updateCartCount();
            
            // Restaurar botón
            button.innerHTML = originalText;
            button.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al agregar al carrito. Inténtalo de nuevo.', 'error');
            
            // Restaurar botón
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    function showNotification(message, type) {
        // Remover notificaciones existentes
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(n => n.remove());

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    function updateCartCount() {
        fetch('../../controllers/CartController.php?action=count')
            .then(response => response.json())
            .then(data => {
                const cartCount = document.querySelector('.cart-count');
                if (cartCount && data.count !== undefined) {
                    cartCount.textContent = data.count;
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
    }
    </script>
</body>
</html>
