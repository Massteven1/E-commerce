<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Protección de ruta: Redirigir si no es admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../admin_login.html'); // Redirige a la página de login de admin
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <!-- Enlace al nuevo archivo de estilos de administrador -->
    <link rel="stylesheet" href="../../public/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

    <header>
        <div class="container">
            <a href="courses.php?controller=admin&action=dashboard" class="logo">
                <div class="logo-circle" style="background-color: var(--primary-color);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span>Admin Panel</span>
            </a>
            <div class="search-bar">
                <input type="text" placeholder="Buscar...">
                <i class="fas fa-search"></i>
            </div>
            <nav>
                <ul>
                    <li><a href="courses.php?controller=admin&action=dashboard">Dashboard</a></li>
                    <li><a href="#">Users</a></li>
                    <li><a href="courses.php?controller=playlist&action=index">Courses</a></li>
                    <li><a href="#">Reports</a></li>
                </ul>
                <a href="#" class="logout" id="logoutBtn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </nav>
        </div>
    </header>

    <div class="banner">
        <div class="container">
            <h1>Panel de Administración</h1>
            <p>Bienvenido al centro de control de tu plataforma.</p>
        </div>
    </div>

    <section class="dashboard">
        <div class="container">
            <div class="dashboard-summary">
                <div class="summary-card">
                    <i class="fas fa-users"></i>
                    <h3>1250</h3> <!-- TODO: make it functional -->
                    <p>Usuarios Registrados</p>
                </div>
                <div class="summary-card">
                    <i class="fas fa-box-open"></i>
                    <h3>320</h3>
                    <p>Productos en Stock</p>
                </div>
                <div class="summary-card">
                    <i class="fas fa-chart-bar"></i>
                    <h3>$15,500</h3>
                    <p>Ventas Mensuales</p>
                </div>
                <div class="summary-card">
                    <i class="fas fa-comments"></i>
                    <h3>85</h3>
                    <p>Nuevos Comentarios</p>
                </div>
            </div>

            <div class="dashboard-content">
                <div class="recent-orders">
                    <h2>Órdenes Recientes</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#1022</td>
                                <td>Carlos López</td>
                                <td>2024-01-15</td>
                                <td>Procesando</td>
                                <td>$80.00</td>
                            </tr>
                            <!-- Más filas de ejemplo -->
                            <tr>
                                <td>#1023</td>
                                <td>Ana García</td>
                                <td>2024-01-14</td>
                                <td>Completado</td>
                                <td>$120.00</td>
                            </tr>
                            <tr>
                                <td>#1024</td>
                                <td>Juan Pérez</td>
                                <td>2024-01-13</td>
                                <td>Pendiente</td>
                                <td>$50.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="product-stats">
                    <h2>Estadísticas de Productos</h2>
                    <div class="chart">
                        <!-- Aquí iría un gráfico real (puedes usar Chart.js o similar) -->
                        <p>Gráfico de Ventas por Producto</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <!-- Puedes añadir un pie de página aquí si lo deseas -->
    </footer>

    <a href="#" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="../../auth/firebase-config.js"></script>
    <script src="../../auth/auth.js"></script>
</body>
</html>
