<?php
// Iniciar sesión
session_start();

// Definir la ruta raíz del proyecto
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', __DIR__);

// Configuración del sitio
define('SITE_NAME', 'El Profesor Hernan');
define('SITE_URL', 'http://localhost/E-commerce');

// Configuración de la base de datos (solo para cursos, no usuarios)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'ecommerce_cursos');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de Firebase (actualizar con tus credenciales)
define('FIREBASE_API_KEY', 'AIzaSyAtCjRAp58m3IewqHWgvwLuxxdIb5026kg');
define('FIREBASE_AUTH_DOMAIN', 'e-commerce-elprofehernan.firebaseapp.com');
define('FIREBASE_PROJECT_ID', 'e-commerce-elprofehernan');

// Lista de administradores (emails de Firebase)
define('ADMIN_EMAILS', [
    'admin@elprofesorhernan.com',
    'hernan@elprofesorhernan.com'
]);

// Configuración de errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Funciones de utilidad para Firebase
function isLoggedIn() {
    return isset($_SESSION['firebase_user']) && !empty($_SESSION['firebase_user']);
}

function isAdmin() {
    if (!isLoggedIn()) return false;
    $userEmail = $_SESSION['firebase_user']['email'] ?? '';
    return in_array($userEmail, ADMIN_EMAILS);
}

function getCurrentUser() {
    return $_SESSION['firebase_user'] ?? null;
}

function getCurrentUserUID() {
    $user = getCurrentUser();
    return $user['uid'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'hace un momento';
    if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
    if ($time < 2592000) return 'hace ' . floor($time/86400) . ' días';
    if ($time < 31536000) return 'hace ' . floor($time/2592000) . ' meses';
    return 'hace ' . floor($time/31536000) . ' años';
}

// Función para limpiar datos de entrada
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para generar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Función para mostrar mensajes flash
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Función para debug (solo en desarrollo)
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
?>
