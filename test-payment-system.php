<?php
// Script de prueba para verificar el sistema de pagos
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar Composer autoloader PRIMERO
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "<p style='color: green;'>✓ Autoloader de Composer cargado correctamente</p>";
} else {
    echo "<p style='color: red;'>✗ No se encontró vendor/autoload.php. Ejecuta: composer install</p>";
}

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/UserCourse.php';
require_once __DIR__ . '/controllers/AuthController.php';

echo "<h1>Prueba del Sistema de Pagos</h1>";

// Verificar conexión a la base de datos
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✓ Conexión a la base de datos exitosa</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error de conexión a la base de datos: " . $e->getMessage() . "</p>";
    exit();
}

// Verificar modelos
try {
    $orderModel = new Order($db);
    echo "<p style='color: green;'>✓ Modelo Order creado correctamente</p>";
    
    $userCourseModel = new UserCourse($db);
    echo "<p style='color: green;'>✓ Modelo UserCourse creado correctamente</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error al crear modelos: " . $e->getMessage() . "</p>";
}

// Verificar métodos del modelo Order
try {
    $testUserId = 1; // ID de usuario de prueba
    $orders = $orderModel->readByUserId($testUserId);
    echo "<p style='color: green;'>✓ Método readByUserId funciona correctamente</p>";
    echo "<p>Pedidos encontrados: " . count($orders) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en readByUserId: " . $e->getMessage() . "</p>";
}

// Verificar métodos del modelo UserCourse
try {
    $testUserId = 1; // ID de usuario de prueba
    $courses = $userCourseModel->getPurchasedPlaylistsByUserId($testUserId);
    echo "<p style='color: green;'>✓ Método getPurchasedPlaylistsByUserId funciona correctamente</p>";
    echo "<p>Cursos encontrados: " . count($courses) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en getPurchasedPlaylistsByUserId: " . $e->getMessage() . "</p>";
}

// Verificar AuthController
try {
    AuthController::setFlashMessage('success', 'Mensaje de prueba');
    $message = AuthController::getFlashMessage();
    if ($message && $message['message'] === 'Mensaje de prueba') {
        echo "<p style='color: green;'>✓ AuthController::setFlashMessage funciona correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ Error en AuthController::setFlashMessage</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en AuthController: " . $e->getMessage() . "</p>";
}

// Verificar Stripe (si está instalado)
if (class_exists('\Stripe\Stripe')) {
    echo "<p style='color: green;'>✓ Librería de Stripe está instalada y disponible</p>";
    
    // Verificar que se puede inicializar Stripe
    try {
        \Stripe\Stripe::setApiKey('sk_test_dummy_key_for_testing');
        echo "<p style='color: green;'>✓ Stripe se puede inicializar correctamente</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Error al inicializar Stripe: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Librería de Stripe no está disponible</p>";
    echo "<p>Verifica que:</p>";
    echo "<ul>";
    echo "<li>El archivo vendor/autoload.php existe</li>";
    echo "<li>La carpeta vendor/stripe/ existe</li>";
    echo "<li>Ejecutaste 'composer require stripe/stripe-php'</li>";
    echo "</ul>";
}

// Verificar PaymentController
try {
    require_once __DIR__ . '/controllers/PaymentController.php';
    $paymentController = new PaymentController();
    echo "<p style='color: green;'>✓ PaymentController se puede instanciar correctamente</p>";
    
    $publishableKey = $paymentController->getPublishableKey();
    if (!empty($publishableKey) && strpos($publishableKey, 'pk_test_') === 0) {
        echo "<p style='color: green;'>✓ Clave publicable de Stripe configurada correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ Clave publicable de Stripe no configurada o incorrecta</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error al crear PaymentController: " . $e->getMessage() . "</p>";
}

// Verificar estructura de directorios para uploads
$uploadDirs = [
    'public/uploads/images/',
    'public/uploads/videos/',
    'public/uploads/thumbnails/'
];

foreach ($uploadDirs as $dir) {
    if (is_dir(__DIR__ . '/' . $dir) && is_writable(__DIR__ . '/' . $dir)) {
        echo "<p style='color: green;'>✓ Directorio $dir existe y es escribible</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Directorio $dir no existe o no es escribible</p>";
    }
}

echo "<h2>Estado del Sistema</h2>";
echo "<p>Si todos los elementos anteriores muestran ✓, el sistema está listo para funcionar.</p>";
echo "<p><a href='views/client/home.php'>Ir a la página principal</a></p>";
echo "<p><a href='views/client/all-courses.php'>Ver todos los cursos</a></p>";
echo "<p><a href='login.php'>Iniciar sesión</a></p>";
?>
