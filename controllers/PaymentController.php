<?php
namespace Controllers;

// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir las clases necesarias
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/UserCourse.php';
require_once __DIR__ . '/../models/Playlist.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../helpers/SecurityHelper.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/StripeHelper.php';

use Config\Database;
use Models\Order;
use Models\UserCourse;
use Models\Playlist;
use Controllers\AuthController;
use Controllers\CartController;
use Helpers\SecurityHelper;
use Helpers\ValidationHelper;
use Helpers\StripeHelper;

class PaymentController {
    private $db;
    private $orderModel;
    private $userCourseModel;
    private $playlistModel;
    private $stripeHelper;

    // Configura tus claves de Stripe aquí
    private $stripeSecretKey = 'sk_test_51Ra0H5EIqBlXSQrI4Fjzj7GA4NUPM6OeG7FvxVqIySnQyJGByfodso8s5UVvTmw2LzwDOsvMf3FN6fzucdxjbDZY00rYqJgkYV';
    private $stripePublishableKey = 'pk_test_51Ra0H5EIqBlXSQrI3JkmyctAonU9YzM9ezg1w2kmBFuTKZxEDRudatwcoRHzAEbH2vx1AUbZyPt4loE7qSNwhnrD00LR7GSHXF';

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            $this->orderModel = new Order($this->db);
            $this->userCourseModel = new UserCourse($this->db);
            $this->playlistModel = new Playlist($this->db);

            // Inicializar Stripe Helper
            $this->stripeHelper = new StripeHelper($this->stripeSecretKey);
            
