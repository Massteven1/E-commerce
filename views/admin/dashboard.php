<?php
// Verificar autenticación
require_once __DIR__ . '/../../controllers/AuthController.php';
use Controllers\AuthController;

if (!AuthController::isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

// Obtener datos básicos
require_once __DIR__ . '/../../config/Database.php';
use Config\Database;

$database = new Database();
$db = $database->getConnection();

// Estadísticas básicas
$stats = [
    'total_users' => 0,
    'total_courses' => 0,
    'total_orders' => 0,
    'total_revenue' => 0
];

try {
    // Total usuarios
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $stmt->execute();
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total cursos
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM playlists");
    $stmt->execute();
    $stats['total_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total pedidos
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders");
    $stmt->execute();
    $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ingresos totales
    $stmt = $db->prepare("SELECT SUM(amount) as total FROM orders WHERE status = 'completed'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_revenue'] = $result['total'] ?? 0;
    
} catch (Exception $e) {
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
}

$currentUser = AuthController::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="../../public/css/admin/admin-base.css">
    <link rel="stylesheet" href="../../public/css/admin/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../../index.php?page=admin&action=dashboard" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="../../index.php?page=admin&action=users" class="nav-link">
                    <i class="fas fa-users"></i> Usuarios
                </a>
                <a href="../../index.php?page=admin&action=courses" class="nav-link">
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
                <h1>Dashboard</h1>
                <div class="user-info">
                    Bienvenido, <?php echo htmlspecialchars($currentUser['first_name'] ?? 'Admin'); ?>
                </div>
            </header>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Usuarios Totales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_courses']); ?></h3>
                        <p>Cursos Totales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p>Pedidos Totales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Ingresos Totales</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-content">
                <div class="content-section">
                    <h2>Resumen del Sistema</h2>
                    <p>Bienvenido al panel de administración. Aquí puedes gestionar usuarios, cursos y pedidos.</p>
                    
                    <div class="quick-actions">
                        <a href="../../index.php?page=admin&action=users" class="btn btn-primary">
                            <i class="fas fa-users"></i> Gestionar Usuarios
                        </a>
                        <a href="../../index.php?page=admin&action=courses" class="btn btn-secondary">
                            <i class="fas fa-book"></i> Gestionar Cursos
                        </a>
                        <a href="../../index.php?page=admin&action=orders" class="btn btn-success">
                            <i class="fas fa-shopping-cart"></i> Ver Pedidos
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
