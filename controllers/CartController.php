<?php
// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir las clases necesarias
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Playlist.php';

class CartController {
    private $db;
    private $playlistModel;
    private $promo_codes = [
        'VERANO10' => 0.10, // 10% de descuento
        'EDUCACION25' => 0.25, // 25% de descuento
        'PROFE50' => 0.50, // 50% de descuento
        'NUEVOUSUARIO' => 0.15, // 15% de descuento
        'AHORRAHOY' => 0.20  // 20% de descuento
    ];

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->playlistModel = new Playlist($this->db);

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

    public function add($playlist_id) {
        $playlist = $this->playlistModel->readOne($playlist_id);

        if ($playlist) {
            // Si la playlist ya está en el carrito, no hacer nada (ya que solo se puede comprar una vez)
            if (!isset($_SESSION['cart'][$playlist_id])) {
                // Solo añadir si no está ya en el carrito
                $_SESSION['cart'][$playlist_id] = [
                    'id' => $playlist['id'],
                    'name' => $playlist['name'],
                    'price' => $playlist['price'],
                    'cover_image' => $playlist['cover_image'],
                    'quantity' => 1 // Siempre será 1
                ];
            }
        }
        // Redirigir de vuelta a la página principal o a la vista del carrito
        $this->redirect('cart', 'view');
    }

    public function remove($playlist_id) {
        if (isset($_SESSION['cart'][$playlist_id])) {
            unset($_SESSION['cart'][$playlist_id]);
        }
        $this->redirect('cart', 'view');
    }

    // Eliminar método updateQuantity ya que no se necesita

    public function applyPromoCode($code) {
        $code = strtoupper(trim($code)); // Normalizar el código (mayúsculas, sin espacios)

        if (array_key_exists($code, $this->promo_codes)) {
            $_SESSION['promo_code_applied'] = $code;
            $_SESSION['promo_discount_rate'] = $this->promo_codes[$code];
            $_SESSION['promo_message'] = 'Código "' . htmlspecialchars($code) . '" aplicado con éxito.';
        } else {
            $_SESSION['promo_code_applied'] = null;
            $_SESSION['promo_discount_rate'] = 0;
            $_SESSION['promo_message'] = 'Código promocional inválido o expirado.';
        }
        $this->redirect('cart', 'view');
    }

    public function view() {
        $cart_items = $_SESSION['cart'];
        $totals = $this->calculateTotals($cart_items);
        
        // Para la sección "También te podría gustar"
        $recommended_playlists = $this->playlistModel->readAll();
        // Opcional: filtrar o seleccionar aleatoriamente algunas para recomendación
        shuffle($recommended_playlists); // Mezclar para mostrar diferentes cada vez
        $recommended_playlists = array_slice($recommended_playlists, 0, 2); // Tomar solo 2

        // Pasar el mensaje del código promocional a la vista
        $promo_message = $_SESSION['promo_message'];
        // Limpiar el mensaje después de mostrarlo una vez
        $_SESSION['promo_message'] = ''; 

        require_once __DIR__ . '/../views/client/cart.php';
    }

    public function checkout() {
        // Verificar que hay items en el carrito
        if (empty($_SESSION['cart'])) {
            $this->redirect('cart', 'view');
            return;
        }

        $cart_items = $_SESSION['cart'];
        $totals = $this->calculateTotals($cart_items);
        
        require_once __DIR__ . '/../views/client/checkout.php';
    }

    private function calculateTotals($items) {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price']; // Ya no multiplicamos por cantidad
        }

        $discount_rate = $_SESSION['promo_discount_rate'] ?? 0; // Usar la tasa de descuento del código promocional
        $tax_rate = 0.07; // 7% de impuesto

        $discount = $subtotal * $discount_rate;
        $tax = ($subtotal - $discount) * $tax_rate;
        $total = $subtotal - $discount + $tax;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'promo_code_applied' => $_SESSION['promo_code_applied'] // Para mostrar qué código se aplicó
        ];
    }

    private function redirect($controller, $action, $id = null, $extra_param = null) {
        $url = "index.php?controller={$controller}&action={$action}";
        if ($id) $url .= "&id={$id}";
        if ($extra_param) $url .= "&param={$extra_param}";
        header("Location: {$url}");
        exit();
    }
}
?>
