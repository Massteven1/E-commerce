<?php
// Verificar autenticación
require_once __DIR__ . '/../../controllers/AuthController.php';
use Controllers\AuthController;

if (!AuthController::isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

// Obtener pedidos
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/Order.php';
use Config\Database;
use Models\Order;

$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);
//!FIXME: $orders = $orderModel->readAll();

$currentUser = AuthController::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Admin Panel</title>
    <link rel="stylesheet" href="../../public/css/admin/admin-base.css">
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
                <a href="../../index.php?page=admin&action=dashboard" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="../../index.php?page=admin&action=users" class="nav-link">
                    <i class="fas fa-users"></i> Usuarios
                </a>
                <a href="../../index.php?page=admin&action=courses" class="nav-link">
                    <i class="fas fa-book"></i> Cursos
                </a>
                <a href="../../index.php?page=admin&action=orders" class="nav-link active">
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
                <h1>Gestión de Pedidos</h1>
                <div class="user-info">
                    Bienvenido, <?php echo htmlspecialchars($currentUser['first_name'] ?? 'Admin'); ?>
                </div>
            </header>

            <div class="orders-content">
                <div class="orders-table-container">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                                        <td><?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')); ?></td>
                                        <td>$<?php echo number_format($order['amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="approveOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay pedidos registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function viewOrder(orderId) {
            alert('Ver detalles del pedido ID: ' + orderId);
        }

        function approveOrder(orderId) {
            if (confirm('¿Aprobar este pedido?')) {
                alert('Pedido aprobado ID: ' + orderId);
                location.reload();
            }
        }
    </script>
</body>
</html>
