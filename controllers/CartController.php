<?php
namespace Controllers; // Añadir namespace

// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir las clases necesarias
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Playlist.php';
require_once __DIR__ . '/../models/UserCourse.php';
require_once __DIR__ . '/../controllers/AuthController.php';

use Config\Database;
use Models\Playlist;
use Models\UserCourse;
use Controllers\AuthController;

class CartController {
    private $db;
    private $playlistModel;
    private $userCourseModel;
    
    // Códigos promocionales actualizados según la documentación
    private $promo_codes = [
        'SAVE10' => 0.10,    // 10% de descuento
        'SAVE20' => 0.20,    // 20% de descuento
        'SAVE30' => 0.30,    // 30% de descuento
        'STUDENT' => 0.15,   // 15% de descuento
        'WELCOME' => 0.25,   // 25% de descuento
        'PROMO50' => 0.50,   // 50% de descuento
        // Códigos adicionales para compatibilidad
        'VERANO10' => 0.10,
        'EDUCACION25' => 0.25,
        'PROFE50' => 0.50,
        'NUEVOUSUARIO' => 0.15,
        'AHORRAHOY' => 0.20,
        'BIENVENIDO20' => 0.20
    ];

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->playlistModel = new Playlist($this->db);
        $this->userCourseModel = new UserCourse($this->db);

        // Inicializar el carrito si no existe en la sesión
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        // Inicializar el estado del código promocional
        if (!isset($_SESSION['promo_code_applied'])) {
            $_SESSION['promo_code_applied'] = null;
            $_SESSION['promo_discount_rate'] = 0;
            $_SESSION['promo_message'] = '';
        }
    }

    public function view() {
        // Limpiar carrito de cursos ya comprados si el usuario está autenticado
        if (AuthController::isAuthenticated()) {
            $this->cleanPurchasedCourses();
        }
        
        $cart_items = $this->getCartItems();
        $totals = $this->calculateTotals($cart_items);
        
        // Incluir la vista del carrito
        include __DIR__ . '/../views/client/cart.php';
    }

    public function add($playlist_id) {
        $playlist = $this->playlistModel->readOne($playlist_id);

        if ($playlist) {
            // Verificar si el usuario ya tiene acceso a este curso
            if (AuthController::isAuthenticated()) {
                $currentUser = AuthController::getCurrentUser();
                if ($this->userCourseModel->hasAccess($currentUser['id'], $playlist_id)) {
                    $response = ['status' => 'error', 'message' => 'Ya tienes acceso a este curso.'];
                    echo json_encode($response);
                    return;
                }
            }

            // Convertir estructura para compatibilidad con PaymentController
            $_SESSION['cart'][$playlist_id] = [
                'id' => $playlist['id'],
                'name' => $playlist['name'],
                'price' => $playlist['price'],
                'cover_image' => $playlist['cover_image'],
                'level' => $playlist['level'] ?? '',
                'quantity' => 1 // Siempre será 1 para cursos
            ];
            
            $response = ['status' => 'success', 'message' => 'Curso añadido al carrito.', 'cart_count' => self::getCartCount()];
            echo json_encode($response);
            return;
        }
        
        $response = ['status' => 'error', 'message' => 'No se pudo añadir el curso al carrito.'];
        echo json_encode($response);
        return;
    }

    public function remove($playlist_id) {
        if (isset($_SESSION['cart'][$playlist_id])) {
            unset($_SESSION['cart'][$playlist_id]);
            $response = ['status' => 'success', 'message' => 'Curso eliminado del carrito.', 'cart_count' => self::getCartCount()];
        } else {
            $response = ['status' => 'error', 'message' => 'El curso no estaba en el carrito.'];
        }
        echo json_encode($response);
    }

    public function applyPromoCode($code) {
        $code = strtoupper(trim($code)); // Normalizar el código

        if (array_key_exists($code, $this->promo_codes)) {
            $_SESSION['promo_code_applied'] = $code;
            $_SESSION['promo_discount_rate'] = $this->promo_codes[$code];
            $_SESSION['promo_message'] = 'Código "' . htmlspecialchars($code) . '" aplicado con éxito. Descuento: ' . ($this->promo_codes[$code] * 100) . '%';
            $response = ['status' => 'success', 'message' => $_SESSION['promo_message']];
        } else {
            $_SESSION['promo_code_applied'] = null;
            $_SESSION['promo_discount_rate'] = 0;
            $_SESSION['promo_message'] = 'Código promocional inválido o expirado.';
            $response = ['status' => 'error', 'message' => $_SESSION['promo_message']];
        }
        echo json_encode($response);
    }

    public function removePromoCode() {
        $_SESSION['promo_code_applied'] = null;
        $_SESSION['promo_discount_rate'] = 0;
        $_SESSION['promo_message'] = 'Código promocional eliminado.';
        $response = ['status' => 'success', 'message' => $_SESSION['promo_message']];
        echo json_encode($response);
    }

    // Obtener items del carrito en formato consistente
    public function getCartItems() {
        $cart_items = [];
        
        foreach ($_SESSION['cart'] as $item) {
            // Asegurar que tenemos toda la información necesaria
            $playlist = $this->playlistModel->readOne($item['id']);
            if ($playlist) {
                $cart_items[] = [
                    'id' => $playlist['id'],
                    'name' => $playlist['name'],
                    'price' => $playlist['price'],
                    'cover_image' => $playlist['cover_image'],
                    'level' => $playlist['level'] ?? '',
                    'description' => $playlist['description'] ?? '',
                    'quantity' => 1
                ];
            }
        }
        
        return $cart_items;
    }

    // Limpiar cursos ya comprados del carrito
    private function cleanPurchasedCourses() {
        if (!AuthController::isAuthenticated()) {
            return;
        }
        
        $currentUser = AuthController::getCurrentUser();
        $cleaned = false;
        
        foreach ($_SESSION['cart'] as $playlist_id => $item) {
            if ($this->userCourseModel->hasAccess($currentUser['id'], $playlist_id)) {
                unset($_SESSION['cart'][$playlist_id]);
                $cleaned = true;
            }
        }
        
        if ($cleaned) {
            AuthController::setFlashMessage('info', 'Se han eliminado del carrito los cursos a los que ya tienes acceso.');
        }
    }

    public function calculateTotals($items) {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'];
        }

        $discount_rate = $_SESSION['promo_discount_rate'] ?? 0;
        $tax_rate = 0.07; // 7% de impuesto

        $discount = $subtotal * $discount_rate;
        $subtotal_after_discount = $subtotal - $discount;
        $tax = $subtotal_after_discount * $tax_rate;
        $total = $subtotal_after_discount + $tax;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'promo_code_applied' => $_SESSION['promo_code_applied']
        ];
    }

    // Obtener número de items en el carrito
    public static function getCartCount() {
        return count($_SESSION['cart']);
    }

    // Vaciar carrito
    public function clear() {
        $_SESSION['cart'] = [];
        unset($_SESSION['promo_code_applied']);
        unset($_SESSION['promo_discount_rate']);
        unset($_SESSION['promo_message']);
        
        AuthController::setFlashMessage('success', 'Carrito vaciado.');
    }
}

