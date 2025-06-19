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

// Obtener usuarios
$users = $users ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Panel de Administración</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Gestión de Usuarios</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="exportUsers()">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>
        </div>

        <!-- Estadísticas de usuarios -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo count($users); ?></div>
                    <div class="stat-label">Total Usuarios</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <?php echo count(array_filter($users, function($u) { return $u['is_active']; })); ?>
                    </div>
                    <div class="stat-label">Usuarios Activos</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon admins">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <?php echo count(array_filter($users, function($u) { return $u['role'] === 'admin'; })); ?>
                    </div>
                    <div class="stat-label">Administradores</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon new">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <?php 
                        $thisMonth = date('Y-m');
                        echo count(array_filter($users, function($u) use ($thisMonth) { 
                            return strpos($u['created_at'], $thisMonth) === 0; 
                        })); 
                        ?>
                    </div>
                    <div class="stat-label">Nuevos Este Mes</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar usuarios..." class="search-input">
                <button class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="filter-group">
                <select id="roleFilter" class="filter-select">
                    <option value="">Todos los roles</option>
                    <option value="user">Usuarios</option>
                    <option value="admin">Administradores</option>
                </select>
                <select id="statusFilter" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="table-container">
            <div class="table-header">
                <h3>Lista de Usuarios</h3>
                <div class="table-actions">
                    <button class="btn btn-sm btn-secondary" onclick="refreshTable()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Cursos</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay usuarios registrados</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['is_active']; ?>">
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                </div>
                                                <div class="user-id">ID: <?php echo $user['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo $user['role'] === 'admin' ? 'Administrador' : 'Usuario'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $user['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="courses-count">
                                            <?php echo $user['courses_count'] ?? 0; ?> cursos
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></div>
                                            <small><?php echo date('H:i', strtotime($user['created_at'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action view" onclick="viewUser(<?php echo $user['id']; ?>)" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <button class="btn-action toggle" 
                                                        onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active'] ? 0 : 1; ?>)" 
                                                        title="<?php echo $user['is_active'] ? 'Desactivar' : 'Activar'; ?>">
                                                    <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
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

    <!-- Modal para ver detalles del usuario -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detalles del Usuario</h2>
                <button class="modal-close" onclick="closeUserModal()">&times;</button>
            </div>
            <div class="modal-body" id="userModalBody">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        // Funciones de filtrado
        function filterUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#usersTable tbody tr');

            rows.forEach(row => {
                if (row.cells.length === 1) return; // Skip empty state row
                
                const userName = row.cells[0].textContent.toLowerCase();
                const userEmail = row.cells[1].textContent.toLowerCase();
                const userRole = row.dataset.role;
                const userStatus = row.dataset.status;
                
                const matchesSearch = !searchTerm || 
                    userName.includes(searchTerm) || 
                    userEmail.includes(searchTerm);
                const matchesRole = !roleFilter || userRole === roleFilter;
                const matchesStatus = !statusFilter || userStatus === statusFilter;
                
                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Event listeners para filtros
        document.getElementById('searchInput').addEventListener('input', filterUsers);
        document.getElementById('roleFilter').addEventListener('change', filterUsers);
        document.getElementById('statusFilter').addEventListener('change', filterUsers);

        // Funciones de acciones
        function viewUser(userId) {
            // Cargar detalles del usuario
            fetch(`index.php?controller=admin&action=ajax`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_user_details&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('userModalBody').innerHTML = data.html;
                    document.getElementById('userModal').style.display = 'flex';
                } else {
                    alert('Error al cargar los detalles del usuario');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los detalles del usuario');
            });
        }

        function toggleUserStatus(userId, newStatus) {
            const action = newStatus ? 'activar' : 'desactivar';
            if (confirm(`¿Estás seguro de que deseas ${action} este usuario?`)) {
                fetch(`index.php?controller=admin&action=ajax`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=toggle_user_status&user_id=${userId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al actualizar el estado del usuario');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar el estado del usuario');
                });
            }
        }

        function closeUserModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        function refreshTable() {
            location.reload();
        }

        function exportUsers() {
            window.open('index.php?controller=admin&action=export_users', '_blank');
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserModal();
            }
        });
    </script>

    <style>
        .table-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            margin: 0;
            color: var(--text-color);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-table th {
            background-color: var(--light-bg);
            font-weight: 600;
            color: var(--text-color);
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
        }

        .user-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .user-id {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .role-admin {
            background: var(--danger-light);
            color: var(--danger-color);
        }

        .role-user {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-active {
            background: var(--success-light);
            color: var(--success-color);
        }

        .status-inactive {
            background: var(--warning-light);
            color: var(--warning-color);
        }

        .courses-count {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .date-info {
            font-size: 0.875rem;
        }

        .date-info small {
            color: var(--text-muted);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-action.view {
            background: var(--info-color);
            color: white;
        }

        .btn-action.toggle {
            background: var(--warning-color);
            color: white;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        .stat-icon.users { background: var(--primary-color); }
        .stat-icon.active { background: var(--success-color); }
        .stat-icon.admins { background: var(--danger-color); }
        .stat-icon.new { background: var(--info-color); }

        @media (max-width: 768px) {
            .admin-table {
                font-size: 0.875rem;
            }
            
            .user-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
