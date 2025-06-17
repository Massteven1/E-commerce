<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir el controlador de pagos para obtener la clave publicable
require_once __DIR__ . '/../../controllers/PaymentController.php';
require_once __DIR__ . '/../../controllers/AuthController.php'; // Para mensajes flash

$paymentController = new PaymentController();
$stripePublishableKey = $paymentController->getPublishableKey();

// Las variables $cart_items y $totals se pasan desde el CartController::checkout()
// Asegúrate de que $cart_items y $totals estén definidos. Si no, redirige.
if (empty($cart_items) || empty($totals)) {
    header('Location: cart.php');
    exit();
}

// Obtener mensaje flash si existe
$flashMessage = AuthController::getFlashMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Profesor Hernán - Finalizar Compra</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="../../public/css/course-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        /* Estilos adicionales para mejorar la experiencia de usuario */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .btn-checkout.processing {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        #card-element {
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            background-color: #fff;
        }
        
        #payment-status {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        
        #payment-status.error {
            display: block;
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        
        #payment-status.success {
            display: block;
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
    </style>
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
                    <li><a href="home.php">Cursos</a></li>
                    <li><a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        Carrito
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
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
                    <a href="../../logout.php" class="btn-logout">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="../../login.php" class="btn-login">Iniciar Sesión</a>
                    <a href="../../signup.php" class="btn-signup">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

<!-- Checkout Section -->
<section class="checkout">
    <div class="container">
        <h1 class="checkout-title">Finalizar Compra</h1>
        
        <?php if ($flashMessage): ?>
            <div class="promo-message <?php echo $flashMessage['type']; ?>" style="margin-bottom: 20px;">
                <?php echo $flashMessage['message']; ?>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            <!-- Formulario de Checkout -->
            <div class="checkout-form">
                <!-- Información Personal -->
                <div class="checkout-section">
                    <h2>Información Personal</h2>
                    <form id="payment-form" action="../../index.php?controller=payment&action=processPayment" method="post">
                        <div class="form-row">
                            <input type="text" name="first_name" placeholder="Nombre" required value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                            <input type="text" name="last_name" placeholder="Apellido" required value="<?php echo htmlspecialchars($_SESSION['user_last_name'] ?? ''); ?>">
                        </div>
                        <div class="form-row">
                            <input type="email" name="email" placeholder="Correo Electrónico" required value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                            <input type="tel" name="phone" placeholder="Teléfono" required>
                        </div>
                    </form>
                </div>

                <!-- Información de Facturación -->
                <div class="checkout-section">
                    <h2>Información de Facturación</h2>
                    <div class="form-row">
                        <input type="text" name="address" form="payment-form" placeholder="Dirección" required>
                        <input type="text" name="city" form="payment-form" placeholder="Ciudad" required>
                    </div>
                    <div class="form-row">
                        <input type="text" name="state" form="payment-form" placeholder="Estado/Provincia" required>
                        <input type="text" name="zip_code" form="payment-form" placeholder="Código Postal" required>
                    </div>
                    <div class="form-row">
                        <select name="country" form="payment-form" required>
                            <option value="">Seleccionar País</option>
                            <option value="CO">Colombia</option>
                            <option value="MX">México</option>
                            <option value="AR">Argentina</option>
                            <option value="ES">España</option>
                            <option value="US">Estados Unidos</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                </div>

                <!-- Método de Pago - Stripe Elements -->
                <div class="checkout-section">
                    <h2>Método de Pago</h2>
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="credit_card" checked>
                            <span class="radio-custom"></span>
                            <div class="payment-label">
                                <i class="fas fa-credit-card"></i>
                                Tarjeta de Crédito/Débito (Stripe)
                            </div>
                        </label>
                    </div>

                    <!-- Stripe Elements Form -->
                    <div class="credit-card-form" id="stripe-card-form">
                        <div id="card-element">
                            <!-- Un elemento de Stripe se insertará aquí. -->
                        </div>
                        <!-- Usado para mostrar errores de Stripe.js -->
                        <div id="card-errors" role="alert" style="color: var(--red-color); margin-top: 10px;"></div>
                        <div id="payment-status"></div>
                    </div>
                </div>
            </div>

            <!-- Resumen del Pedido -->
            <div class="order-summary">
                <div class="summary-header">
                    <h2>Resumen del Pedido</h2>
                    <span class="items-count"><?php echo count($cart_items); ?></span>
                </div>
                
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <?php if (!empty($item['cover_image'])): ?>
                                    <img src="../../<?php echo htmlspecialchars($item['cover_image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p>Acceso Digital</p>
                            </div>
                            <div class="item-price">$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>$<?php echo htmlspecialchars(number_format($totals['subtotal'], 2)); ?></span>
                    </div>
                    
                    <?php if ($totals['discount'] > 0): ?>
                        <div class="summary-row">
                            <span>Descuento 
                                <?php if ($totals['promo_code_applied']): ?>
                                    (<?php echo htmlspecialchars($totals['promo_code_applied']); ?>)
                                <?php endif; ?>
                            </span>
                            <span style="color: var(--orange-color);">-$<?php echo htmlspecialchars(number_format($totals['discount'], 2)); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-row">
                        <span>Impuesto</span>
                        <span>$<?php echo htmlspecialchars(number_format($totals['tax'], 2)); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>$<?php echo htmlspecialchars(number_format($totals['total'], 2)); ?></span>
                    </div>
                </div>

                <button type="button" id="submit-button" class="btn-checkout">
                    <i class="fas fa-lock"></i>
                    Completar Compra
                </button>

                <div class="secure-checkout">
                    <i class="fas fa-shield-alt"></i>
                    <span>Pago 100% Seguro</span>
                </div>

                <div class="payment-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-paypal"></i>
                    <i class="fab fa-cc-amex"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Back to Shopping Link -->
