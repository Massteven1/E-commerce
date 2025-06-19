<?php
// Incluir las dependencias necesarias
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Playlist.php';

use Controllers\AuthController;
use Config\Database;
use Models\Playlist;

// Obtener la página actual para marcar el elemento activo
$currentController = $_GET['controller'] ?? 'admin';
$currentAction = $_GET['action'] ?? 'dashboard';

// Función para determinar si un enlace está activo
function isActive($controller, $action = null) {
    global $currentController, $currentAction;
    if ($action) {
        return $currentController === $controller && $currentAction === $action;
    }
    return $currentController === $controller;
}

// Obtener usuario actual de forma segura
$currentUser = null;
try {
    $currentUser = AuthController::getCurrentUser();
} catch (Exception $e) {
    // Si hay error, redirigir al login
    header('Location: ../../login.php');
    exit();
}

// Si no hay usuario, redirigir al login
if (!$currentUser) {
    header('Location: ../../login.php');
    exit();
}

// Obtener estadísticas de forma segura
$totalCourses = 0;
$pendingOrders = 0;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Obtener total de cursos
    $playlistModel = new Playlist($db);
    $playlists = $playlistModel->readAll();
    $totalCourses = count($playlists);
    
    // Obtener pedidos pendientes
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pendingOrders = $result['total'] ?? 0;
    
} catch (Exception $e) {
    // En caso de error, usar valores por defecto
    error_log("Error en sidebar.php: " . $e->getMessage());
}
?>

<nav class="admin-sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <span>Admin Panel</span>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($currentUser['first_name'], 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                <div class="user-role">Administrador</div>
            </div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="dashboard.php" 
               class="menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="courses.php" 
               class="menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'courses.php') ? 'active' : ''; ?>">
                <i class="fas fa-book"></i>
                <span>Cursos</span>
                <?php if ($totalCourses > 0): ?>
                    <span class="menu-badge"><?php echo $totalCourses; ?></span>
                <?php endif; ?>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="upload_video.php" 
               class="menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'upload_video.php') ? 'active' : ''; ?>">
                <i class="fas fa-video"></i>
                <span>Subir Videos</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="users.php" 
               class="menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="orders.php" 
               class="menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Pedidos</span>
                <?php if ($pendingOrders > 0): ?>
                    <span class="notification-dot"><?php echo $pendingOrders; ?></span>
                <?php endif; ?>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="settings.php" 
               class="menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="../client/home.php" class="footer-link" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>Ver Sitio Web</span>
        </a>
        <a href="../../logout.php" class="footer-link logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</nav>

<style>
.admin-sidebar {
    width: 260px;
    background: var(--white);
    border-right: 1px solid var(--border-color);
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--primary-color);
}

.logo i {
    font-size: 1.5rem;
}

.sidebar-user {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}

.user-name {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.25rem;
}

.user-role {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.sidebar-menu {
    list-style: none;
    padding: 1rem 0;
    margin: 0;
}

.menu-item {
    margin: 0;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: var(--text-color);
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
}

.menu-link:hover {
    background-color: var(--light-bg);
    color: var(--primary-color);
}

.menu-link.active {
    background-color: var(--primary-light);
    color: var(--primary-color);
    border-right: 3px solid var(--primary-color);
}

.menu-link i {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

.menu-badge {
    margin-left: auto;
    background: var(--primary-light);
    color: var(--primary-color);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.notification-dot {
    margin-left: auto;
    background: var(--danger-color);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.sidebar-footer {
    margin-top: auto;
    padding: 1rem;
    border-top: 1px solid var(--border-color);
}

.footer-link {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    color: var(--text-muted);
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s ease;
    margin-bottom: 0.5rem;
}

.footer-link:hover {
    background-color: var(--light-bg);
    color: var(--text-color);
}

.footer-link.logout {
    color: var(--danger-color);
}

.footer-link.logout:hover {
    background-color: rgba(220, 53, 69, 0.1);
}

.footer-link i {
    margin-right: 0.75rem;
    width: 16px;
    text-align: center;
}

/* Layout para el contenido principal */
.admin-content {
    margin-left: 260px;
    padding: 2rem;
    min-height: 100vh;
    background-color: var(--light-bg);
}

/* Responsive */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .admin-sidebar.open {
        transform: translateX(0);
    }
    
    .admin-content {
        margin-left: 0;
        padding: 1rem;
    }
}

/* Scrollbar personalizado */
.admin-sidebar::-webkit-scrollbar {
    width: 6px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: var(--light-bg);
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}
</style>
