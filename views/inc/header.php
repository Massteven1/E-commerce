<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir el controlador de autenticación para verificar el estado de login
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../controllers/CartController.php';

use Controllers\AuthController;
use Controllers\CartController;

$isAuthenticated = AuthController::isAuthenticated();
$currentUser = $isAuthenticated ? AuthController::getCurrentUser() : null;
//$cartItemCount = $isAuthenticated ? CartController::getCartItem($currentUser['id']) : 0;
$cartItemCount = $isAuthenticated ? : 0;

//! getCartItem falta por implementar
?>
<header class="bg-white shadow-sm py-4">
    <div class="container mx-auto flex justify-between items-center px-4">
        <div class="flex items-center">
            <a href="/index.php" class="text-2xl font-bold text-gray-800 mr-8">El Profesor Hernán</a>
            <nav class="hidden md:flex space-x-6">
                <a href="/index.php" class="text-gray-600 hover:text-primary-color transition-colors">Inicio</a>
                <a href="/views/client/all-courses.php" class="text-gray-600 hover:text-primary-color transition-colors">Cursos</a>
                <?php if ($isAuthenticated): ?>
                    <a href="/views/client/purchase-history.php" class="text-gray-600 hover:text-primary-color transition-colors">Mis Compras</a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="flex items-center space-x-4">
            <a href="/views/client/cart.php" class="relative text-gray-600 hover:text-primary-color transition-colors">
                <i class="fas fa-shopping-cart text-xl"></i>
                <span class="cart-count absolute -top-2 -right-2 bg-primary-color text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    <?php echo $cartItemCount; ?>
                </span>
            </a>
            <?php if ($isAuthenticated): ?>
                <div class="relative group">
                    <button class="flex items-center space-x-2 text-gray-600 hover:text-primary-color transition-colors focus:outline-none">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span class="hidden md:inline"><?php echo htmlspecialchars($currentUser['name'] ?? 'Usuario'); ?></span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <a href="/views/client/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Perfil</a>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <a href="/views/admin/dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Panel Admin</a>
                        <?php endif; ?>
                        <a href="/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Cerrar Sesión</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/login.php" class="btn btn-primary">Iniciar Sesión</a>
                <a href="/signup.php" class="btn btn-outline hidden md:inline-block">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</header>
