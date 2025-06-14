<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Lista de Reproducción: <?php echo htmlspecialchars($playlist['name']); ?></title>
    <link rel="stylesheet" href="../../public/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-circle" style="background-color: var(--primary-color);">V</div>
                <span style="margin-left: 10px; font-weight: 600;">Video Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="courses.php?controller=admin&action=dashboard">Dashboard</a></li>
                    <li><a href="courses.php?controller=playlist&action=index">Cursos</a></li>
                    <li><a href="#">Configuración</a></li>
                    <li class="logout"><i class="fas fa-sign-out-alt"></i></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="banner">
        <div class="container">
            <h1>Editar Lista de Reproducción</h1>
            <p>Modifica los detalles de la lista de reproducción "<?php echo htmlspecialchars($playlist['name']); ?>".</p>
        </div>
    </div>

    <div class="container">
        <div class="checkout-section">
            <h2>Formulario de Edición</h2>
            <form action="courses.php?controller=playlist&action=update" method="post" enctype="multipart/form-data" class="form-row">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($playlist['id']); ?>">

                <input type="text" name="name" placeholder="Nombre de la lista" value="<?php echo htmlspecialchars($playlist['name']); ?>" required>
                <textarea name="description" placeholder="Descripción"><?php echo htmlspecialchars($playlist['description']); ?></textarea>
                <input type="number" name="price" placeholder="Precio del curso" step="0.01" min="0" value="<?php echo htmlspecialchars($playlist['price']); ?>" required>

                <!-- Nuevo: Etiqueta y aclaración para la imagen de portada de la lista -->
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="cover_image">Imagen de Portada de la Lista (JPG/JPEG)</label>
                    <?php if (!empty($playlist['cover_image'])): ?>
                        <img id="current_cover_image" src="/<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="Portada actual" style="max-width: 200px; height: auto; display: block; margin-top: 10px; border-radius: 5px;">
                    <?php else: ?>
                        <p>No hay imagen de portada actual.</p>
                    <?php endif; ?>
                    <input type="file" id="cover_image" name="cover_image" accept="image/jpeg">
                    <p style="font-size: 0.9em; color: var(--dark-gray); margin-top: 5px;">Sube una nueva imagen (JPG/JPEG) para la portada de la lista. Deja vacío para mantener la actual.</p>
                </div>

                <button type="submit" class="btn-primary" style="grid-column: 1 / -1;">Actualizar Lista</button>
            </form>
        </div>
    </div>

    <div class="back-to-top">
        <a href="courses.php?controller=playlist&action=index"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>
