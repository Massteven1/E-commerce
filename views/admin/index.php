<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .container { width: 80%; margin: 0 auto; padding: 20px; }
        header { background: #333; color: white; padding: 10px 0; }
        .logo { display: flex; align-items: center; }
        .logo-circle { width: 40px; height: 40px; background: #007bff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; }
        nav ul { list-style: none; display: flex; gap: 20px; }
        nav a { color: white; text-decoration: none; }
        .banner { background: #f4f4f4; padding: 20px 0; }
        .checkout-section { margin: 20px 0; }
        .form-row { display: flex; flex-direction: column; gap: 10px; }
        input, textarea, select { padding: 10px; font-size: 16px; }
        .btn-primary { background: #007bff; color: white; padding: 10px; border: none; cursor: pointer; }
        .products-grid { list-style: none; padding: 0; display: grid; gap: 20px; }
        .product-card { border: 1px solid #ddd; padding: 10px; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-circle">V</div>
                <span style="margin-left: 10px; font-weight: 600;">Video Admin</span>
            </div>
            <nav>
                <ul>
                    <li><a href="courses.php?controller=playlist&action=index">Inicio</a></li>
                    <li><a href="#">Configuración</a></li>
                    <li class="logout"><i class="fas fa-sign-out-alt"></i></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="banner">
        <div class="container">
            <h1>Panel de Administración</h1>
            <p>Gestiona tus listas de reproducción y videos.</p>
        </div>
    </div>

    <div class="container">
        <!-- Crear Lista de Reproducción -->
        <div class="checkout-section">
            <h2>Crear Lista de Reproducción</h2>
            <form action="courses.php?controller=playlist&action=create" method="post" enctype="multipart/form-data" class="form-row">
                <input type="text" name="name" placeholder="Nombre de la lista" required>
                <textarea name="description" placeholder="Descripción"></textarea>
                <input type="file" name="cover_image" accept="image/jpeg" required>
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
                <select name="playlist_id">
                    <option value="">Seleccionar Lista</option>
                    <?php foreach ($playlists as $playlist): ?>
                        <option value="<?php echo $playlist['id']; ?>"><?php echo htmlspecialchars($playlist['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary">Subir Video</button>
            </form>
        </div>

        <!-- Mostrar Listas de Reproducción -->
        <div class="checkout-section">
            <h2>Listas de Reproducción</h2>
            <?php if (empty($playlists)): ?>
                <p>No hay listas de reproducción creadas.</p>
            <?php else: ?>
                <ul class="products-grid">
                    <?php foreach ($playlists as $playlist): ?>
                        <li class="product-card">
                            <a href="courses.php?controller=video&action=view_playlist&id=<?php echo $playlist['id']; ?>">
                                <?php echo htmlspecialchars($playlist['name']); ?>
                            </a>
                            - <?php echo htmlspecialchars($playlist['description']); ?>
                            <?php if (!empty($playlist['cover_image'])): ?>
                                <br><img src="/E-commerce/<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="Portada" style="max-width: 100px;">
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="back-to-top">
        <a href="#"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>