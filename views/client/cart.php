<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/CartController.php';

use Controllers\AuthController;
use Controllers\CartController;

// Las variables $cart_items y $totals se pasan desde el CartController
// Si se accede directamente, inicializarlas
$cartController = new CartController();
$cart_items = $cartController->getCartItems();
$totals = $cartController->calculateTotals($cart_items);

// Obtener mensaje flash si existe
$flashMessage = AuthController::getFlashMessage();
$promoMessage = $_SESSION['promo_message'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Profesor Hernán - Carrito de Compras</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="../../public/css/cart-improvements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="../../img/logo-profe-hernan.png" alt="El Profesor Hernán" style="height: 40px;">
                <span>El Profesor Hernán</span>
            </div>
            
            <nav class="nav">
                <ul>
                    <li><a href="home.php">Inicio</a></li>
                    <li><a href="all-courses.php">Cursos</a></li>
                    <li><a href="cart.php" class="active">
                        <i class="fas fa-shopping-cart"></i>
                        Carrito
                        <?php if (count($cart_items) > 0): ?>
                            <span class="cart-count"><?php echo count($cart_items); ?></span>
                        <?php endif; ?>
                    </a></li>
                </ul>
            </nav>
            
            <div class="auth-links">
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']); ?></span>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="../admin/index.php?controller=admin&action=dashboard" class="btn-admin">Panel Admin</a>
                    <?php endif; ?>
                    <a href="purchase-history.php" class="btn-history">Mis Cursos</a>
                    <a href="../../logout.php" class="btn-logout">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="../../login.php" class="btn-login">Iniciar Sesión</a>
                    <a href="../../signup.php" class="btn-signup">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <h1 class="cart-title">Tu Carrito de Compras</h1>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?>">
                    <i class="fas fa-<?php echo $flashMessage['type'] === 'error' ? 'exclamation-triangle' : ($flashMessage['type'] === 'success' ? 'check-circle' : 'info-circle'); ?>"></i>
                    <?php echo $flashMessage['message']; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
                <!-- Empty Cart -->
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2>Tu carrito está vacío</h2>
                    <p>¡Explora nuestros cursos y comienza tu aprendizaje de inglés!</p>
                    <a href="all-courses.php" class="btn-primary">
                        <i class="fas fa-book"></i>
                        Ver Cursos Disponibles
                    </a>
                </div>
            <?php else: ?>
                <!-- Cart with Items -->
                <div class="cart-container">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <div class="cart-header">
                            <div class="cart-header-product">Curso</div>
                            <div class="cart-header-price">Precio</div>
                            <div class="cart-header-total">Acciones</div>
                        </div>
                        
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-product">
                                    <div class="cart-item-image">
                                        <?php if (!empty($item['cover_image'])): ?>
                                            <img src="../../<?php echo htmlspecialchars($item['cover_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <?php else: ?>
                                            <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-item-details">
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p>Acceso Digital Completo</p>
                                        <?php if (!empty($item['level'])): ?>
                                            <span class="course-level"><?php echo htmlspecialchars($item['level']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="cart-item-price">
                                    $<?php echo htmlspecialchars(number_format($item['price'], 2)); ?>
                                </div>
                                <div class="cart-item-actions">
                                    <a href="../../controllers/CartController.php?action=remove&id=<?php echo $item['id']; ?>" 
                                       class="remove-item" 
                                       onclick="return confirm('¿Estás seguro de que quieres eliminar este curso del carrito?')">
                                        <i class="fas fa-trash"></i>
                                        Eliminar
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <h2>Resumen del Pedido</h2>
                        
                        <!-- Promo Code Section -->
                        <div class="promo-section">
                            <h3>Código Promocional</h3>
                            <?php if ($totals['promo_code_applied']): ?>
                                <div class="promo-applied">
                                    <div class="promo-info">
                                        <i class="fas fa-tag"></i>
                                        <span>Código aplicado: <strong><?php echo htmlspecialchars($totals['promo_code_applied']); ?></strong></span>
                                    </div>
                                    <a href="../../controllers/CartController.php?action=remove_promo" class="remove-promo">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="../../controllers/CartController.php?action=apply_promo" class="promo-form">
                                    <div class="promo-code">
                                        <input type="text" name="promo_code" placeholder="Ingresa tu código" maxlength="20">
                                        <button type="submit">Aplicar</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($promoMessage): ?>
                                <div class="promo-message <?php echo strpos($promoMessage, 'éxito') !== false ? 'success' : 'error'; ?>">
                                    <?php echo htmlspecialchars($promoMessage); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Totals -->
                        <div class="summary-totals">
                            <div class="summary-row">
                                <span>Subtotal (<?php echo count($cart_items); ?> curso<?php echo count($cart_items) > 1 ? 's' : ''; ?>)</span>
                                <span>$<?php echo htmlspecialchars(number_format($totals['subtotal'], 2)); ?></span>
                            </div>
                            
                            <?php if ($totals['discount'] > 0): ?>
                                <div class="summary-row discount">
                                    <span>Descuento</span>
                                    <span>-$<?php echo htmlspecialchars(number_format($totals['discount'], 2)); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="summary-row">
                                <span>Impuesto (7%)</span>
                                <span>$<?php echo htmlspecialchars(number_format($totals['tax'], 2)); ?></span>
                            </div>
                            
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>$<?php echo htmlspecialchars(number_format($totals['total'], 2)); ?></span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <?php if (AuthController::isAuthenticated()): ?>
                            <a href="../../controllers/PaymentController.php?action=checkout" class="checkout-btn">
                                <i class="fas fa-credit-card"></i>
                                Proceder al Pago
                            </a>
                        <?php else: ?>
                            <div class="login-required">
                                <p><i class="fas fa-info-circle"></i> Debes iniciar sesión para continuar</p>
                                <a href="../../login.php?redirect=cart" class="checkout-btn">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Iniciar Sesión
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Additional Actions -->
                        <div class="cart-actions">
                            <a href="../../controllers/CartController.php?action=clear" 
                               class="clear-cart"
                               onclick="return confirm('¿Estás seguro de que quieres vaciar el carrito?')">
                                <i class="fas fa-trash-alt"></i>
                                Vaciar Carrito
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Continue Shopping -->
                <div class="continue-shopping-section">
                    <a href="all-courses.php" class="continue-shopping">
                        <i class="fas fa-arrow-left"></i>
                        Seguir Comprando
                    </a>
                </div>

                <!-- Security Features -->
                <div class="security-features">
                    <div class="security-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Pago 100% Seguro</span>
                    </div>
                    <div class="security-item">
                        <i class="fas fa-medal"></i>
                        <span>Garantía de 30 días</span>
                    </div>
                    <div class="security-item">
                        <i class="fas fa-headset"></i>
                        <span>Soporte 24/7</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 El Profesor Hernán. Todos los derechos reservados.</p>
            <div class="footer-links">
                <a href="home.php">Inicio</a>
                <a href="all-courses.php">Cursos</a>
                <a href="cart.php">Carrito</a>
            </div>
            <p>Aprende inglés con los mejores cursos online</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="../../auth/firebase-config.js"></script>
    <script src="../../auth/auth.js"></script>
</body>
</html>