// Manejar las rutas si se accede directamente a este archivo
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $action = $_GET['action'] ?? 'view';
    $controller = new CartController();
    
    switch ($action) {
        case 'add':
            $playlist_id = $_GET['id'] ?? 0;
            if ($playlist_id > 0) {
                $controller->add($playlist_id);
            } else {
                $response = ['status' => 'error', 'message' => 'ID de curso inválido.'];
                echo json_encode($response);
            }
            exit();
            break;
            
        case 'remove':
            $playlist_id = $_GET['id'] ?? 0;
            if ($playlist_id > 0) {
                $controller->remove($playlist_id);
            } else {
                $response = ['status' => 'error', 'message' => 'ID de curso inválido.'];
                echo json_encode($response);
            }
            exit();
            break;
            
        case 'apply_promo':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $promo_code = $_POST['promo_code'] ?? '';
                $controller->applyPromoCode($promo_code);
            } else {
                $response = ['status' => 'error', 'message' => 'Método no permitido.'];
                echo json_encode($response);
            }
            exit();
            break;
            
        case 'remove_promo':
            $controller->removePromoCode();
            exit();
            break;
            
        case 'clear':
            $controller->clear();
            header('Location: ../views/client/cart.php');
            exit();
            break;
            
        case 'view':
        default:
            // Si se accede directamente a CartController.php sin una acción específica,
            // redirigir a la vista del carrito.
            header('Location: ../views/client/cart.php');
            exit();
            break;
    }
}
?>
