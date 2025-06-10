<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Asegurarse de que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener el contenido JSON de la solicitud
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log para debugging
error_log('Auth API called with data: ' . print_r($data, true));

// Verificar que los datos sean válidos
if (!$data || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Manejar diferentes acciones
switch ($data['action']) {
    case 'login':
        handleLogin($data);
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción desconocida']);
        exit;
}

/**
 * Maneja el inicio de sesión con Firebase
 */
function handleLogin($data) {
    // Verificar que todos los campos necesarios estén presentes
    if (!isset($data['uid'], $data['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        return;
    }

    // Guardar información del usuario en la sesión PHP
    $_SESSION['firebase_user'] = [
        'uid' => $data['uid'],
        'email' => $data['email'],
        'displayName' => $data['displayName'] ?? 'Usuario',
        'photoURL' => $data['photoURL'] ?? ''
    ];

    $_SESSION['user_logged_in'] = true;

    // Determinar si es administrador
    $isAdmin = in_array($data['email'], ADMIN_EMAILS);
    $_SESSION['is_admin'] = $isAdmin;
    
    // Log para debugging
    error_log('User logged in: ' . $data['email'] . ' (Admin: ' . ($isAdmin ? 'Yes' : 'No') . ')');
    
    // Determinar redirección basada en el rol
    $redirect = $isAdmin ? 'admin/index.php' : 'index.php';
    
    echo json_encode([
        'success' => true, 
        'message' => 'Inicio de sesión exitoso',
        'isAdmin' => $isAdmin,
        'redirect' => $redirect
    ]);
}

/**
 * Maneja el cierre de sesión
 */
function handleLogout() {
    // Destruir todas las variables de sesión
    $_SESSION = array();
    
    // Si se desea destruir la sesión completamente, borrar también la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finalmente, destruir la sesión
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Sesión cerrada correctamente']);
}
?>
