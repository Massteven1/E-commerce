<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir dependencias
require_once __DIR__ . '/../../controllers/CartController.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

use Controllers\CartController;
use Controllers\AuthController;

// Verificar autenticación
if (!AuthController::isAuthenticated()) {
    header('Location: ../../login.php');
    exit();
}

// Inicializar controlador del carrito
$cartController = new CartController();
$cart_items = $cartController->getCartItems();
$totals = $cartController->calculateTotals($cart_items);
$currentUser = AuthController::getCurrentUser();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - El Profesor Hernán</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8a56e2;
            --purple-color: #6c5ce7;
            --teal-color: #56e2c6;
            --orange-color: #fd79a8;
            --red-color: #ff5a5a;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --dark-gray: #6c757d;
            --text-color: #2d3748;
            --text-muted: #718096;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --border-radius-md: 8px;
            --border-radius-lg: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .cart-items {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 120px;
            height: 80px;
            border-radius: var(--border-radius-md);
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            flex: 1;
        }

        .item-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .item-level {
            background: var(--primary-color);
            color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .item-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-remove {
            background: var(--red-color);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius-md);
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-remove:hover {
            background: #e53e3e;
        }

        .cart-summary {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            height: fit-content;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .btn-checkout {
            width: 100%;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 1rem;
            border-radius: var(--border-radius-md);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
        }

        .btn-checkout:hover {
            background: var(--purple-color);
            transform: translateY(-2px);
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .btn-continue {
            background: var(--teal-color);
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: var(--border-radius-md);
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-top: 1rem;
            transition: var(--transition);
        }

        .btn-continue:hover {
            background: #48b090;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }

            .cart-item {
                flex-direction: column;
            }

            .item-image {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart"></i> Mi Carrito</h1>
            <p>Revisa tus cursos seleccionados</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Tu carrito está vacío</h3>
                <p>¡Explora nuestros cursos y comienza tu aprendizaje!</p>
                <a href="all-courses.php" class="btn-continue">
                    <i class="fas fa-book"></i> Ver Cursos
                </a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <h3>Cursos en tu carrito (<?php echo count($cart_items); ?>)</h3>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="<?php echo !empty($item['cover_image']) ? '../../' . $item['cover_image'] : 'https://via.placeholder.com/120x80/8a56e2/ffffff?text=Curso'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            
                            <div class="item-details">
                                <h4 class="item-title"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <?php if (!empty($item['level'])): ?>
                                    <span class="item-level"><?php echo htmlspecialchars($item['level']); ?></span>
                                <?php endif; ?>
                                <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                            
                            <div class="item-actions">
                                <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="btn-remove">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3>Resumen del Pedido</h3>
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($totals['subtotal'], 2); ?></span>
                    </div>
                    
                    <?php if ($totals['discount'] > 0): ?>
                        <div class="summary-row">
                            <span>Descuento:</span>
                            <span>-$<?php echo number_format($totals['discount'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-row">
                        <span>Impuestos:</span>
                        <span>$<?php echo number_format($totals['tax'], 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Total:</span>
                        <span>$<?php echo number_format($totals['total'], 2); ?></span>
                    </div>
                    
                    <button onclick="proceedToCheckout()" class="btn-checkout">
                        <i class="fas fa-credit-card"></i> Proceder al Pago
                    </button>
                    
                    <a href="all-courses.php" class="btn-continue" style="text-align: center; margin-top: 1rem;">
                        <i class="fas fa-arrow-left"></i> Seguir Comprando
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function removeFromCart(courseId) {
            if (confirm('¿Estás seguro de que quieres eliminar este curso del carrito?')) {
                fetch('../../controllers/CartController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&id=${courseId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert(data.message || 'Error al eliminar el curso');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el curso');
                });
            }
        }

        function proceedToCheckout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>
