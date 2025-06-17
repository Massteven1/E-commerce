<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar Stripe usando Composer (si está disponible)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/UserCourse.php';
require_once __DIR__ . '/../models/Playlist.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class PaymentController {
    private $db;
    private $orderModel;
    private $userCourseModel;
    private $playlistModel;

    // Configura tus claves de Stripe aquí
    private $stripeSecretKey = 'sk_test_51Ra0H5EIqBlXSQrI4Fjzj7GA4NUPM6OeG7FvxVqIySnQyJGByfodso8s5UVvTmw2LzwDOsvMf3FN6fzucdxjbDZY00rYqJgkYV'; // ¡CAMBIA ESTO!
    private $stripePublishableKey = 'pk_test_51Ra0H5EIqBlXSQrI3JkmyctAonU9YzM9ezg1w2kmBFuTKZxEDRudatwcoRHzAEbH2vx1AUbZyPt4loE7qSNwhnrD00LR7GSHXF'; // ¡CAMBIA ESTO!

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->orderModel = new Order($this->db);
        $this->userCourseModel = new UserCourse($this->db);
        $this->playlistModel = new Playlist($this->db);

        // Solo configurar Stripe si la librería está disponible
        if (class_exists('\Stripe\Stripe')) {
            \Stripe\Stripe::setApiKey($this->stripeSecretKey);
        }
    }

    // Método para obtener la clave publicable (usado en el frontend)
    public function getPublishableKey() {
        return $this->stripePublishableKey;
    }

    public function checkout() {
        // Verificar autenticación
        if (!AuthController::isAuthenticated()) {
            header('Location: ../../login.php');
            exit();
        }

        $user = AuthController::getCurrentUser();
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            header('Location: cart.php');
            exit();
        }

        // Obtener información de los cursos en el carrito
        $database = new Database();
        $db = $database->getConnection();
        $playlistModel = new Playlist($db);

        $cartItems = [];
        $total = 0;

        foreach ($cart as $item) {
            // Asegurarse de que $item sea un ID o tenga un ID
            $playlistId = is_array($item) ? $item['id'] : $item;
            
            $playlist = $playlistModel->readOne($playlistId);
            if ($playlist) {
                $cartItems[] = $playlist;
                $total += $playlist['price'];
            }
        }

        // Calcular totales
        $subtotal = $total;
        $discount = 0;
        $promoCodeApplied = '';
        
        // Aplicar descuento si hay un código promocional
        if (isset($_SESSION['promo_code_applied'])) {
            $promoCodeApplied = $_SESSION['promo_code_applied'];
            $discountRate = $_SESSION['promo_discount_rate'] ?? 0;
            $discount = $subtotal * $discountRate;
        }
        
        $subtotalAfterDiscount = $subtotal - $discount;
        $taxRate = 0.07; // 7% de impuesto
        $tax = $subtotalAfterDiscount * $taxRate;
        $totalAmount = $subtotalAfterDiscount + $tax;
        
        // Preparar datos para la vista
        $totals = [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'promo_code_applied' => $promoCodeApplied,
            'tax' => $tax,
            'total' => $totalAmount
        ];

        // Pasar datos a la vista
        $cart_items = $cartItems;
        
        include __DIR__ . '/../views/client/checkout.php';
    }

    public function processPayment() {
        if (!AuthController::isAuthenticated()) {
            AuthController::setFlashMessage('error', 'Debes iniciar sesión para completar la compra.');
            header('Location: ../../login.php');
            exit();
        }

        $currentUser = AuthController::getCurrentUser();
        $userId = $currentUser['id'];
        $userEmail = $currentUser['email'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stripeToken = $_POST['stripeToken'] ?? null;
            $cartItems = $_SESSION['cart'] ?? [];

            if (empty($stripeToken)) {
                AuthController::setFlashMessage('error', 'Token de pago no recibido. Intenta de nuevo.');
                header('Location: ../../views/client/cart.php?action=checkout');
                exit();
            }

            if (empty($cartItems)) {
                AuthController::setFlashMessage('error', 'Tu carrito está vacío. No se puede procesar el pago.');
                header('Location: ../../views/client/cart.php');
                exit();
            }

            // Verificar que Stripe esté disponible
            if (!class_exists('\Stripe\Stripe')) {
                AuthController::setFlashMessage('error', 'Sistema de pagos no disponible. Por favor, contacta al administrador.');
                header('Location: ../../views/client/cart.php');
                exit();
            }

            // Recalcular el total del carrito en el servidor para evitar manipulaciones
            $totalAmount = 0;
            $purchasedPlaylistIds = [];
            foreach ($cartItems as $item) {
                // Asegurarse de que $item sea un ID o tenga un ID
                $playlistId = is_array($item) ? $item['id'] : $item;
                
                $playlist = $this->playlistModel->readOne($playlistId);
                if ($playlist) {
                    $totalAmount += $playlist['price'];
                    $purchasedPlaylistIds[] = $playlist['id'];
                }
            }

            // Aplicar descuento si hay un código promocional
            $discountRate = $_SESSION['promo_discount_rate'] ?? 0;
            $discountAmount = $totalAmount * $discountRate;
            $subtotalAfterDiscount = $totalAmount - $discountAmount;

            // Aplicar impuesto
            $taxRate = 0.07; // 7% de impuesto
            $taxAmount = $subtotalAfterDiscount * $taxRate;
            $finalAmount = $subtotalAfterDiscount + $taxAmount;

            // Stripe espera el monto en la unidad más pequeña de la moneda (ej. centavos para USD)
            $amountInCents = round($finalAmount * 100);
            $currency = 'usd'; // O 'mxn', 'cop', 'eur' según tu configuración de Stripe

            try {
                // Crear un cargo en Stripe
                $charge = \Stripe\Charge::create([
                    'amount' => $amountInCents,
                    'currency' => $currency,
                    'description' => 'Compra de cursos en El Profesor Hernán',
                    'source' => $stripeToken,
                    'receipt_email' => $userEmail,
                    'metadata' => [
                        'user_id' => $userId,
                        'cart_items' => json_encode($purchasedPlaylistIds),
                    ],
                ]);

                if ($charge->status === 'succeeded') {
                    // El pago fue exitoso
                    $transactionId = $charge->id;
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
                        foreach ($purchasedPlaylistIds as $playlistId) {
                            $this->userCourseModel->grantAccess($userId, $playlistId, $orderId);
                        }

                        // 3. Limpiar el carrito de la sesión
                        unset($_SESSION['cart']);
                        unset($_SESSION['promo_code_applied']);
                        unset($_SESSION['promo_discount_rate']);
                        unset($_SESSION['promo_message']);

                        AuthController::setFlashMessage('success', '¡Pago exitoso! Tu compra ha sido confirmada.');
                        header('Location: ../../views/client/order-confirmation.php?order_id=' . $orderId);
                        exit();

                    } else {
                        // Si el pedido no se pudo guardar en DB, pero Stripe sí cobró
                        AuthController::setFlashMessage('error', 'Pago exitoso, pero hubo un error al registrar tu pedido. Por favor, contacta a soporte con el ID de transacción: ' . $transactionId);
                        header('Location: ../../views/client/cart.php');
                        exit();
                    }
                } else {
                    AuthController::setFlashMessage('error', 'El pago no pudo ser procesado. Estado: ' . $charge->status);
                    header('Location: ../../views/client/cart.php?action=checkout');
                    exit();
                }
            } catch (\Stripe\Exception\CardException $e) {
                // Error de tarjeta (ej. tarjeta rechazada)
                $body = $e->getJsonBody();
                $err  = $body['error'];
                AuthController::setFlashMessage('error', 'Error de tarjeta: ' . ($err['message'] ?? 'Tu tarjeta fue rechazada.'));
                header('Location: ../../views/client/cart.php?action=checkout');
                exit();
            } catch (Exception $e) {
                // Cualquier otro error
                AuthController::setFlashMessage('error', 'Ocurrió un error inesperado al procesar tu pago. Intenta de nuevo.');
                error_log("General Payment Error: " . $e->getMessage());
                header('Location: ../../views/client/cart.php?action=checkout');
                exit();
            }
        } else {
            AuthController::setFlashMessage('error', 'Método de solicitud no permitido.');
            header('Location: ../../views/client/cart.php?action=checkout');
            exit();
        }
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
        case 'checkout':
        default:
            $controller->checkout();
            break;
    }
}
?>
