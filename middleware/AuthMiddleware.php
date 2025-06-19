<?php
namespace Middleware;

require_once __DIR__ . '/../controllers/AuthController.php';

use Controllers\AuthController;

class AuthMiddleware {
    
    // Verificar si el usuario está autenticado
    public static function requireAuth() {
        if (!AuthController::isAuthenticated()) {
            header('Location: ../../login.php');
            exit();
        }
    }
    
    // Verificar si el usuario es administrador
    public static function requireAdmin() {
        self::requireAuth();
        
        if (!AuthController::isAdmin()) {
            header('Location: ../../views/client/home.php');
            exit();
        }
    }
    
    // Verificar si el usuario es cliente
    public static function requireUser() {
        self::requireAuth();
        
        if (AuthController::isAdmin()) {
            header('Location: ../../views/admin/index.php?controller=admin&action=dashboard');
            exit();
        }
    }
    
    // Redirigir usuarios autenticados
    public static function redirectIfAuthenticated() {
        if (AuthController::isAuthenticated()) {
            if (AuthController::isAdmin()) {
                header('Location: views/admin/index.php?controller=admin&action=dashboard');
            } else {
                header('Location: views/client/home.php');
            }
            exit();
        }
    }
}
?>
