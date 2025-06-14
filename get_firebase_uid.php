<?php
// Script auxiliar para obtener el UID de Firebase después del login
// Solo para propósitos de desarrollo - ELIMINAR EN PRODUCCIÓN

session_start();

if (isset($_SESSION['firebase_uid'])) {
    echo "<h2>Información de Usuario Actual</h2>";
    echo "<p><strong>Firebase UID:</strong> " . htmlspecialchars($_SESSION['firebase_uid']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($_SESSION['user_email'] ?? 'No disponible') . "</p>";
    echo "<p><strong>Rol:</strong> " . htmlspecialchars($_SESSION['user_role'] ?? 'No disponible') . "</p>";
    echo "<p><strong>ID Local:</strong> " . htmlspecialchars($_SESSION['user_id'] ?? 'No disponible') . "</p>";
    
    echo "<hr>";
    echo "<h3>Para actualizar el usuario administrador:</h3>";
    echo "<p>Ejecuta esta consulta SQL:</p>";
    echo "<code>UPDATE users SET firebase_uid = '" . htmlspecialchars($_SESSION['firebase_uid']) . "' WHERE email = 'admin@ecommerce.com';</code>";
    
    echo "<hr>";
    echo "<a href='logout.php'>Cerrar Sesión</a> | <a href='index.php'>Ir al Inicio</a>";
} else {
    echo "<h2>No hay sesión activa</h2>";
    echo "<p><a href='login.html'>Iniciar Sesión</a></p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    code { background: #f4f4f4; padding: 10px; display: block; margin: 10px 0; border-radius: 4px; }
    h2, h3 { color: #333; }
</style>
