<?php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$firebaseUID = getCurrentUserUID();

if (!$firebaseUID) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no válido']);
    exit;
}

// Conectar a la base de datos
$database = new Database();
$conn = $database->getConnection();

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($conn, $firebaseUID, $action);
            break;
        case 'POST':
            handlePostRequest($conn, $firebaseUID, $action);
            break;
        case 'PUT':
            handlePutRequest($conn, $firebaseUID);
            break;
        case 'DELETE':
            handleDeleteRequest($conn, $firebaseUID);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    error_log('Cart API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

/**
 * Manejar solicitudes GET
 */
function handleGetRequest($conn, $firebaseUID, $action) {
    if ($action === 'count') {
        $count = getCartItemCount($conn, $firebaseUID);
        echo json_encode(['success' => true, 'count' => $count]);
    } elseif ($action === 'total') {
        $total = getCartTotal($conn, $firebaseUID);
        echo json_encode(['success' => true, 'total' => $total]);
    } else {
        $items = getCartItems($conn, $firebaseUID);
        $total = getCartTotal($conn, $firebaseUID);
        echo json_encode([
            'success' => true, 
            'items' => $items, 
            'total' => $total,
            'count' => count($items)
        ]);
    }
}

/**
 * Manejar solicitudes POST
 */
function handlePostRequest($conn, $firebaseUID, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'add') {
        if (!isset($input['course_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID del curso requerido']);
            return;
        }
        
        $courseId = (int)$input['course_id'];
        $quantity = (int)($input['quantity'] ?? 1);
        
        // Verificar que el curso existe
        if (!courseExists($conn, $courseId)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Curso no encontrado']);
            return;
        }
        
        // Verificar si ya está en el carrito
        if (isItemInCart($conn, $firebaseUID, $courseId)) {
            echo json_encode(['success' => false, 'message' => 'El curso ya está en tu carrito']);
            return;
        }
        
        $success = addItemToCart($conn, $firebaseUID, $courseId, $quantity);
        
        if ($success) {
            $count = getCartItemCount($conn, $firebaseUID);
            $total = getCartTotal($conn, $firebaseUID);
            echo json_encode([
                'success' => true, 
                'message' => 'Curso añadido al carrito',
                'count' => $count,
                'total' => $total
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al añadir al carrito']);
        }
        
    } elseif ($action === 'remove') {
        if (!isset($input['course_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID del curso requerido']);
            return;
        }
        
        $courseId = (int)$input['course_id'];
        $success = removeItemFromCart($conn, $firebaseUID, $courseId);
        
        if ($success) {
            $count = getCartItemCount($conn, $firebaseUID);
            $total = getCartTotal($conn, $firebaseUID);
            echo json_encode([
                'success' => true, 
                'message' => 'Curso eliminado del carrito',
                'count' => $count,
                'total' => $total
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al eliminar del carrito']);
        }
        
    } elseif ($action === 'update') {
        if (!isset($input['course_id']) || !isset($input['quantity'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID del curso y cantidad requeridos']);
            return;
        }
        
        $courseId = (int)$input['course_id'];
        $quantity = (int)$input['quantity'];
        
        if ($quantity <= 0) {
            // Si la cantidad es 0 o negativa, eliminar el item
            $success = removeItemFromCart($conn, $firebaseUID, $courseId);
        } else {
            $success = updateCartItemQuantity($conn, $firebaseUID, $courseId, $quantity);
        }
        
        if ($success) {
            $count = getCartItemCount($conn, $firebaseUID);
            $total = getCartTotal($conn, $firebaseUID);
            echo json_encode([
                'success' => true, 
                'message' => 'Carrito actualizado',
                'count' => $count,
                'total' => $total
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el carrito']);
        }
        
    } elseif ($action === 'clear') {
        $success = clearCart($conn, $firebaseUID);
        
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => 'Carrito vaciado',
                'count' => 0,
                'total' => 0
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al vaciar el carrito']);
        }
    }
}

/**
 * Obtener items del carrito
 */
function getCartItems($conn, $firebaseUID) {
    $query = "SELECT ci.*, c.title, c.description, c.price, c.image_url, c.level 
              FROM cart_items ci 
              JOIN courses c ON ci.course_id = c.id 
              WHERE ci.firebase_uid = ? AND c.is_active = 1
              ORDER BY ci.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$firebaseUID]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener cantidad de items en el carrito
 */
function getCartItemCount($conn, $firebaseUID) {
    $query = "SELECT COALESCE(SUM(quantity), 0) as count 
              FROM cart_items ci 
              JOIN courses c ON ci.course_id = c.id 
              WHERE ci.firebase_uid = ? AND c.is_active = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$firebaseUID]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$result['count'];
}

/**
 * Obtener total del carrito
 */
function getCartTotal($conn, $firebaseUID) {
    $query = "SELECT COALESCE(SUM(c.price * ci.quantity), 0) as total 
              FROM cart_items ci 
              JOIN courses c ON ci.course_id = c.id 
              WHERE ci.firebase_uid = ? AND c.is_active = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$firebaseUID]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float)$result['total'];
}

/**
 * Verificar si un curso existe
 */
function courseExists($conn, $courseId) {
    $query = "SELECT id FROM courses WHERE id = ? AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute([$courseId]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Verificar si un item ya está en el carrito
 */
function isItemInCart($conn, $firebaseUID, $courseId) {
    $query = "SELECT id FROM cart_items WHERE firebase_uid = ? AND course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$firebaseUID, $courseId]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Añadir item al carrito
 */
function addItemToCart($conn, $firebaseUID, $courseId, $quantity) {
    $query = "INSERT INTO cart_items (firebase_uid, course_id, quantity, created_at) 
              VALUES (?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($query);
    return $stmt->execute([$firebaseUID, $courseId, $quantity]);
}

/**
 * Eliminar item del carrito
 */
function removeItemFromCart($conn, $firebaseUID, $courseId) {
    $query = "DELETE FROM cart_items WHERE firebase_uid = ? AND course_id = ?";
    $stmt = $conn->prepare($query);
    return $stmt->execute([$firebaseUID, $courseId]);
}

/**
 * Actualizar cantidad de un item
 */
function updateCartItemQuantity($conn, $firebaseUID, $courseId, $quantity) {
    $query = "UPDATE cart_items SET quantity = ? WHERE firebase_uid = ? AND course_id = ?";
    $stmt = $conn->prepare($query);
    return $stmt->execute([$quantity, $firebaseUID, $courseId]);
}

/**
 * Vaciar carrito
 */
function clearCart($conn, $firebaseUID) {
    $query = "DELETE FROM cart_items WHERE firebase_uid = ?";
    $stmt = $conn->prepare($query);
    return $stmt->execute([$firebaseUID]);
}
?>