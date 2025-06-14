<?php
session_start();
require_once __DIR__ . '/config/Database.php';

// Función para decodificar JWT (simplificada, sin verificación de firma)
function decodeJwt($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false; // No es un JWT válido
    }
    $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
    return json_decode($payload, true);
}

if (isset($_GET['idToken'])) {
    $idToken = $_GET['idToken'];
    $decodedToken = decodeJwt($idToken);

    if ($decodedToken && isset($decodedToken['sub'])) {
        $firebaseUid = $decodedToken['sub']; // 'sub' es el UID del usuario en Firebase
        $userEmail = $decodedToken['email'] ?? null; // El email del usuario

        $database = new Database();
        $db = $database->getConnection();

        // Buscar usuario en la base de datos local
        $query = "SELECT id, role FROM users WHERE firebase_uid = ? LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$firebaseUid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Usuario existente, establecer sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firebase_uid'] = $firebaseUid;
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $userEmail; // Guardar email también
            
            // Redirigir según el rol
            if ($user['role'] === 'admin') {
                header('Location: views/admin/courses.php?controller=admin&action=dashboard');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            // Nuevo usuario o usuario no registrado en la BD local
            // Registrar el usuario en la base de datos local con un rol por defecto
            $defaultRole = 'client'; // Rol por defecto para nuevos registros
            
            // Puedes añadir más campos si tu tabla 'users' los requiere (ej. nombre, apellido)
            $insertQuery = "INSERT INTO users (firebase_uid, email, role, created_at) VALUES (?, ?, ?, NOW())";
            $insertStmt = $db->prepare($insertQuery);
            
            if ($insertStmt->execute([$firebaseUid, $userEmail, $defaultRole])) {
                $newUserId = $db->lastInsertId();
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['firebase_uid'] = $firebaseUid;
                $_SESSION['user_role'] = $defaultRole;
                $_SESSION['user_email'] = $userEmail;

                header('Location: index.php'); // Redirigir a la página de inicio para clientes
                exit();
            } else {
                // Error al insertar en la BD
                error_log("Error al registrar nuevo usuario en la BD: " . implode(" ", $insertStmt->errorInfo()));
                header('Location: login.html?error=db_register_failed');
                exit();
            }
        }
    } else {
        // Token inválido o no decodificable
        header('Location: login.html?error=invalid_token');
        exit();
    }
} else {
    // No se proporcionó ID Token
    header('Location: login.html?error=no_token');
    exit();
}
?>
