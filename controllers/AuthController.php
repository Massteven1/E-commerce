<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // Procesar login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validaciones básicas
            if (empty($email) || empty($password)) {
                self::setFlashMessage('error', 'Email y contraseña son requeridos');
                header('Location: login.php');
                exit();
            }

            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                self::setFlashMessage('error', 'Por favor, ingresa un email válido');
                header('Location: login.php');
                exit();
            }

            // Buscar usuario
            if ($this->user->findByEmail($email)) {
                // Verificar contraseña
                if ($this->user->verifyPassword($password)) {
                    // Actualizar último login
                    $this->user->updateLastLogin();
                    
                    // Crear sesión
                    $this->createSession($this->user);
                    
                    // Redirigir según el rol
                    $this->redirectByRole($this->user->role);
                } else {
                    self::setFlashMessage('error', 'Credenciales inválidas');
                    header('Location: login.php');
                    exit();
                }
            } else {
                self::setFlashMessage('error', 'Usuario no encontrado');
                header('Location: login.php');
                exit();
            }
        }
    }

    // Procesar registro
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $first_name = trim($_POST['firstName'] ?? '');
            $last_name = trim($_POST['lastName'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirmPassword'] ?? '';

            // Validaciones
            $errors = $this->validateRegistration($first_name, $last_name, $email, $password, $confirm_password);

            if (!empty($errors)) {
                self::setFlashMessage('error', implode('<br>', $errors));
                header('Location: signup.php');
                exit();
            }

            // Verificar si el email ya existe
            if ($this->user->emailExists($email)) {
                self::setFlashMessage('error', 'Este email ya está registrado');
                header('Location: signup.php');
                exit();
            }

            // Crear usuario
            $this->user->first_name = $first_name;
            $this->user->last_name = $last_name;
            $this->user->email = $email;
            $this->user->password = $this->user->hashPassword($password);
            $this->user->role = 'user'; // Los usuarios registrados son siempre 'user'

            if ($this->user->create()) {
                // Crear sesión automáticamente
                $this->createSession($this->user);
                
                self::setFlashMessage('success', 'Cuenta creada exitosamente');
                $this->redirectByRole($this->user->role);
            } else {
                self::setFlashMessage('error', 'Error al crear la cuenta. Intenta de nuevo.');
                header('Location: signup.php');
                exit();
            }
        }
    }

    // Logout
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Destruir todas las variables de sesión
        $_SESSION = array();

        // Si se desea destruir la sesión completamente, borre también la cookie de sesión.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finalmente, destruir la sesión.
        session_destroy();
        
        header('Location: login.php');
        exit();
    }

    // Validaciones de registro
    private function validateRegistration($first_name, $last_name, $email, $password, $confirm_password) {
        $errors = [];

        if (empty($first_name)) {
            $errors[] = 'El nombre es requerido';
        } elseif (strlen($first_name) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres';
        }

        if (empty($last_name)) {
            $errors[] = 'El apellido es requerido';
        } elseif (strlen($last_name) < 2) {
            $errors[] = 'El apellido debe tener al menos 2 caracteres';
        }

        if (empty($email)) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        }

        if (empty($password)) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if ($password !== $confirm_password) {
            $errors[] = 'Las contraseñas no coinciden';
        }

        return $errors;
    }

    // Crear sesión
    private function createSession($user) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->getFullName();
        $_SESSION['user_role'] = $user->role;
        $_SESSION['login_time'] = time();
    }

    // Redirigir según el rol
    private function redirectByRole($role) {
        $redirect_url = $this->getRedirectUrl($role);
        header("Location: $redirect_url");
        exit();
    }

    // Obtener URL de redirección según el rol
    private function getRedirectUrl($role) {
        if ($role === 'admin') {
            return 'views/admin/index.php?controller=admin&action=dashboard';
        } else {
            return 'views/client/home.php'; // Redirección directa y simple
        }
    }

    // Establecer mensaje flash (método estático)
    public static function setFlashMessage($type, $message) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
    }

    // Obtener mensaje flash
    public static function getFlashMessage() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }

    // Verificar si el usuario está autenticado
    public static function isAuthenticated() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Verificar si el usuario es administrador
    public static function isAdmin() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return self::isAuthenticated() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
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
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }
}
?>
