<?php
error_reporting(E_ALL); // Reporta todos los errores de PHP
ini_set('display_errors', 1); // Muestra los errores en pantalla (solo para desarrollo)

// Verificar autenticación
require_once __DIR__ . '/../../controllers/AuthController.php';
use Controllers\AuthController;

if (!AuthController::isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

// Obtener usuarios
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../models/User.php';
use Config\Database;
use Models\User;

$database = new Database();
$db = $database->getConnection();

// --- INICIO DE DEPURACIÓN ---
echo "<!-- Debugging Database Connection and Users -->\n";
if ($db) {
    echo "<!-- Conexión a la base de datos establecida. -->\n";
    // Opcional: Probar la conexión explícitamente
    if ($database->testConnection()) {
        echo "<!-- Prueba de conexión a la base de datos exitosa. -->\n";
    } else {
        echo "<!-- FALLO: La prueba de conexión a la base de datos falló. Revisa las credenciales/servidor. -->\n";
    }
    $userModel = new User($db);
    $users = $userModel->readAll();
    echo "<!-- Número de usuarios obtenidos: " . count($users) . " -->\n";
    // Descomenta la siguiente línea para ver el contenido completo del array de usuarios:
    // echo "<pre>"; var_dump($users); echo "</pre>";
} else {
    echo "<!-- ERROR: La conexión a la base de datos es NULA. Revisa config/Database.php -->\n";
    $users = []; // Asegura que $users sea un array vacío si la conexión falla
}
echo "<!-- FIN DE DEPURACIÓN -->\n";
// --- FIN DE DEPURACIÓN ---

$currentUser = AuthController::getCurrentUser();

// Manejar acciones POST (toggle status, delete user)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json'); // Asegurar que la respuesta sea JSON
    $response = ['success' => false, 'message' => ''];

    if ($_POST['action'] === 'toggle_status' && isset($_POST['user_id']) && isset($_POST['status'])) {
        $userId = (int)$_POST['user_id'];
        $newStatus = (int)$_POST['status'];
        
        // No permitir desactivar al propio usuario logueado
        if ($userId == $currentUser['id']) {
            $response['message'] = 'No puedes cambiar tu propio estado.';
        } else {
            $userToUpdate = new User($db);
            $userToUpdate->id = $userId;
            $userToUpdate->is_active = $newStatus;
            if ($userToUpdate->toggleStatus()) {
                $response['success'] = true;
                $response['message'] = 'Estado del usuario actualizado correctamente.';
            } else {
                $response['message'] = 'Error al actualizar el estado del usuario.';
            }
        }
    } elseif ($_POST['action'] === 'delete_user' && isset($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];

        // No permitir eliminar al propio usuario logueado
        if ($userId == $currentUser['id']) {
            $response['message'] = 'No puedes eliminar tu propia cuenta.';
        } else {
            $userToDelete = new User($db);
            $userToDelete->id = $userId;
            if ($userToDelete->deactivate()) {
                $response['success'] = true;
                $response['message'] = 'Usuario desactivado correctamente.';
            } else {
                $response['message'] = 'Error al desactivar el usuario.';
            }
        }
    }
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Admin Panel</title>
    <link rel="stylesheet" href="../../public/css/admin/admin-base.css">
    <link rel="stylesheet" href="../../public/css/admin/users.css">
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
                <a href="../../index.php?page=admin&action=users" class="nav-link active">
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
                <h1>Gestión de Usuarios</h1>
                <div class="user-info">
                    Bienvenido, <?php echo htmlspecialchars($currentUser['first_name'] ?? 'Admin'); ?>
                </div>
            </header>

            <div class="users-content">
                <div class="filters-section">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="userSearch" placeholder="Buscar por nombre o email...">
                    </div>
                    <div class="filter-controls">
                        <select id="roleFilter">
                            <option value="">Todos los Roles</option>
                            <option value="user">Usuario</option>
                            <option value="admin">Admin</option>
                        </select>
                        <select id="statusFilter">
                            <option value="">Todos los Estados</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-primary" onclick="exportUsers()">
                            <i class="fas fa-file-export"></i> Exportar
                        </button>
                    </div>
                </div>

                <div class="users-table-container">
                    <table class="users-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha Registro</th>
                                <th>Último Login</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr data-user-id="<?php echo htmlspecialchars($user['id']); ?>">
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo htmlspecialchars(strtoupper(substr($user['first_name'], 0, 1))); ?>
                                                </div>
                                                <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $user['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'N/A'; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($user['id'] != $currentUser['id']): ?>
                                                    <button class="btn btn-sm <?php echo $user['is_active'] ? 'btn-warning' : 'btn-success'; ?>" 
                                                            onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active'] ? 0 : 1; ?>)">
                                                        <i class="fas <?php echo $user['is_active'] ? 'fa-ban' : 'fa-check'; ?>"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No hay usuarios registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- User Details Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <div id="userModalBody">
                <!-- User details will be loaded here by JavaScript -->
            </div>
        </div>
    </div>

    <script src="../../public/js/admin/users.js"></script>
</body>
</html>
