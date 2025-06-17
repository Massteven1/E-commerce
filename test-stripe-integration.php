<?php
// Script específico para probar la integración de Stripe
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar Composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    die("Error: No se encontró vendor/autoload.php. Ejecuta: composer install");
}

echo "<h1>Prueba de Integración de Stripe</h1>";

// Verificar que Stripe esté disponible
if (!class_exists('\Stripe\Stripe')) {
    die("<p style='color: red;'>Error: La clase Stripe no está disponible. Verifica la instalación.</p>");
}

echo "<p style='color: green;'>✓ Clase Stripe disponible</p>";

// Configurar Stripe con las claves de prueba
$stripeSecretKey = 'sk_test_51Ra0H5EIqBlXSQrI4Fjzj7GA4NUPM6OeG7FvxVqIySnQyJGByfodso8s5UVvTmw2LzwDOsvMf3FN6fzucdxjbDZY00rYqJgkYV';
$stripePublishableKey = 'pk_test_51Ra0H5EIqBlXSQrI3JkmyctAonU9YzM9ezg1w2kmBFuTKZxEDRudatwcoRHzAEbH2vx1AUbZyPt4loE7qSNwhnrD00LR7GSHXF';

try {
    \Stripe\Stripe::setApiKey($stripeSecretKey);
    echo "<p style='color: green;'>✓ Stripe inicializado con clave secreta</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error al inicializar Stripe: " . $e->getMessage() . "</p>";
    exit();
}

// Probar crear un cargo de prueba
// Usamos un token de prueba (tok_visa) que simula una tarjeta tokenizada por el frontend.
// Esto es la forma correcta de probar la capacidad de tu backend para procesar cargos.
try {
    $charge = \Stripe\Charge::create([
        'amount' => 1000, // $10.00 en centavos
        'currency' => 'usd',
        'description' => 'Prueba de cargo',
        'source' => 'tok_visa', // Token de prueba de Stripe
    ]);
    
    echo "<p style='color: green;'>✓ Cargo de prueba creado exitosamente</p>";
    echo "<p>ID del cargo: " . $charge->id . "</p>";
    echo "<p>Estado: " . $charge->status . "</p>";
    echo "<p>Monto: $" . ($charge->amount / 100) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error al crear cargo: " . $e->getMessage() . "</p>";
}

echo "<h2>Información de Configuración</h2>";
echo "<p><strong>Clave Publicable:</strong> " . $stripePublishableKey . "</p>";
echo "<p><strong>Versión de Stripe PHP:</strong> " . \Stripe\Stripe::VERSION . "</p>";

echo "<h2>Tarjetas de Prueba de Stripe</h2>";
echo "<ul>";
echo "<li><strong>Visa:</strong> 4242424242424242</li>";
echo "<li><strong>Visa (debit):</strong> 4000056655665556</li>";
echo "<li><strong>Mastercard:</strong> 5555555555554444</li>";
echo "<li><strong>American Express:</strong> 378282246310005</li>";
echo "<li><strong>Declined:</strong> 4000000000000002</li>";
echo "</ul>";

echo "<p><a href='test-payment-system.php'>Volver a la prueba general del sistema</a></p>";
?>
