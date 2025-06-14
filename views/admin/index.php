<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Cursos</title>
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
            <h1>Gestión de Cursos</h1>
            <p>Crea y administra tus listas de reproducción y videos.</p>
        </div>
    </div>

    <div class="container">
        <div class="form-grid">
            <!-- Crear Lista de Reproducción -->
            <div class="checkout-section">
                <h2>Crear Lista de Reproducción</h2>
                <form action="courses.php?controller=playlist&action=create" method="post" enctype="multipart/form-data" class="form-row">
                    <input type="text" name="name" placeholder="Nombre de la lista" required>
                    <textarea name="description" placeholder="Descripción"></textarea>
                    <input type="number" name="price" placeholder="Precio del curso" step="0.01" min="0" required>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="cover_image">Imagen de Portada de la Lista (JPG/JPEG)</label>
                        <input type="file" id="cover_image" name="cover_image" accept="image/jpeg" required>
                        <p style="font-size: 0.9em; color: var(--dark-gray); margin-top: 5px;">Sube una imagen (JPG/JPEG) que representará la lista de reproducción.</p>
                    </div>

                    <button type="submit" class="btn-primary">Crear Lista</button>
                </form>
            </div>

            <!-- Subir Video y Asignar a Lista -->
            <div class="checkout-section">
                <h2>Subir Video</h2>
                <form action="courses.php?controller=video&action=upload" method="post" enctype="multipart/form-data" class="form-row">
                    <input type="file" name="video" accept="video/mp4" required>
                    <input type="text" name="title" placeholder="Título del Video" required>
                    <textarea name="description" placeholder="Descripción"></textarea>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="thumbnail_image">Miniatura del Video (Imagen de Portada)</label>
                        <input type="file" id="thumbnail_image" name="thumbnail_image" accept="image/jpeg,image/png" required>
                        <p style="font-size: 0.9em; color: var(--dark-gray); margin-top: 5px;">Sube una imagen (JPG, JPEG, PNG) que se mostrará como portada del video.</p>
                    </div>

                    <select name="playlist_id" required>
                        <option value="">Seleccionar Lista</option>
                        <?php if (!empty($playlists)): ?>
                            <?php foreach ($playlists as $playlist): ?>
                                <option value="<?php echo htmlspecialchars($playlist['id']); ?>"><?php echo htmlspecialchars($playlist['name']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <button type="submit" class="btn-primary">Subir Video</button>
                </form>
            </div>
        </div>

        <!-- Mostrar Listas de Reproducción -->
        <div class="checkout-section">
            <h2>Listas de Reproducción</h2>
            <?php if (empty($playlists)): ?>
                <p style="text-align: center; color: var(--dark-gray);">No hay listas de reproducción creadas.</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($playlists as $playlist): ?>
                        <div class="product-card">
                            <div class="product-tumb">
                                <?php if (!empty($playlist['cover_image'])): ?>
                                    <!-- Corregir la ruta: usar ruta relativa desde la vista -->
                                    <img src="../../<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="<?php echo htmlspecialchars($playlist['name']); ?>">
                                <?php else: ?>
                                    <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                                <?php endif; ?>
                            </div>
                            <div class="product-details">
                                <span class="product-catagory">Playlist</span>
                                <h4><a href="courses.php?controller=video&action=view_playlist&id=<?php echo htmlspecialchars($playlist['id']); ?>"><?php echo htmlspecialchars($playlist['name']); ?></a></h4>
                                <p><?php echo htmlspecialchars($playlist['description'] ?: 'Sin descripción'); ?></p>
                                <div class="product-bottom-details">
                                    <div class="product-price">$<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?></div>
                                    <div class="product-links">
                                        <a href="courses.php?controller=playlist&action=edit&id=<?php echo htmlspecialchars($playlist['id']); ?>" title="Editar"><i class="fa fa-edit"></i></a>
                                        <a href="courses.php?controller=video&action=view_playlist&id=<?php echo htmlspecialchars($playlist['id']); ?>" title="Ver Videos"><i class="fa fa-eye"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="back-to-top">
        <a href="#"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>
