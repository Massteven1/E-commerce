<?php
require_once __DIR__ . '/config/config.php';

// Redirigir si no está autenticado
if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = 'Carrito de Compras - ' . SITE_NAME;
$user = getCurrentUser();
$userDisplayName = $user['displayName'] ?? $user['email'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-circle">
                    <a href="/"><span>EH</span></a>
                </div>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Buscar cursos..." id="searchInput">
                <i class="fas fa-search"></i>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="courses.php">Cursos</a></li>
                    <li><a href="#contact">Contacto</a></li>
                    <li><a href="profile.php"><?php echo htmlspecialchars($userDisplayName); ?></a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/">Admin</a></li>
                    <?php endif; ?>
                </ul>
                <div class="cart" id="cartIcon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge" id="cartBadge">0</span>
                </div>
                <div class="logout" id="logoutBtn">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <div class="cart-header">
                <h1><i class="fas fa-shopping-cart"></i> Mi Carrito</h1>
                <p>Revisa y gestiona los cursos en tu carrito</p>
            </div>

            <!-- Cart Content -->
            <div class="cart-content">
                <!-- Cart Items -->
                <div class="cart-items" id="cartItems">
                    <div class="loading-cart">
                        <div class="spinner"></div>
                        <p>Cargando carrito...</p>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary" id="cartSummary">
                    <div class="summary-card">
                        <h3>Resumen del Pedido</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Descuento:</span>
                            <span id="discount" class="discount-amount">-$0.00</span>
                        </div>
                        
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <span id="total">$0.00</span>
                        </div>
                        
                        <div class="promo-code">
                            <input type="text" id="promoInput" placeholder="Código de descuento">
                            <button type="button" id="applyPromo" class="btn-secondary">Aplicar</button>
                        </div>
                        
                        <div class="cart-actions">
                            <button type="button" id="clearCart" class="btn-secondary btn-full">
                                <i class="fas fa-trash"></i> Vaciar Carrito
                            </button>
                            <button type="button" id="proceedCheckout" class="btn-primary btn-full">
                                <i class="fas fa-credit-card"></i> Proceder al Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Cart -->
            <div class="empty-cart" id="emptyCart" style="display: none;">
                <div class="empty-cart-content">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Tu carrito está vacío</h3>
                    <p>¡Explora nuestros cursos y comienza tu aprendizaje!</p>
                    <a href="courses.php" class="btn-primary">
                        <i class="fas fa-book"></i> Ver Cursos
                    </a>
                </div>
            </div>

            <!-- Recommended Courses -->
            <div class="recommended-courses" id="recommendedCourses">
                <h2>Cursos Recomendados</h2>
                <div class="products-grid" id="recommendedGrid">
                    <!-- Se llenará dinámicamente -->
                </div>
            </div>
        </div>
    </section>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer"></div>

    <!-- Scripts -->
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js"></script>
    <script>
    // Configuración de Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyAtCjRAp58m3IewqHWgvwLuxxdIb5026kg",
        authDomain: "e-commerce-elprofehernan.firebaseapp.com",
        databaseURL: "https://e-commerce-elprofehernan-default-rtdb.firebaseio.com",
        projectId: "e-commerce-elprofehernan",
        storageBucket: "e-commerce-elprofehernan.firebasestorage.app",
        messagingSenderId: "769275191194",
        appId: "1:769275191194:web:5546d2aed7bd9e60f56423",
        measurementId: "G-3RGDE75FEY"
    };

    // Inicializar Firebase
    firebase.initializeApp(firebaseConfig);
    </script>
    <script src="assets/js/cart-page.js"></script>
</body>
</html>