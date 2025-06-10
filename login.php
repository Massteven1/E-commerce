<?php
require_once __DIR__ . '/config/config.php';

// Redirigir si ya está autenticado
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(SITE_URL . '/admin/index.php');
    } else {
        redirect(SITE_URL . '/index.php');
    }
}

$pageTitle = 'Iniciar Sesión - ' . SITE_NAME;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-circle">
                    <a href="/"><span>EH</span></a>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="courses.php">Cursos</a></li>
                    <li><a href="signup.php">Registrarse</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Login Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Bienvenido de Nuevo</h1>
                    <p>Inicia sesión para continuar tu aprendizaje</p>
                </div>
                
                <div class="auth-form">
                    <div id="error-message" class="error-message" style="display: none;"></div>
                    <div id="success-message" class="success-message" style="display: none;"></div>
                    <div id="loading" class="loading-message" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Iniciando sesión...
                    </div>

                    <div class="social-auth">
                        <button type="button" id="googleLogin" class="social-btn google">
                            <i class="fab fa-google"></i>
                            <span>Continuar con Google</span>
                        </button>
                    </div>

                    <div class="auth-divider">
                        <span>o inicia sesión con email</span>
                    </div>

                    <form id="emailLoginForm">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Ingresa tu email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                                <i class="fas fa-eye toggle-password"></i>
                            </div>
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
                    
                    <div class="auth-footer">
                        <p>¿No tienes una cuenta? <a href="signup.php">Regístrate</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Firebase Scripts -->
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js"></script>
    <script>
    // Configuración de Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyAtCjRAp58m3IewqHWgvwLuxxdIb5026kg",
        authDomain: "e-commerce-elprofehernan.firebaseapp.com",
        databaseURL: "https://e-commerce-elprofehernan-default-rtdb.firebaseio.com",
        projectId: "e-commerce-elprofehernan",
        storageBucket: "e-commerce-elprofehernan.firebasestorage.app",
        messagingSenderId: "769275191194",
        appId: "1:769275191194:web:5546d2aed7bd9e60f56423",
        measurementId: "G-3RGDE75FEY"
    };

    // Inicializar Firebase
    firebase.initializeApp(firebaseConfig);
    
    document.addEventListener('DOMContentLoaded', function() {
        const errorDiv = document.getElementById('error-message');
        const successDiv = document.getElementById('success-message');
        const loadingDiv = document.getElementById('loading');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        function showError(message) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            successDiv.style.display = 'none';
            hideLoading();
        }
        
        function showSuccess(message) {
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            errorDiv.style.display = 'none';
            hideLoading();
        }
        
        function showLoading() {
            loadingDiv.style.display = 'block';
            loadingOverlay.classList.add('show');
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
        }
        
        function hideLoading() {
            loadingDiv.style.display = 'none';
            loadingOverlay.classList.remove('show');
        }

        // Toggle password visibility
        const togglePassword = document.querySelector('.toggle-password');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }

        // Verificar que Firebase esté cargado
        if (typeof firebase === 'undefined') {
            showError('Error: Firebase no se ha cargado correctamente.');
            return;
        }

        console.log('Firebase Auth inicializado correctamente');

        // Inicio de sesión con Google
        document.getElementById('googleLogin').addEventListener('click', function() {
            console.log('Botón de Google clickeado');
            showLoading();
            
            const provider = new firebase.auth.GoogleAuthProvider();
            provider.addScope('email');
            provider.addScope('profile');
            
            firebase.auth().signInWithPopup(provider)
                .then((result) => {
                    console.log('Login exitoso:', result.user);
                    const user = result.user;
                    showSuccess('¡Inicio de sesión exitoso!');
                    handleSuccessfulLogin(user);
                })
                .catch((error) => {
                    console.error('Error de Google Auth:', error);
                    hideLoading();
                    
                    let errorMessage = 'Error al iniciar sesión con Google.';
                    
                    if (error.code === 'auth/popup-closed-by-user') {
                        errorMessage = 'Has cerrado la ventana de inicio de sesión.';
                    } else if (error.code === 'auth/popup-blocked') {
                        errorMessage = 'El navegador ha bloqueado la ventana emergente. Por favor, permite ventanas emergentes para este sitio.';
                    } else if (error.code === 'auth/cancelled-popup-request') {
                        errorMessage = 'La solicitud de inicio de sesión fue cancelada.';
                    } else if (error.code === 'auth/network-request-failed') {
                        errorMessage = 'Error de red. Verifica tu conexión a internet.';
                    }
                    
                    showError(errorMessage);
                });
        });

        // Inicio de sesión con email y contraseña
        document.getElementById('emailLoginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Formulario de email enviado');
            showLoading();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                showError('Por favor completa todos los campos.');
                return;
            }
            
            firebase.auth().signInWithEmailAndPassword(email, password)
                .then((userCredential) => {
                    console.log('Login con email exitoso:', userCredential.user);
                    const user = userCredential.user;
                    showSuccess('¡Inicio de sesión exitoso!');
                    handleSuccessfulLogin(user);
                })
                .catch((error) => {
                    console.error('Error de email auth:', error);
                    hideLoading();
                    let errorMessage = 'Error al iniciar sesión.';
                    
                    switch(error.code) {
                        case 'auth/user-not-found':
                            errorMessage = 'No existe una cuenta con este email.';
                            break;
                        case 'auth/wrong-password':
                            errorMessage = 'Contraseña incorrecta.';
                            break;
                        case 'auth/invalid-email':
                            errorMessage = 'Email inválido.';
                            break;
                        case 'auth/too-many-requests':
                            errorMessage = 'Demasiados intentos. Intenta más tarde.';
                            break;
                        default:
                            errorMessage = error.message;
                    }
                    
                    showError(errorMessage);
                });
        });

        // Recuperar contraseña
        document.getElementById('forgotPassword').addEventListener('click', function(e) {
            e.preventDefault();
            const email = prompt('Ingresa tu email para recuperar la contraseña:');
            if (email) {
                showLoading();
                firebase.auth().sendPasswordResetEmail(email)
                    .then(() => {
                        showSuccess('Se ha enviado un email para restablecer tu contraseña.');
                    })
                    .catch((error) => {
                        let errorMessage = 'Error al enviar el email de recuperación.';
                        
                        if (error.code === 'auth/user-not-found') {
                            errorMessage = 'No existe una cuenta con este email.';
                        } else if (error.code === 'auth/invalid-email') {
                            errorMessage = 'Email inválido.';
                        }
                        
                        showError(errorMessage);
                    });
            }
        });

        function handleSuccessfulLogin(user) {
            console.log('Manejando login exitoso para:', user.email);
            
            // Enviar datos del usuario al servidor para establecer la sesión PHP
            fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'login',
                    uid: user.uid,
                    email: user.email,
                    displayName: user.displayName || user.email.split('@')[0],
                    photoURL: user.photoURL || ''
                })
            })
            .then(response => {
                console.log('Respuesta del servidor:', response);
                return response.json();
            })
            .then(data => {
                console.log('Datos del servidor:', data);
                hideLoading();
                if (data.success) {
                    showSuccess('¡Bienvenido! Redirigiendo...');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php';
                    }, 1500);
                } else {
                    showError('Error del servidor: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error de fetch:', error);
                hideLoading();
                showSuccess('¡Inicio de sesión exitoso! Redirigiendo...');
                // Redirigir de todas formas después de un error de conexión
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            });
        }

        // Escuchar cambios en el estado de autenticación
        firebase.auth().onAuthStateChanged((user) => {
            if (user) {
                console.log("Usuario autenticado:", user.email);
            } else {
                console.log("Usuario no autenticado");
            }
        });
    });
    </script>
</body>
</html>
