<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si ya está logueado
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: views/admin/index.php?controller=admin&action=dashboard');
    } else {
        header('Location: views/client/home.php');
    }
    exit();
}

// Procesar login si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'controllers/AuthController.php';
    $authController = new AuthController();
    $authController->login();
}

// Obtener mensaje flash
require_once 'controllers/AuthController.php';
$flash_message = AuthController::getFlashMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - English Learning Platform</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Login Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Bienvenido de Vuelta</h1>
                    <p>Inicia sesión para continuar tu aprendizaje</p>
                </div>
                
                <?php if ($flash_message): ?>
                    <div class="flash-message <?php echo $flash_message['type']; ?>">
                        <?php echo $flash_message['message']; ?>
                    </div>
                <?php endif; ?>
                
                <div class="auth-form">
                    <form id="loginForm" method="POST">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Ingresa tu email" required>
                            </div>
                            <div class="error-message" id="emailError"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <div class="input-with-icon">
                                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                                <i class="fas fa-eye toggle-password"></i>
                            </div>
                            <div class="error-message" id="passwordError"></div>
                        </div>
                        
                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="rememberMe" name="rememberMe">
                                <label for="rememberMe">Recordarme</label>
                            </div>
                            <a href="#" class="forgot-password" id="forgotPassword">¿Olvidaste tu contraseña?</a>
                        </div>
                        
                        <button type="submit" class="btn-primary btn-full">Iniciar Sesión</button>
                    </form>
                    
                    <div class="auth-divider">
                        <span>o inicia sesión con</span>
                    </div>
                    
                    <div class="social-auth">
                        <button class="social-btn google" id="googleLogin">
                            <i class="fab fa-google"></i>
                            <span>Google</span>
                        </button>
                    </div>
                    
                    <div class="auth-footer">
                        <p>¿No tienes una cuenta? <a href="signup.php">Regístrate</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Password Reset Modal -->
    <div class="modal" id="passwordResetModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Restablecer Contraseña</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.</p>
                <form id="resetPasswordForm">
                    <div class="form-group">
                        <label for="resetEmail">Email</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="resetEmail" name="resetEmail" placeholder="Ingresa tu email" required>
                        </div>
                        <div class="error-message" id="resetEmailError"></div>
                    </div>
                    <button type="submit" class="btn-primary btn-full">Enviar Enlace</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
    
    <!-- Firebase Scripts -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>

    <script src="auth/firebase-config.js"></script>
    <script>
        // Elementos del DOM
        const loadingOverlay = document.getElementById('loadingOverlay');
        const googleLoginBtn = document.getElementById('googleLogin');
        const forgotPasswordLink = document.getElementById('forgotPassword');
        const passwordResetModal = document.getElementById('passwordResetModal');
        const closeModalBtn = document.querySelector('.close-modal');

        // Proveedores de Firebase
        const googleProvider = new firebase.auth.GoogleAuthProvider();

        // Mostrar/ocultar contraseña
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', () => {
                const input = icon.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Función para mostrar loading
        function showLoading() {
            loadingOverlay.classList.add('show');
        }

        // Función para ocultar loading
        function hideLoading() {
            loadingOverlay.classList.remove('show');
        }

        // Login con Google
        googleLoginBtn.addEventListener('click', async () => {
            showLoading();
            try {
                const result = await firebase.auth().signInWithPopup(googleProvider);
                const user = result.user;
                
                // Enviar datos al servidor
                const response = await fetch('google-login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        firebase_uid: user.uid,
                        email: user.email,
                        first_name: user.displayName ? user.displayName.split(' ')[0] : '',
                        last_name: user.displayName ? user.displayName.split(' ').slice(1).join(' ') : ''
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.error || 'Error al iniciar sesión con Google');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al iniciar sesión con Google');
            } finally {
                hideLoading();
            }
        });

        // Modal de restablecimiento de contraseña
        forgotPasswordLink.addEventListener('click', (e) => {
            e.preventDefault();
            passwordResetModal.classList.add('show');
        });

        closeModalBtn.addEventListener('click', () => {
            passwordResetModal.classList.remove('show');
        });

        passwordResetModal.addEventListener('click', (e) => {
            if (e.target === passwordResetModal) {
                passwordResetModal.classList.remove('show');
            }
        });
    </script>
</body>
</html>
