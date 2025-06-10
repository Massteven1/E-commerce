<?php
require_once '../config/config.php';
require_once '../models/Course.php';

// Verificar que sea administrador
requireAdmin();

$pageTitle = 'Gestión de Cursos - Admin';
$courseModel = new Course();

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $courseModel->title = $_POST['title'];
        $courseModel->description = $_POST['description'];
        $courseModel->price = $_POST['price'];
        $courseModel->level = $_POST['level'];
        $courseModel->image_url = $_POST['image_url'] ?? '';
        
        if ($courseModel->create()) {
            setMessage('Curso creado exitosamente', 'success');
        } else {
            setMessage('Error al crear el curso', 'error');
        }
        redirect('courses.php');
    }
    
    if ($action === 'update') {
        $courseModel->id = $_POST['id'];
        $courseModel->title = $_POST['title'];
        $courseModel->description = $_POST['description'];
        $courseModel->price = $_POST['price'];
        $courseModel->level = $_POST['level'];
        $courseModel->image_url = $_POST['image_url'] ?? '';
        
        if ($courseModel->update()) {
            setMessage('Curso actualizado exitosamente', 'success');
        } else {
            setMessage('Error al actualizar el curso', 'error');
        }
        redirect('courses.php');
    }
    
    if ($action === 'delete') {
        if ($courseModel->delete($_POST['id'])) {
            setMessage('Curso eliminado exitosamente', 'success');
        } else {
            setMessage('Error al eliminar el curso', 'error');
        }
        redirect('courses.php');
    }
}

// Obtener cursos
$courses = $courseModel->getAll();
$editCourse = null;

if (isset($_GET['edit'])) {
    $editCourse = $courseModel->getById($_GET['edit']);
}

$message = getMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-logo">
            <h2>Admin Panel</h2>
        </div>
        <nav class="admin-nav">
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="courses.php" class="active"><i class="fas fa-book"></i> Cursos</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Usuarios</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Órdenes</a></li>
                <li><a href="videos.php"><i class="fas fa-video"></i> Videos</a></li>
                <li><a href="questions.php"><i class="fas fa-question-circle"></i> Preguntas</a></li>
                <li><a href="../index.php"><i class="fas fa-home"></i> Volver al Sitio</a></li>
                <li><a href="#" id="adminLogout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </nav>
    </div>

    <!-- Admin Content -->
    <div class="admin-content">
        <div class="admin-header">
            <h1>Gestión de Cursos</h1>
            <button class="btn-primary" id="newCourseBtn">
                <i class="fas fa-plus"></i> Nuevo Curso
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <?php echo $message['message']; ?>
            </div>
        <?php endif; ?>

        <!-- Courses Table -->
        <div class="admin-section">
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Nivel</th>
                            <th>Precio</th>
                            <th>Videos</th>
                            <th>Calificación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td>
                                    <span class="level-badge level-<?php echo strtolower($course['level']); ?>">
                                        <?php echo $course['level']; ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($course['price'], 2); ?></td>
                                <td><?php echo $course['video_count'] ?? 0; ?></td>
                                <td>
                                    <?php if ($course['average_rating']): ?>
                                        <?php echo number_format($course['average_rating'], 1); ?>/5
                                        (<?php echo $course['rating_count']; ?>)
                                    <?php else: ?>
                                        Sin calificar
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $course['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $course['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $course['id']; ?>" class="btn-sm btn-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="videos.php?course_id=<?php echo $course['id']; ?>" class="btn-sm btn-info">
                                            <i class="fas fa-video"></i>
                                        </a>
                                        <button class="btn-sm btn-danger delete-course" data-id="<?php echo $course['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Course Modal -->
    <div class="modal" id="courseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"><?php echo $editCourse ? 'Editar Curso' : 'Nuevo Curso'; ?></h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="courseForm" method="POST">
                    <input type="hidden" name="action" value="<?php echo $editCourse ? 'update' : 'create'; ?>">
                    <?php if ($editCourse): ?>
                        <input type="hidden" name="id" value="<?php echo $editCourse['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Título del Curso</label>
                        <input type="text" id="title" name="title" value="<?php echo $editCourse ? htmlspecialchars($editCourse['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" rows="4" required><?php echo $editCourse ? htmlspecialchars($editCourse['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Precio ($)</label>
                            <input type="number" id="price" name="price" step="0.01" value="<?php echo $editCourse ? $editCourse['price'] : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="level">Nivel</label>
                            <select id="level" name="level" required>
                                <option value="">Seleccionar nivel</option>
                                <option value="A1" <?php echo ($editCourse && $editCourse['level'] === 'A1') ? 'selected' : ''; ?>>A1 - Básico</option>
                                <option value="A2" <?php echo ($editCourse && $editCourse['level'] === 'A2') ? 'selected' : ''; ?>>A2 - Pre Intermedio</option>
                                <option value="B1" <?php echo ($editCourse && $editCourse['level'] === 'B1') ? 'selected' : ''; ?>>B1 - Intermedio</option>
                                <option value="B2" <?php echo ($editCourse && $editCourse['level'] === 'B2') ? 'selected' : ''; ?>>B2 - Intermedio Alto</option>
                                <option value="C1" <?php echo ($editCourse && $editCourse['level'] === 'C1') ? 'selected' : ''; ?>>C1 - Avanzado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url">URL de la Imagen</label>
                        <input type="url" id="image_url" name="image_url" value="<?php echo $editCourse ? htmlspecialchars($editCourse['image_url']) : ''; ?>">
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" id="cancelBtn">Cancelar</button>
                        <button type="submit" class="btn-primary">
                            <?php echo $editCourse ? 'Actualizar' : 'Crear'; ?> Curso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script type="module" src="../assets/js/admin.js"></script>
    <script>
        // Mostrar modal si estamos editando
        <?php if ($editCourse): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('courseModal').classList.add('show');
            });
        <?php endif; ?>
    </script>
</body>
</html>
