<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - El Profesor Hernán</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/course-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="img/logo-profe-hernan.png" alt="El Profesor Hernán" style="height: 40px;">
                <span>El Profesor Hernán</span>
            </div>
            
            <nav class="nav">
                <ul>
                    <li><a href="home.php">Inicio</a></li>
                    <li><a href="home.php">Cursos</a></li>
                    <li><a href="home.php?controller=cart&action=view">
                        <i class="fas fa-shopping-cart"></i>
                        Carrito
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a></li>
                </ul>
            </nav>
            
            <div class="auth-links">
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <section class="cart-section">
        <div class="container">
            <h1 class="cart-title">Tu Carrito de Compras</h1>
            
            <div class="cart-container">
                <div class="cart-items">
                    <div class="cart-header">
                        <div class="cart-header-product">Producto</div>
                        <div class="cart-header-price">Precio</div>
                        <div class="cart-header-total">Acciones</div>
                    </div>
                    
                    <?php if (!empty($cart_items)): ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-product">
                                    <div class="cart-item-image">
                                        <?php if (!empty($item['cover_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['cover_image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                        <?php else: ?>
                                            <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-item-details">
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p>Acceso Digital Completo</p>
                                    </div>
                                </div>
                                <div class="cart-item-price">$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></div>
                                <div class="cart-item-actions">
                                    <a href="home.php?controller=cart&action=remove&id=<?php echo htmlspecialchars($item['id']); ?>" class="remove-item" onclick="return confirm('¿Estás seguro de que quieres eliminar este curso del carrito?');"><i class="fas fa-trash"></i> Eliminar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 20px; color: var(--dark-gray); grid-column: 1 / -1;">Tu carrito está vacío.</p>
                    <?php endif; ?>
                </div>
                
                <div class="cart-summary">
                    <h2>Resumen del Pedido</h2>
                    
                    <div class="summary-row">
                        <span>Subtotal (<?php echo count($cart_items); ?> curso<?php echo count($cart_items) != 1 ? 's' : ''; ?>)</span>
                        <span>$<?php echo htmlspecialchars(number_format($totals['subtotal'], 2)); ?></span>
                    </div>
                    
                    <div class="summary-row discount">
                        <span>Descuento 
                            <?php if ($totals['promo_code_applied']): ?>
                                (Código: <?php echo htmlspecialchars($totals['promo_code_applied']); ?>)
                            <?php endif; ?>
                        </span>
                        <span>-$<?php echo htmlspecialchars(number_format($totals['discount'], 2)); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Envío</span>
                        <span>$0.00</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Impuesto</span>
                        <span>$<?php echo htmlspecialchars(number_format($totals['tax'], 2)); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>$<?php echo htmlspecialchars(number_format($totals['total'], 2)); ?></span>
                    </div>
                    
                    <form action="home.php?controller=cart&action=apply_promo" method="post" class="promo-code">
                        <input type="text" name="promo_code" placeholder="Código Promocional" value="<?php echo htmlspecialchars($_SESSION['promo_code_applied'] ?? ''); ?>">
                        <button type="submit">Aplicar</button>
                    </form>
                    <?php if (!empty($promo_message)): ?>
                        <div class="promo-message <?php echo (strpos($promo_message, 'éxito') !== false) ? 'success' : 'error'; ?>">
                            <?php echo $promo_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($cart_items)): ?>
                        <a href="home.php?controller=cart&action=checkout" class="btn-primary checkout-btn">Proceder al Pago</a>
                    <?php endif; ?>
                    <a href="home.php" class="continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continuar Comprando
                    </a>
                </div>
            </div>
            
            <div class="recommended-courses">
                <h2>También te podría gustar</h2>
                <div class="courses-grid">
                    <?php if (!empty($recommended_playlists)): ?>
                        <?php foreach ($recommended_playlists as $rec_playlist): ?>
                            <div class="course-card">
                                <div class="level-badge neon-glow orange" style="background-color: var(--orange-color); color: white;">
                                    <?php 
                                        preg_match('/(A|B|C)\d/', $rec_playlist['name'], $matches);
                                        echo $matches[0] ?? 'A1';
                                    ?>
                                </div>
                                <div class="course-icon"><i class="fas fa-book"></i></div>
                                <h3 class="course-title"><?php echo htmlspecialchars($rec_playlist['name']); ?></h3>
                                <p class="course-subtitle"><?php echo htmlspecialchars($rec_playlist['description'] ?: 'Curso completo de inglés'); ?></p>
                                <p class="course-price">$<?php echo htmlspecialchars(number_format($rec_playlist['price'], 2)); ?></p>
                                <a href="home.php?controller=cart&action=add&id=<?php echo htmlspecialchars($rec_playlist['id']); ?>" class="btn-primary">Añadir al Carrito</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 El Profesor Hernán. Todos los derechos reservados.</p>
            <div class="footer-links">
                <a href="home.php">Inicio</a>
                <a href="home.php">Cursos</a>
                <a href="home.php?controller=cart&action=view">Carrito</a>
            </div>
            <p>Aprende inglés con los mejores cursos online</p>
        </div>
    </footer>

    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="auth/firebase-config.js"></script>
    <script src="auth/auth.js"></script>
</body>
</html>
