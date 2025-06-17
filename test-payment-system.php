<?php
// Script de prueba para verificar el sistema de pagos
if (session_status() == PHP_SESSION_NONE) {
    session_start();
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
    echo "<p style='color: green;'>✓ Librería de Stripe está instalada</p>";
} else {
    echo "<p style='color: orange;'>⚠ Librería de Stripe no está instalada. Ejecuta: composer require stripe/stripe-php</p>";
}

echo "<h2>Estado del Sistema</h2>";
echo "<p>Si todos los elementos anteriores muestran ✓, el sistema está listo para funcionar.</p>";
echo "<p><a href='views/client/home.php'>Ir a la página principal</a></p>";
?>
