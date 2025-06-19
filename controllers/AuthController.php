<?php

namespace Controllers;

// Asegurarse de que los archivos de las clases se incluyan antes de usarlas
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

use Config\Database;
use Models\User;

class AuthController {

    // Propiedad para la conexión a la base de datos
    private $db;

    public function __construct() {
        // Inicializar la conexión a la base de datos
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Autenticar usuario
    public function login() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Sanitizar entradas
            $email = htmlspecialchars(strip_tags(trim($email)));
            $password = htmlspecialchars(strip_tags(trim($password)));

            if (empty($email) || empty($password)) {
                self::setFlashMessage('error', 'Por favor, ingresa tu email y contraseña.');
                header('Location: login.php');
                exit();
            }

            $userModel = new User($this->db);
            $userData = $userModel->findByEmail($email);
            
            if ($userData) {
                // Poblar el objeto User con los datos encontrados
                $userModel->id = $userData['id'];
                $userModel->email = $userData['email'];
                $userModel->password = $userData['password'];
                $userModel->first_name = $userData['first_name'];
                $userModel->last_name = $userData['last_name'];
                $userModel->role = $userData['role'];
                $userModel->is_active = $userData['is_active'];
                
                if ($userModel->verifyPassword($password)) {
                    $this->createSession($userModel);
                    $userModel->updateLastLogin(); // Actualizar la fecha del último login
                    self::setFlashMessage('success', '¡Bienvenido de nuevo!');
                    
                    // Redirigir según el rol
                    if ($userModel->role === 'admin') {
                        header('Location: views/admin/dashboard.php');
                    } else {
                        header('Location: views/client/home.php');
                    }
                    exit();
                } else {
                    self::setFlashMessage('error', 'Contraseña incorrecta.');
                }
            } else {
                self::setFlashMessage('error', 'Usuario no encontrado o inactivo.');
            }
            header('Location: login.php');
            exit();
        }
        // Si no es POST, la vista (login.php) se encarga de mostrar el formulario.
    }

    public function register() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Sanitizar entradas
            $first_name = htmlspecialchars(strip_tags(trim($first_name)));
            $last_name = htmlspecialchars(strip_tags(trim($last_name)));
            $email = htmlspecialchars(strip_tags(trim($email)));
            $password = htmlspecialchars(strip_tags(trim($password)));
            $confirm_password = htmlspecialchars(strip_tags(trim($confirm_password)));

            if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
                self::setFlashMessage('error', 'Todos los campos son requeridos.');
                header('Location: signup.php');
                exit();
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                self::setFlashMessage('error', 'Formato de email inválido.');
                header('Location: signup.php');
                exit();
            }

            if ($password !== $confirm_password) {
                self::setFlashMessage('error', 'Las contraseñas no coinciden.');
                header('Location: signup.php');
                exit();
            }

            if (strlen($password) < 6) {
                self::setFlashMessage('error', 'La contraseña debe tener al menos 6 caracteres.');
                header('Location: signup.php');
                exit();
            }

            $userModel = new User($this->db);
            if ($userModel->emailExists($email)) {
                self::setFlashMessage('error', 'Este email ya está registrado.');
                header('Location: signup.php');
                exit();
            }

            $userModel->first_name = $first_name;
            $userModel->last_name = $last_name;
            $userModel->email = $email;
            $userModel->password = $userModel->hashPassword($password); // Hash de la contraseña
            $userModel->role = 'user'; // Rol por defecto

            if ($userModel->create()) {
                $this->createSession($userModel); // Iniciar sesión automáticamente
                self::setFlashMessage('success', '¡Registro exitoso! Bienvenido.');
                header('Location: views/client/home.php'); // Redirigir a la home del cliente
                exit();
            } else {
                self::setFlashMessage('error', 'Error al registrar el usuario. Intenta de nuevo.');
                header('Location: signup.php');
                exit();
            }
        }
        // Si no es POST, la vista (signup.php) se encarga de mostrar el formulario.
    }

    // Crear sesión
    public function createSession($user) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->getFullName();
        $_SESSION['user_first_name'] = $user->first_name;
        $_SESSION['user_last_name'] = $user->last_name;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['login_time'] = time();
    }

    // Cerrar sesión
    public static function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = array();
        session_destroy();
        header('Location: login.php'); // Redirigir a la página de login después de cerrar sesión
        exit();
    }

    // Verificar si el usuario está autenticado
    public static function isAuthenticated() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Alias para isAuthenticated() - para compatibilidad
    public static function isLoggedIn() {
        return self::isAuthenticated();
    }

    // Verificar si el usuario está logueado (método adicional)
    public static function checkAuth() {
        return self::isAuthenticated();
    }

    // Obtener usuario actual
    public static function getCurrentUser() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (self::isAuthenticated()) {
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'name' => $_SESSION['user_name'],
                'first_name' => $_SESSION['user_first_name'] ?? '',
                'last_name' => $_SESSION['user_last_name'] ?? '',
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }

    // Verificar si el usuario es administrador
    public static function isAdmin() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return self::isAuthenticated() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    // Verificar si el usuario es cliente
    public static function isUser() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return self::isAuthenticated() && ($_SESSION['user_role'] ?? '') === 'user';
    }

    // Requerir autenticación - redirige si no está logueado
    public static function requireAuth($redirectTo = 'login.php') {
        if (!self::isAuthenticated()) {
            self::setFlashMessage('error', 'Debes iniciar sesión para acceder a esta página.');
            header('Location: ' . $redirectTo);
            exit();
        }
    }

    // Requerir rol de administrador
    public static function requireAdmin($redirectTo = 'login.php') {
        if (!self::isAdmin()) {
            self::setFlashMessage('error', 'No tienes permisos para acceder a esta página.');
            header('Location: ' . $redirectTo);
            exit();
        }
    }

    // Obtener ID del usuario actual
    public static function getCurrentUserId() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return self::isAuthenticated() ? $_SESSION['user_id'] : null;
    }

    // Obtener rol del usuario actual
    public static function getCurrentUserRole() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return self::isAuthenticated() ? ($_SESSION['user_role'] ?? 'user') : null;
    }

    // Establecer mensaje flash
    public static function setFlashMessage($type, $message) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
    }

    // Obtener y limpiar mensaje flash
    public static function getFlashMessage() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        return $message;
    }

    // Verificar tiempo de sesión (opcional - para expirar sesiones)
    public static function checkSessionTimeout($timeout = 3600) { // 1 hora por defecto
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (self::isAuthenticated()) {
            $loginTime = $_SESSION['login_time'] ?? 0;
            if (time() - $loginTime > $timeout) {
                self::logout();
                return false;
            }
            // Actualizar tiempo de actividad
            $_SESSION['login_time'] = time();
        }
        return true;
    }

    // Regenerar ID de sesión para seguridad
    public static function regenerateSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
    }
}
?>
