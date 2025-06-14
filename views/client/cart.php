<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Las variables $cart_items, $totals, $recommended_playlists y $promo_message
// se pasan desde el CartController::view()
?>

<!-- Cart Section -->
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
                                <a href="index.php?controller=cart&action=remove&id=<?php echo htmlspecialchars($item['id']); ?>" class="remove-item" onclick="return confirm('¿Estás seguro de que quieres eliminar este curso del carrito?');"><i class="fas fa-trash"></i> Eliminar</a>
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
                
                <form action="index.php?controller=cart&action=apply_promo" method="post" class="promo-code">
                    <input type="text" name="promo_code" placeholder="Código Promocional" value="<?php echo htmlspecialchars($_SESSION['promo_code_applied'] ?? ''); ?>">
                    <button type="submit">Aplicar</button>
                </form>
                <?php if (!empty($promo_message)): ?>
                    <div class="promo-message <?php echo (strpos($promo_message, 'éxito') !== false) ? 'success' : 'error'; ?>">
                        <?php echo $promo_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($cart_items)): ?>
                    <a href="index.php?controller=cart&action=checkout" class="btn-primary checkout-btn">Proceder al Pago</a>
                <?php endif; ?>
                <a href="index.php" class="continue-shopping">
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
                                    // Intenta extraer el nivel (A1, B2, etc.) del nombre si sigue un patrón
                                    preg_match('/(A|B|C)\d/', $rec_playlist['name'], $matches);
                                    echo htmlspecialchars($matches[0] ?? 'N/A');
                                ?>
                            </div>
                            <div class="course-icon"><i class="fas fa-book"></i></div>
                            <h3 class="course-title"><?php echo htmlspecialchars($rec_playlist['name']); ?></h3>
                            <p class="course-subtitle"><?php echo htmlspecialchars($rec_playlist['description'] ?: 'Sin descripción'); ?></p>
                            <p class="course-price">$<?php echo htmlspecialchars(number_format($rec_playlist['price'], 2)); ?></p>
                            <?php if (!isset($_SESSION['cart'][$rec_playlist['id']])): ?>
                                <a href="index.php?controller=cart&action=add&id=<?php echo htmlspecialchars($rec_playlist['id']); ?>" class="add-to-cart-btn">Añadir al Carrito</a>
                            <?php else: ?>
                                <button class="add-to-cart-btn" disabled style="opacity: 0.6;">Ya en el Carrito</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--dark-gray); grid-column: 1 / -1;">No hay recomendaciones en este momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
    /* Estilos para mensajes de promo code */
    .promo-message {
        margin-top: 10px;
        padding: 10px;
        border-radius: 5px;
        font-size: 0.9em;
        text-align: center;
    }
    .promo-message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .promo-message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
