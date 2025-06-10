<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/Course.php';
require_once dirname(__DIR__) . '/models/User.php';

// Verificar que el usuario sea administrador
requireAdmin();

$pageTitle = 'Panel de Administración - El Profesor Hernan';

// Obtener estadísticas
$courseModel = new Course();
$userModel = new User();

$totalCourses = count($courseModel->getAll());
$totalUsers = $userModel->getTotalUsers();
$recentCourses = array_slice($courseModel->getAll(), 0, 5);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-container">
            <div class="admin-logo">
                <h2><i class="fas fa-graduation-cap"></i> Panel Admin</h2>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                    <li><a href="courses.php"><i class="fas fa-book"></i> Cursos</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Usuarios</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Ver Sitio</a></li>
                </ul>
            </nav>
            <div class="admin-user">
                <span>Bienvenido, <?php echo $_SESSION['user_name']; ?></span>
                <button id="adminLogoutBtn" class="btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </div>
        </div>
    </header>

    <!-- Admin Content -->
    <main class="admin-main">
        <div class="admin-container">
            <div class="admin-breadcrumb">
                <h1><i class="fas fa-dashboard"></i> Dashboard</h1>
                <p>Resumen general del sistema</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalCourses; ?></h3>
                        <p>Total Cursos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total Usuarios</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>0</h3>
                        <p>Pedidos Hoy</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$0</h3>
                        <p>Ingresos Mes</p>
                    </div>
                </div>
            </div>

            <!-- Recent Courses -->
            <div class="admin-section">
                <div class="section-header">
                    <h2><i class="fas fa-book"></i> Cursos Recientes</h2>
                    <a href="courses.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Curso
                    </a>
                </div>
                
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Nivel</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentCourses)): ?>
                                <?php foreach ($recentCourses as $course): ?>
                                    <tr>
                                        <td><?php echo $course['id']; ?></td>
                                        <td>
                                            <div class="course-title-cell">
                                                <?php if (!empty($course['image_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="Curso" class="course-thumb">
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($course['title']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="level-badge <?php echo strtolower($course['level']); ?>">
                                                <?php echo $course['level']; ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($course['price'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $course['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $course['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="courses.php?edit=<?php echo $course['id']; ?>" class="btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn-delete" onclick="deleteCourse(<?php echo $course['id']; ?>)" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-data">No hay cursos disponibles</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="admin-section">
                <div class="section-header">
                    <h2><i class="fas fa-bolt"></i> Acciones Rápidas</h2>
                </div>
                
                <div class="quick-actions">
                    <a href="courses.php" class="quick-action-card">
                        <i class="fas fa-plus"></i>
                        <h3>Nuevo Curso</h3>
                        <p>Crear un nuevo curso</p>
                    </a>
                    
                    <a href="users.php" class="quick-action-card">
                        <i class="fas fa-user-plus"></i>
                        <h3>Gestionar Usuarios</h3>
                        <p>Ver y administrar usuarios</p>
                    </a>
                    
                    <a href="orders.php" class="quick-action-card">
                        <i class="fas fa-chart-line"></i>
                        <h3>Ver Reportes</h3>
                        <p>Estadísticas y reportes</p>
                    </a>
                    
                    <a href="../index.php" class="quick-action-card">
                        <i class="fas fa-eye"></i>
                        <h3>Ver Sitio Web</h3>
                        <p>Ir al sitio público</p>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Scripts -->
    <script type="module" src="../assets/js/firebase-config.js"></script>
    <script type="module" src="../assets/js/auth.js"></script>
    <script type="module" src="../assets/js/admin.js"></script>
    
    <script>
        // Función para eliminar curso
        function deleteCourse(courseId) {
            if (confirm('¿Estás seguro de que quieres eliminar este curso?')) {
                fetch('../api/courses.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: courseId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al eliminar el curso: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el curso');
                });
            }
        }
    </script>
</body>
</html>