<div class="back-to-shopping">
    <a href="cart.php">
        <i class="fas fa-arrow-left"></i>
        Volver al Carrito
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');
    const cardErrors = document.getElementById('card-errors');
    const paymentStatus = document.getElementById('payment-status');
    
    // Verificar si Stripe está disponible
    if (typeof Stripe === 'undefined') {
        showError('No se pudo cargar el sistema de pagos. Por favor, recarga la página o contacta al administrador.');
        submitButton.disabled = true;
        return;
    }
    
    // Inicializar Stripe con la clave publicable
    const stripe = Stripe('<?php echo $stripePublishableKey; ?>');
    const elements = stripe.elements();
    
    // Crear el elemento de tarjeta
    const cardElement = elements.create('card', {
        style: {
            base: {
                iconColor: '#8a56e2',
                color: '#333',
                fontWeight: '500',
                fontFamily: 'Poppins, sans-serif',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4',
                },
            },
            invalid: {
                iconColor: '#ff5a5a',
                color: '#ff5a5a',
            },
        },
    });
    
    // Montar el elemento de tarjeta en el DOM
    cardElement.mount('#card-element');
    
    // Manejar errores de validación en tiempo real
    cardElement.on('change', function(event) {
        if (event.error) {
            showError(event.error.message);
        } else {
            clearError();
        }
    });
    
    // Manejar el clic en el botón de envío
    submitButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Validar el formulario antes de procesar el pago
        if (!validateForm()) {
            return;
        }
        
        // Mostrar estado de carga
        setLoading(true);
        
        // Crear token con Stripe
        stripe.createToken(cardElement).then(function(result) {
            if (result.error) {
                // Mostrar error y habilitar el botón nuevamente
                showError(result.error.message);
                setLoading(false);
            } else {
                // Añadir el token al formulario
                const hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', result.token.id);
                form.appendChild(hiddenInput);
                
                // Enviar el formulario
                form.submit();
            }
        }).catch(function(error) {
            showError('Ocurrió un error al procesar tu pago. Por favor, intenta de nuevo.');
            console.error('Error de Stripe:', error);
            setLoading(false);
        });
    });
    
    // Función para validar el formulario
    function validateForm() {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#ff5a5a';
                isValid = false;
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            showError('Por favor, completa todos los campos requeridos.');
        }
        
        return isValid;
    }
    
    // Función para mostrar errores
    function showError(message) {
        cardErrors.textContent = message;
        paymentStatus.textContent = message;
        paymentStatus.className = 'error';
        paymentStatus.style.display = 'block';
    }
    
    // Función para limpiar errores
    function clearError() {
        cardErrors.textContent = '';
        paymentStatus.textContent = '';
        paymentStatus.style.display = 'none';
    }
    
    // Función para mostrar/ocultar estado de carga
    function setLoading(isLoading) {
        if (isLoading) {
            submitButton.disabled = true;
            submitButton.classList.add('processing');
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            document.querySelector('.checkout-form').classList.add('loading');
        } else {
            submitButton.disabled = false;
            submitButton.classList.remove('processing');
            submitButton.innerHTML = '<i class="fas fa-lock"></i> Completar Compra';
            document.querySelector('.checkout-form').classList.remove('loading');
        }
    }
    
    // Añadir validación en tiempo real para los campos requeridos
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = '#ff5a5a';
            } else {
                this.style.borderColor = '';
            }
        });
        
        field.addEventListener('input', function() {
            if (this.value.trim()) {
                this.style.borderColor = '';
            }
        });
    });
});
</script>

<!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 El Profesor Hernán. Todos los derechos reservados.</p>
            <div class="footer-links">
                <a href="home.php">Inicio</a>
                <a href="home.php">Cursos</a>
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
