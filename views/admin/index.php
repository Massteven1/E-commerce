<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <!-- <link rel="stylesheet" href="/E-commerce/public/css/styles.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-circle" style="background-color: var(--primary-color);">V</div>
                <span style="margin-left: 10px; font-weight: 600;">Video Admin</span>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Buscar listas o videos...">
                <i class="fas fa-search"></i>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php?controller=playlist&action=index">Inicio</a></li>
                    <li><a href="#">Configuración</a></li>
                    <li class="logout"><i class="fas fa-sign-out-alt"></i></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Banner -->
    <div class="banner">
        <div class="container">
            <div class="banner-content">
                <h1>Panel de Administración</h1>
                <p>Gestiona tus listas de reproducción y videos.</p>
            </div>
        </div>
    </div>

    <!-- Contenido del Panel -->
    <div class="container">
        <!-- Crear Lista de Reproducción -->
        <div class="checkout-section">
            <h2>Crear Lista de Reproducción</h2>
            <form action="index.php?controller=playlist&action=create" method="post" class="form-row">
                <input type="text" name="name" placeholder="Nombre de la lista" required>
                <textarea name="description" placeholder="Descripción"></textarea>
                <button type="submit" class="btn-primary">Crear Lista</button>
            </form>
        </div>

        <!-- Subir Video y Asignar a Lista -->
        <div class="checkout-section">
            <h2>Subir Video</h2>
            <form action="index.php?controller=video&action=upload" method="post" enctype="multipart/form-data" class="form-row">
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
                            <a href="index.php?controller=video&action=view_playlist&id=<?php echo $playlist['id']; ?>">
                                <?php echo htmlspecialchars($playlist['name']); ?>
                            </a>
                            - <?php echo htmlspecialchars($playlist['description']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back to Top Button -->
    <div class="back-to-top">
        <a href="#"><i class="fas fa-arrow-up"></i></a>
    </div>
</body>
</html>