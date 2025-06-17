<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Login - El Profesor Hernán</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="../../public/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        .admin-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--purple-color));
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin: 20px 0;
            font-weight: 600;
        }
        .user-info {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-admin {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-admin:hover {
            background-color: var(--purple-color);
            transform: translateY(-2px);
        }
        .btn-logout {
            background-color: #e74c3c;
            color: white;
        }
        .btn-logout:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="success-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <h1>¡Login de Administrador Exitoso!</h1>
        <div class="admin-badge">
            <i class="fas fa-crown"></i> ADMINISTRADOR
        </div>
        
        <p>Has iniciado sesión correctamente como administrador del sistema.</p>
        
        <div class="user-info">
            <h3>Información del Usuario</h3>
            <p><strong>Email:</strong> <span id="userEmail">Cargando...</span></p>
            <p><strong>Nombre:</strong> <span id="userName">Cargando...</span></p>
            <p><strong>UID:</strong> <span id="userUID">Cargando...</span></p>
            <p><strong>Rol:</strong> <span style="color: var(--primary-color); font-weight: 600;">Administrador</span></p>
        </div>
        
        <div class="action-buttons">
            <a href="../admin/index.php?controller=admin&action=dashboard" class="btn btn-admin">
                <i class="fas fa-tachometer-alt"></i> Ir al Panel de Admin
            </a>
            <button id="logoutBtn" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </button>
        </div>
    </div>

    <!-- Firebase Scripts -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="../../auth/firebase-config.js"></script>
    
    <script>
        // Verificar autenticación y mostrar información del usuario
        firebase.auth().onAuthStateChanged((user) => {
            if (user) {
                document.getElementById('userEmail').textContent = user.email || 'No disponible';
                document.getElementById('userName').textContent = user.displayName || 'No disponible';
                document.getElementById('userUID').textContent = user.uid || 'No disponible';
            } else {
                // Si no hay usuario, redirigir al login
                window.location.href = '../../login.php';
            }
        });

        // Funcionalidad de logout
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            try {
                await firebase.auth().signOut();
                window.location.href = '../../login.php';
            } catch (error) {
                console.error('Error al cerrar sesión:', error);
                alert('Error al cerrar sesión. Intenta de nuevo.');
            }
        });
    </script>
</body>
</html>