            // Intentar cargar la librería oficial de Stripe si está disponible
            $this->initializeStripe();
            
        } catch (\Exception $e) {
            error_log("Error inicializando PaymentController: " . $e->getMessage());
            throw new \Exception("Error del sistema de pagos. Por favor, contacta al administrador.");
        }
    }

    /**
     * Inicializar Stripe (librería oficial si está disponible)
     */
    private function initializeStripe() {
        // Intentar cargar Stripe usando Composer
        $composerPaths = [
            __DIR__ . '/../vendor/autoload.php',
            __DIR__ . '/../../vendor/autoload.php',
            __DIR__ . '/../../../vendor/autoload.php'
        ];

        foreach ($composerPaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                break;
            }
        }

        // Configurar Stripe si la clase está disponible
        if (class_exists('\Stripe\Stripe')) {
            \Stripe\Stripe::setApiKey($this->stripeSecretKey);
            error_log("Stripe library loaded successfully");
        } else {
            error_log("Stripe library not found, using StripeHelper");
        }
    }

    // Método para obtener la clave publicable (usado en el frontend)
    public function getPublishableKey() {
        return $this->stripePublishableKey;
    }

    // Generar token CSRF
    public function generateCSRFToken() {
        return SecurityHelper::generateCSRFToken();
    }

    // Validar token CSRF
    private function validateCSRFToken($token) {
        return SecurityHelper::validateCSRFToken($token);
    }

    // Validar datos del formulario
    private function validateCheckoutData($data) {
        return ValidationHelper::validateCheckoutData($data);
    }

    public function checkout() {
        try {
            // Verificar autenticación
            if (!AuthController::isAuthenticated()) {
                AuthController::setFlashMessage('error', 'Debes iniciar sesión para continuar con la compra.');
                header('Location: ../../login.php');
                exit();
            }

            $user = AuthController::getCurrentUser();
            $cart = $_SESSION['cart'] ?? [];

            if (empty($cart)) {
                AuthController::setFlashMessage('error', 'Tu carrito está vacío.');
                header('Location: ../views/client/cart.php');
                exit();
            }

            // Usar CartController para obtener items normalizados
            $cartController = new CartController();
            $cartItems = $cartController->getCartItems();
            $totals = $cartController->calculateTotals($cartItems);

            if (empty($cartItems)) {
                AuthController::setFlashMessage('error', 'No hay cursos válidos en tu carrito.');
                header('Location: ../views/client/cart.php');
                exit();
            }

            // Generar token CSRF
            $csrfToken = $this->generateCSRFToken();

            // Pasar datos a la vista
            $cart_items = $cartItems;

            include __DIR__ . '/../views/client/checkout.php';
            
        } catch (\Exception $e) {
            error_log("Error en checkout: " . $e->getMessage());
            AuthController::setFlashMessage('error', 'Error del sistema. Por favor, intenta de nuevo.');
            header('Location: ../views/client/cart.php');
            exit();
        }
    }

    public function processPayment() {
        try {
            if (!AuthController::isAuthenticated()) {
                AuthController::setFlashMessage('error', 'Debes iniciar sesión para completar la compra.');
                header('Location: ../../login.php');
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                AuthController::setFlashMessage('error', 'Método de solicitud no permitido.');
                header('Location: ../views/client/cart.php');
                exit();
            }

            // Validar token CSRF
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!$this->validateCSRFToken($csrfToken)) {
                AuthController::setFlashMessage('error', 'Token de seguridad inválido. Por favor, intenta de nuevo.');
                header('Location: ../views/client/checkout.php');
                exit();
            }

            $currentUser = AuthController::getCurrentUser();
            $userId = $currentUser['id'];
            $userEmail = $currentUser['email'];

            // Validar datos del formulario
            $validationErrors = $this->validateCheckoutData($_POST);
            if (!empty($validationErrors)) {
                AuthController::setFlashMessage('error', 'Errores en el formulario: ' . implode(', ', $validationErrors));
                header('Location: ../views/client/checkout.php');
                exit();
            }

            $stripeToken = $_POST['stripeToken'] ?? null;

            if (empty($stripeToken)) {
                AuthController::setFlashMessage('error', 'Token de pago no recibido. Intenta de nuevo.');
                header('Location: ../views/client/checkout.php');
                exit();
            }

            // Usar CartController para obtener datos actualizados del carrito
            $cartController = new CartController();
            $cartItems = $cartController->getCartItems();
            $totals = $cartController->calculateTotals($cartItems);

            if (empty($cartItems)) {
                AuthController::setFlashMessage('error', 'Tu carrito está vacío. No se puede procesar el pago.');
                header('Location: ../views/client/cart.php');
                exit();
            }

            // Verificar acceso duplicado y preparar lista de cursos
            $purchasedPlaylistIds = [];
            foreach ($cartItems as $item) {
                $playlistId = $item['id'];
                
                // Verificar que el usuario no tenga acceso
                if ($this->userCourseModel->hasAccess($userId, $playlistId)) {
                    AuthController::setFlashMessage('error', 'Ya tienes acceso a uno de los cursos seleccionados: ' . htmlspecialchars($item['name']));
                    header('Location: ../views/client/cart.php');
                    exit();
                }
                
                $purchasedPlaylistIds[] = $playlistId;
            }

            $finalAmount = $totals['total'];

            // Validar que el monto sea mayor a 0
            if ($finalAmount <= 0) {
                AuthController::setFlashMessage('error', 'El monto total debe ser mayor a cero.');
                header('Location: ../views/client/checkout.php');
                exit();
            }

            // Stripe espera el monto en centavos
            $amountInCents = round($finalAmount * 100);
            $currency = 'usd';

            // Procesar pago con Stripe
            $charge = $this->processStripePayment([
                'amount' => $amountInCents,
                'currency' => $currency,
                'source' => $stripeToken,
                'description' => 'Compra de cursos en El Profesor Hernán - Usuario: ' . $userEmail,
                'receipt_email' => $userEmail,
                'metadata' => [
                    'user_id' => $userId,
                    'user_email' => $userEmail,
                    'cart_items' => json_encode($purchasedPlaylistIds),
                    'original_amount' => $totals['subtotal'],
                    'discount_applied' => $totals['discount'],
                    'tax_amount' => $totals['tax'],
                    'promo_code' => $totals['promo_code_applied'] ?? 'none'
                ]
            ]);

            if ($charge && isset($charge['status']) && $charge['status'] === 'succeeded') {
                // El pago fue exitoso
                $transactionId = $charge['id'];
                $orderStatus = 'completed';

                // 1. Registrar el pedido en la base de datos
                $this->orderModel->user_id = $userId;
                $this->orderModel->transaction_id = $transactionId;
                $this->orderModel->amount = $finalAmount;
                $this->orderModel->currency = $currency;
                $this->orderModel->status = $orderStatus;

                if ($this->orderModel->create()) {
                    $orderId = $this->orderModel->id;

                    // 2. Otorgar acceso a los cursos comprados
                    $accessGranted = true;
                    foreach ($purchasedPlaylistIds as $playlistId) {
                        if (!$this->userCourseModel->grantAccess($userId, $playlistId, $orderId)) {
                            $accessGranted = false;
                            error_log("Error al otorgar acceso al curso $playlistId para el usuario $userId");
                        }
                    }

                    if (!$accessGranted) {
                        error_log("No se pudo otorgar acceso a todos los cursos para el pedido $orderId");
                    }

                    // 3. Limpiar el carrito de la sesión
                    unset($_SESSION['cart']);
                    unset($_SESSION['promo_code_applied']);
                    unset($_SESSION['promo_discount_rate']);
                    unset($_SESSION['promo_message']);
                    unset($_SESSION['csrf_token']);

                    AuthController::setFlashMessage('success', '¡Pago exitoso! Tu compra ha sido confirmada y ya tienes acceso a tus cursos.');
                    header('Location: ../views/client/order-confirmation.php?order_id=' . $orderId);
                    exit();

                } else {
                    error_log("Error al guardar pedido en BD. Transaction ID: $transactionId, User ID: $userId");
                    AuthController::setFlashMessage('error', 'Pago exitoso, pero hubo un error al registrar tu pedido. Por favor, contacta a soporte con el ID de transacción: ' . $transactionId);
                    header('Location: ../views/client/cart.php');
                    exit();
                }
            } else {
                $status = $charge['status'] ?? 'unknown';
                AuthController::setFlashMessage('error', 'El pago no pudo ser procesado. Estado: ' . $status);
                header('Location: ../views/client/checkout.php');
                exit();
            }

        } catch (\Exception $e) {
            error_log("Error general en processPayment: " . $e->getMessage());
            
            // Determinar el tipo de error para mostrar mensaje apropiado
            $errorMessage = 'Ocurrió un error inesperado al procesar tu pago. Intenta de nuevo.';
            
            if (strpos($e->getMessage(), 'card') !== false || strpos($e->getMessage(), 'declined') !== false) {
                $errorMessage = 'Tu tarjeta fue rechazada. Por favor, verifica los datos o usa otra tarjeta.';
            } elseif (strpos($e->getMessage(), 'network') !== false || strpos($e->getMessage(), 'connection') !== false) {
                $errorMessage = 'Error de conexión con el sistema de pagos. Intenta de nuevo.';
            }
            
            AuthController::setFlashMessage('error', $errorMessage);
            header('Location: ../views/client/checkout.php');
            exit();
        }
    }

    /**
     * Procesar pago con Stripe (usando librería oficial o helper)
     */
    private function processStripePayment($params) {
        try {
            // Intentar usar la librería oficial de Stripe primero
            if (class_exists('\Stripe\Charge')) {
                return \Stripe\Charge::create($params)->toArray();
            } else {
                // Usar nuestro helper personalizado
                return $this->stripeHelper->createCharge($params);
            }
        } catch (\Exception $e) {
            error_log("Error en processStripePayment: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Webhook para manejar eventos de Stripe
     */
    public function handleWebhook() {
        try {
            $payload = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
            
            // Validar la firma del webhook (implementar según necesidades)
            // $this->validateWebhookSignature($payload, $signature);
            
            $event = json_decode($payload, true);
            
            if (!$event) {
                http_response_code(400);
                exit('Invalid payload');
            }

            // Manejar diferentes tipos de eventos
            switch ($event['type']) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event['data']['object']);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event['data']['object']);
                    break;
                default:
                    error_log('Unhandled webhook event type: ' . $event['type']);
            }

            http_response_code(200);
            echo 'Webhook handled successfully';
            
        } catch (\Exception $e) {
            error_log("Error en webhook: " . $e->getMessage());
            http_response_code(500);
            exit('Webhook error');
        }
    }

    private function handlePaymentSucceeded($paymentIntent) {
        // Lógica para manejar pago exitoso
        error_log("Payment succeeded: " . $paymentIntent['id']);
    }

    private function handlePaymentFailed($paymentIntent) {
        // Lógica para manejar pago fallido
        error_log("Payment failed: " . $paymentIntent['id']);
    }
}

// Manejar las rutas si se accede directamente a este archivo
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $action = $_GET['action'] ?? 'checkout';
    $controller = new PaymentController();
    
    switch ($action) {
        case 'processPayment':
            $controller->processPayment();
            break;
        case 'webhook':
            $controller->handleWebhook();
            break;
        case 'checkout':
        default:
            $controller->checkout();
            break;
    }
}
?>
