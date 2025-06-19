<?php
// Verificar autenticación y permisos de administrador
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/AuthController.php';
use Controllers\AuthController;

if (!AuthController::isAuthenticated() || !AuthController::isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

// Obtener pedidos
$orders = $orders ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Panel de Administración</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Gestión de Pedidos</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="exportOrders()">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>
        </div>

        <!-- Estadísticas de pedidos -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo count($orders); ?></div>
                    <div class="stat-label">Total Pedidos</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <?php echo count(array_filter($orders, function($o) { return $o['status'] === 'completed'; })); ?>
                    </div>
                    <div class="stat-label">Completados</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <?php echo count(array_filter($orders, function($o) { return $o['status'] === 'pending'; })); ?>
                    </div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        $<?php 
                        $total = array_sum(array_map(function($o) { 
                            return $o['status'] === 'completed' ? $o['amount'] : 0; 
                        }, $orders));
                        echo number_format($total, 2);
                        ?>
                    </div>
                    <div class="stat-label">Ingresos Totales</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar pedidos..." class="search-input">
                <button class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="filter-group">
                <select id="statusFilter" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="pending">Pendientes</option>
                    <option value="completed">Completados</option>
                    <option value="failed">Fallidos</option>
                </select>
                <input type="date" id="dateFilter" class="filter-input">
            </div>
        </div>

        <!-- Tabla de pedidos -->
        <div class="table-container">
            <div class="table-header">
                <h3>Lista de Pedidos</h3>
                <div class="table-actions">
                    <button class="btn btn-sm btn-secondary" onclick="refreshTable()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table" id="ordersTable">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Cursos</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay pedidos registrados</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr data-status="<?php echo $order['status']; ?>" data-date="<?php echo date('Y-m-d', strtotime($order['created_at'])); ?>">
                                    <td>
                                        <div class="order-id">
                                            <strong>#<?php echo $order['id']; ?></strong>
                                            <?php if (!empty($order['transaction_id'])): ?>
                                                <small><?php echo substr($order['transaction_id'], 0, 8); ?>...</small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-name">
                                                <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                            </div>
                                            <div class="customer-email">
                                                <?php echo htmlspecialchars($order['email']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="courses-info">
                                            <?php if (!empty($order['courses_purchased'])): ?>
                                                <span class="courses-list">
                                                    <?php echo htmlspecialchars($order['courses_purchased']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="no-courses">Sin cursos</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="amount-info">
                                            <strong>$<?php echo number_format($order['amount'], 2); ?></strong>
                                            <small><?php echo $order['currency'] ?? 'USD'; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php 
                                            $statusLabels = [
                                                'pending' => 'Pendiente',
                                                'completed' => 'Completado',
                                                'failed' => 'Fallido'
                                            ];
                                            echo $statusLabels[$order['status']] ?? ucfirst($order['status']);
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                                            <small><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action view" onclick="viewOrder(<?php echo $order['id']; ?>)" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <button class="btn-action complete" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'completed')" title="Marcar como completado">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal para ver detalles del pedido -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detalles del Pedido</h2>
                <button class="modal-close" onclick="closeOrderModal()">&times;</button>
            </div>
            <div class="modal-body" id="orderModalBody">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        // Funciones de filtrado
        function filterOrders() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            const rows = document.querySelectorAll('#ordersTable tbody tr');

            rows.forEach(row => {
                if (row.cells.length === 1) return; // Skip empty state row
                
                const orderId = row.cells[0].textContent.toLowerCase();
                const customerName = row.cells[1].textContent.toLowerCase();
                const orderStatus = row.dataset.status;
                const orderDate = row.dataset.date;
                
                const matchesSearch = !searchTerm || 
                    orderId.includes(searchTerm) || 
                    customerName.includes(searchTerm);
                const matchesStatus = !statusFilter || orderStatus === statusFilter;
                const matchesDate = !dateFilter || orderDate === dateFilter;
                
                if (matchesSearch && matchesStatus && matchesDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Event listeners para filtros
        document.getElementById('searchInput').addEventListener('input', filterOrders);
        document.getElementById('statusFilter').addEventListener('change', filterOrders);
        document.getElementById('dateFilter').addEventListener('change', filterOrders);

        // Funciones de acciones
        function viewOrder(orderId) {
            // Cargar detalles del pedido
            fetch(`index.php?controller=admin&action=ajax`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_order_details&order_id=${orderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('orderModalBody').innerHTML = data.html;
                    document.getElementById('orderModal').style.display = 'flex';
                } else {
                    alert('Error al cargar los detalles del pedido');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los detalles del pedido');
            });
        }

        function updateOrderStatus(orderId, newStatus) {
            if (confirm(`¿Estás seguro de que deseas marcar este pedido como ${newStatus}?`)) {
                fetch(`index.php?controller=admin&action=ajax`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_order_status&order_id=${orderId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al actualizar el estado del pedido');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar el estado del pedido');
                });
            }
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function refreshTable() {
            location.reload();
        }

        function exportOrders() {
            window.open('index.php?controller=admin&action=export_orders', '_blank');
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
    </script>

    <style>
        .order-id strong {
            color: var(--primary-color);
        }

        .order-id small {
            display: block;
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        .customer-info {
            min-width: 150px;
        }

        .customer-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .customer-email {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .courses-info {
            max-width: 200px;
        }

        .courses-list {
            font-size: 0.875rem;
            color: var(--text-color);
        }

        .no-courses {
            color: var(--text-muted);
            font-style: italic;
        }

        .amount-info strong {
            color: var(--success-color);
            font-size: 1.1rem;
        }

        .amount-info small {
            display: block;
            color: var(--text-muted);
        }

        .status-pending {
            background: var(--warning-light);
            color: var(--warning-color);
        }

        .status-completed {
            background: var(--success-light);
            color: var(--success-color);
        }

        .status-failed {
            background: var(--danger-light);
            color: var(--danger-color);
        }

        .btn-action.complete {
            background: var(--success-color);
            color: white;
        }

        .stat-icon.total { background: var(--primary-color); }
        .stat-icon.completed { background: var(--success-color); }
        .stat-icon.pending { background: var(--warning-color); }
        .stat-icon.revenue { background: var(--info-color); }

        .filter-input {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .courses-info {
                max-width: 120px;
            }
            
            .courses-list {
                font-size: 0.75rem;
            }
        }
    </style>
</body>
</html>
