<?php
namespace Helpers; // Añadir namespace

class SecurityHelper {
    
    /**
     * Generar token CSRF
     */
    public static function generateCSRFToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validar token CSRF
     */
    public static function validateCSRFToken($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Limpiar y sanitizar entrada
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar teléfono
     */
    public static function validatePhone($phone) {
        return preg_match('/^[\+]?[0-9\s\-$$$$]{10,}$/', $phone);
    }
    
    /**
     * Generar hash seguro para contraseñas
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verificar contraseña
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Rate limiting básico
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'rate_limit_' . $identifier;
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'first_attempt' => $now];
            return true;
        }
        
        $data = $_SESSION[$key];
        
        // Si ha pasado el tiempo límite, resetear
        if ($now - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = ['count' => 1, 'first_attempt' => $now];
            return true;
        }
        
        // Si no ha excedido el límite
        if ($data['count'] < $maxAttempts) {
            $_SESSION[$key]['count']++;
            return true;
        }
        
        return false;
    }
}
?>
