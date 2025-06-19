<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir dependencias necesarias
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

use Controllers\AuthController;
use Controllers\AdminController;

// Verificar autenticación y permisos de administrador
AuthController::requireAdmin('../../login.php');

// Obtener datos del dashboard
try {
    $adminController = new AdminController();
    $dashboardData = $adminController->getDashboardData();
    
    // Extraer datos con valores por defecto
    $stats = $dashboardData['stats'] ?? [
        'total_courses' => 0,
        'total_users' => 0,
        'total_orders' => 0,
        'total_revenue' => 0,
        'pending_orders' => 0,
        'active_users' => 0,
        'monthly_revenue' => 0,
        'growth_percentage' => 0
    ];
    
    $recentOrders = $dashboardData['recentOrders'] ?? [];
    $recentUsers = $dashboardData['recentUsers'] ?? [];
    $popularCourses = $dashboardData['popularCourses'] ?? [];
    $monthlyStats = $dashboardData['monthlyStats'] ?? [];
    $topSellingCourses = $dashboardData['topSellingCourses'] ?? [];
    
} catch (Exception $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    
    // Valores por defecto en caso de error
    $stats = [
        'total_courses' => 0,
        'total_users' => 0,
        'total_orders' => 0,
        'total_revenue' => 0,
        'pending_orders' => 0,
        'active_users' => 0,
        'monthly_revenue' => 0,
        'growth_percentage' => 0
    ];
    $recentOrders = [];
    $recentUsers = [];
    $popularCourses = [];
    $monthlyStats = [];
    $topSellingCourses = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel de Administración</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="refreshStats()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
                <select id="periodSelector" class="form-select" onchange="changePeriod(this.value)">
                    <option value="30days">Últimos 30 días</option>
                    <option value="7days">Últimos 7 días</option>
                    <option value="90days">Últimos 90 días</option>
                    <option value="1year">Último año</option>
                </select>
            </div>
        </div>

        <!-- Estadísticas principales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon courses">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo number_format($stats['total_courses']); ?></div>
                    <div class="stat-label">Cursos Totales</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Usuarios Registrados</div>
                    <div class="stat-change">
                        <small><?php echo number_format($stats['active_users']); ?> activos este mes</small>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stat-label">Pedidos Totales</div>
                    <div class="stat-change">
                        <small><?php echo number_format($stats['pending_orders']); ?> pendientes</small>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
                    <div class="stat-label">Ingresos Totales</div>
                    <div class="stat-change <?php echo $stats['growth_percentage'] >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $stats['growth_percentage'] >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($stats['growth_percentage']); ?>% vs mes anterior
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos y estadísticas avanzadas -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="card-header">
                    <h3>Ingresos Mensuales</h3>
                </div>
                <div class="card-content">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="card-header">
                    <h3>Crecimiento de Usuarios</h3>
                </div>
                <div class="card-content">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="dashboard-grid">
            <!-- Pedidos recientes -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Pedidos Recientes</h3>
                    <a href="orders.php" class="view-all">Ver todos</a>
                </div>
                <div class="card-content">
                    <?php if (empty($recentOrders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-cart"></i>
                            <p>No hay pedidos recientes</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach (array_slice($recentOrders, 0, 5) as $order): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <div class="order-id">#<?php echo $order['id']; ?></div>
                                        <div class="order-customer">
                                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                        </div>
                                        <div class="order-date">
                                            <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div class="order-details">
                                        <div class="order-amount">$<?php echo number_format($order['amount'], 2); ?></div>
                                        <div class="order-status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Usuarios recientes -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Usuarios Recientes</h3>
                    <a href="users.php" class="view-all">Ver todos</a>
                </div>
                <div class="card-content">
                    <?php if (empty($recentUsers)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No hay usuarios recientes</p>
                        </div>
                    <?php else: ?>
                        <div class="users-list">
                            <?php foreach (array_slice($recentUsers, 0, 5) as $user): ?>
                                <div class="user-item">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                    </div>
                                    <div class="user-info">
                                        <div class="user-name">
                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                        </div>
                                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                        <div class="user-date">
                                            Registrado: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cursos más vendidos -->
            <div class="dashboard-card full-width">
                <div class="card-header">
                    <h3>Cursos Más Vendidos</h3>
                    <a href="courses.php" class="view-all">Ver todos</a>
                </div>
                <div class="card-content">
                    <?php if (empty($topSellingCourses)): ?>
                        <div class="empty-state">
                            <i class="fas fa-book"></i>
                            <p>No hay datos de ventas disponibles</p>
                        </div>
                    <?php else: ?>
                        <div class="courses-grid">
                            <?php foreach (array_slice($topSellingCourses, 0, 6) as $course): ?>
                                <div class="course-card">
                                    <div class="course-image">
                                        <?php if (!empty($course['cover_image'])): ?>
                                            <img src="../../<?php echo htmlspecialchars($course['cover_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($course['name']); ?>">
                                        <?php else: ?>
                                            <div class="course-placeholder">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="course-info">
                                        <h4><?php echo htmlspecialchars($course['name']); ?></h4>
                                        <div class="course-stats">
                                            <span class="course-sales"><?php echo $course['sales_count'] ?? 0; ?> ventas</span>
                                            <span class="course-revenue">$<?php echo number_format($course['total_revenue'] ?? 0, 2); ?></span>
                                        </div>
                                        <div class="course-price">$<?php echo number_format($course['price'], 2); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    // Datos para los gráficos
    const monthlyData = <?php echo json_encode($monthlyStats); ?>;
    
    // Configurar gráfico de ingresos
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month),
            datasets: [{
                label: 'Ingresos',
                data: monthlyData.map(item => item.revenue),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    function refreshStats() {
        location.reload();
    }

    function changePeriod(period) {
        // Implementar cambio de período
        console.log('Cambiando período a:', period);
    }

    // Auto-refresh cada 5 minutos
    setInterval(refreshStats, 300000);
    </script>

    <style>
    .admin-body {
        background-color: var(--light-bg);
        font-family: var(--font-family);
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .admin-header h1 {
        color: var(--text-color);
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .form-select {
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: white;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--white);
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-icon.courses { background: var(--primary-color); }
    .stat-icon.users { background: var(--success-color); }
    .stat-icon.orders { background: var(--warning-color); }
    .stat-icon.revenue { background: var(--info-color); }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 0.25rem;
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    .stat-change {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .stat-change.positive {
        color: var(--success-color);
    }

    .stat-change.negative {
        color: var(--danger-color);
    }

    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .chart-card {
        background: var(--white);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .dashboard-card {
        background: var(--white);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .dashboard-card.full-width {
        grid-column: 1 / -1;
    }

    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h3 {
        margin: 0;
        color: var(--text-color);
    }

    .view-all {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .card-content {
        padding: 1.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .order-item, .user-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .order-item:last-child, .user-item:last-child {
        border-bottom: none;
    }

    .order-info {
        flex: 1;
    }

    .order-id {
        font-weight: 600;
        color: var(--text-color);
    }

    .order-customer, .user-name {
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 0.25rem;
    }

    .order-date, .user-email, .user-date {
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .order-amount {
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 0.25rem;
    }

    .order-status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-completed { background: var(--success-light); color: var(--success-color); }
    .status-pending { background: var(--warning-light); color: var(--warning-color); }
    .status-failed { background: var(--danger-light); color: var(--danger-color); }

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
        margin-right: 1rem;
    }

    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .course-card {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
    }

    .course-image {
        height: 120px;
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
        font-size: 2rem;
    }

    .course-info {
        padding: 1rem;
    }

    .course-info h4 {
        margin: 0 0 0.5rem 0;
        font-size: 0.875rem;
        color: var(--text-color);
    }

    .course-stats {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .course-sales, .course-revenue {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .course-price {
        font-weight: 600;
        color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .dashboard-grid, .charts-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .courses-grid {
            grid-template-columns: 1fr;
        }
        
        .header-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
    </style>
</body>
</html>
