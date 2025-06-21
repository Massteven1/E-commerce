<?php
// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Incluir el controlador de autenticación
require_once 'controllers/AuthController.php';

use Controllers\AuthController; // Usar namespace

// Redirigir si ya está logueado
if (AuthController::isAuthenticated()) {
  if (AuthController::isAdmin()) {
      header('Location: views/admin/index.php?controller=admin&action=dashboard');
  } else {
      header('Location: views/client/home.php');
  }
  exit();
}

// Procesar registro si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $authController = new AuthController();
        $authController->register();
    } catch (\Exception $e) {
        error_log("Error en signup.php: " . $e->getMessage());
        AuthController::setFlashMessage('error', 'Error interno del servidor. Intenta de nuevo.');
    }
}

// Obtener mensaje flash
$flash_message = AuthController::getFlashMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro - English Learning Platform</title>
  <link rel="stylesheet" href="public/css/styles.css">
  <link rel="stylesheet" href="public/css/auth.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <!-- Signup Section -->
  <section class="auth-section">
      <div class="container">
          <div class="auth-container">
              <div class="auth-header">
                  <h1>Crear una Cuenta</h1>
                  <p>Únete a nuestra comunidad y comienza a aprender inglés hoy</p>
              </div>
              
              <?php if ($flash_message): ?>
                  <div class="flash-message <?php echo $flash_message['type']; ?>">
                      <?php echo $flash_message['message']; ?>
                  </div>
              <?php endif; ?>
              
              <div class="auth-form">
                  <form id="signupForm" method="POST">
                      <div class="form-row">
                          <div class="form-group">
                              <label for="firstName">Nombre</label>
                              <div class="input-with-icon">
                                  <i class="fas fa-user"></i>
                                  <input type="text" id="firstName" name="first_name" placeholder="Ingresa tu nombre" required>
                              </div>
                              <div class="error-message" id="firstNameError"></div>
                          </div>
                          
                          <div class="form-group">
                              <label for="lastName">Apellido</label>
                              <div class="input-with-icon">
                                  <i class="fas fa-user"></i>
                                  <input type="text" id="lastName" name="last_name" placeholder="Ingresa tu apellido" required>
                              </div>
                              <div class="error-message" id="lastNameError"></div>
                          </div>
                      </div>
                      
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
                              <input type="password" id="password" name="password" placeholder="Crea una contraseña" required>
                              <i class="fas fa-eye toggle-password"></i>
                          </div>
                          <div class="password-strength" id="passwordStrength">
                              <div class="strength-meter">
                                  <div class="strength-segment"></div>
                                  <div class="strength-segment"></div>
                                  <div class="strength-segment"></div>
                                  <div class="strength-segment"></div>
                              </div>
                              <span class="strength-text">Fuerza de la contraseña</span>
                          </div>
                          <div class="error-message" id="passwordError"></div>
                      </div>
                      
                      <div class="form-group">
                          <label for="confirmPassword">Confirmar Contraseña</label>
                          <div class="input-with-icon">
                              <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirma tu contraseña" required>
                              <i class="fas fa-eye toggle-password"></i>
                          </div>
                          <div class="error-message" id="confirmPasswordError"></div>
                      </div>
                      
                      <div class="form-group checkbox-group">
                          <input type="checkbox" id="termsAgree" name="termsAgree" required>
                          <label for="termsAgree">Acepto los <a href="#">Términos de Servicio</a> y <a href="#">Política de Privacidad</a></label>
                          <div class="error-message" id="termsAgreeError"></div>
                      </div>
                      
                      <button type="submit" class="btn-primary btn-full">Crear Cuenta</button>
                  </form>
                  
                  <div class="auth-divider">
                      <span>o regístrate con</span>
                  </div>
                  
                  <div class="social-auth">
                      <button class="social-btn google" id="googleSignup">
                          <i class="fab fa-google"></i>
                          <span>Google</span>
                      </button>
                  </div>
                  
                  <div class="auth-footer">
                      <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
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
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>

  <script src="auth/firebase-config.js"></script>
  <script>
      // Elementos del DOM
      const loadingOverlay = document.getElementById('loadingOverlay');
      const googleSignupBtn = document.getElementById('googleSignup');
      const passwordInput = document.getElementById('password');
      const passwordStrength = document.getElementById('passwordStrength');
      const strengthSegments = passwordStrength.querySelectorAll('.strength-segment');
      const strengthText = passwordStrength.querySelector('.strength-text');

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

      // Verificador de fuerza de contraseña
      function checkPasswordStrength(password) {
          let strength = 0;

          if (password.length >= 8) strength += 1;
          if (/[a-z]/.test(password)) strength += 1;
          if (/[A-Z]/.test(password)) strength += 1;
          if (/[0-9]/.test(password)) strength += 1;
          if (/[^a-zA-Z0-9]/.test(password)) strength += 1;

          strengthSegments.forEach((segment, index) => {
              segment.className = 'strength-segment';
              if (index < strength) {
                  if (strength <= 2) {
                      segment.classList.add('weak');
                  } else if (strength <= 3) {
                      segment.classList.add('medium');
                  } else {
                      segment.classList.add('strong');
                  }
              }
          });

          if (password.length === 0) {
              strengthText.textContent = 'Fuerza de la contraseña';
          } else if (strength <= 2) {
              strengthText.textContent = 'Contraseña débil';
          } else if (strength <= 3) {
              strengthText.textContent = 'Contraseña media';
          } else {
              strengthText.textContent = 'Contraseña fuerte';
          }

          return strength;
      }

      passwordInput.addEventListener('input', () => {
          checkPasswordStrength(passwordInput.value);
      });

      // Función para mostrar loading
      function showLoading() {
          loadingOverlay.classList.add('show');
      }

      // Función para ocultar loading
      function hideLoading() {
          loadingOverlay.classList.remove('show');
      }

      // Registro con Google
      googleSignupBtn.addEventListener('click', async () => {
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
                  alert(data.error || 'Error al registrarse con Google');
              }
          } catch (error) {
              console.error('Error:', error);
              alert('Error al registrarse con Google');
          } finally {
              hideLoading();
          }
      });
  </script>
</body>
</html>
