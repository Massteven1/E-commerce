<?php
session_start();

if (isset($_GET['idToken'])) {
    $idToken = $_GET['idToken'];
    
    // Decodificar el token básico (NO es seguro para producción)
    $parts = explode('.', $idToken);
    if (count($parts) === 3) {
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $decodedToken = json_decode($payload, true);
        
        if ($decodedToken && isset($decodedToken['sub'], $decodedToken['email'])) {
            $firebaseUid = $decodedToken['sub'];
            $userEmail = $decodedToken['email'];
            
            // Establecer sesión
            $_SESSION['firebase_uid'] = $firebaseUid;
            $_SESSION['user_email'] = $userEmail;
            
            // Determinar rol
            $adminEmails = ['admin@ecommerce.com', 'admin@elprofehernan.com'];
            if (in_array($userEmail, $adminEmails)) {
                $_SESSION['user_role'] = 'admin';
                header('Location: views/admin/courses.php');
            } else {
                $_SESSION['user_role'] = 'client';
                header('Location: index.php');
            }
            exit();
        }
    }
}

die("Error en la autenticación");
?>
