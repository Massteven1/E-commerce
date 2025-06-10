<?php
require_once __DIR__ . '/config/config.php';

// Redirigir si ya está autenticado
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    redirect('/');
}

$pageTitle = 'Crear Cuenta - El Profesor Hernan';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php" class="logo-circle">EH</a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="courses.php">Cursos</a></li>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Auth Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Crear una Cuenta</h1>
                    <p>Únete a nuestra comunidad y comienza a aprender inglés</p>
                </div>
                
                <div class="auth-form">
                    <!-- Messages -->
                    <div id="authMessage" style="display: none;"></div>
                    
                    <!-- Social Auth -->
                    <div class="social-auth">
                        <button type="button" class="social-btn google" id="googleSignup">
                            <i class="fab fa-google"></i>
                            Registrarse con Google
                        </button>
                    </div>
                    
                    <div class="auth-divider">
                        <span>o regístrate con email</span>
                    </div>
                    
                    <!-- Signup Form -->
                    <form id="signupForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">Nombre</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="firstName" name="firstName" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Apellido</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="lastName" name="lastName" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" required minlength="6">
                                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                            </div>
                            <div class="password-strength" id="passwordStrength" style="display: none;">
                                <div class="strength-meter">
                                    <div class="strength-segment" id="strength1"></div>
                                    <div class="strength-segment" id="strength2"></div>
                                    <div class="strength-segment" id="strength3"></div>
                                    <div class="strength-segment" id="strength4"></div>
                                </div>
                                <div class="strength-text" id="strengthText"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword">Confirmar Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6">
                                <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="termsAgree" name="termsAgree" required>
                                <label for="termsAgree">
                                    Acepto los <a href="#" target="_blank">términos y condiciones</a> 
                                    y la <a href="#" target="_blank">política de privacidad</a>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="newsletter" name="newsletter">
                                <label for="newsletter">
                                    Quiero recibir noticias y ofertas especiales por email
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-primary btn-full" id="signupBtn">
                            Crear Cuenta
                        </button>
                    </form>
                    
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
    <script src="https://www.gstatic.com/firebasejs/9.15.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.15.0/firebase-auth-compat.js"></script>
    <script>
        // Firebase configuration
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

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();
        const googleProvider = new firebase.auth.GoogleAuthProvider();

        console.log('Firebase initialized for signup');

        // DOM Elements
        const signupForm = document.getElementById('signupForm');
        const googleSignupBtn = document.getElementById('googleSignup');
        const authMessage = document.getElementById('authMessage');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');

        // Password strength checker
        passwordInput.addEventListener('input', checkPasswordStrength);

        function checkPasswordStrength() {
            const password = passwordInput.value;
            const strength = calculatePasswordStrength(password);
            
            if (password.length > 0) {
                passwordStrength.style.display = 'block';
                updateStrengthMeter(strength);
            } else {
                passwordStrength.style.display = 'none';
            }
        }

        function calculatePasswordStrength(password) {
            let score = 0;
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            return score;
        }

        function updateStrengthMeter(strength) {
            const segments = document.querySelectorAll('.strength-segment');
            const strengthText = document.getElementById('strengthText');
            
            segments.forEach(segment => {
                segment.className = 'strength-segment';
            });
            
            const strengthLevels = ['Muy débil', 'Débil', 'Regular', 'Fuerte', 'Muy fuerte'];
            const strengthClasses = ['', 'weak', 'weak', 'medium', 'strong'];
            
            for (let i = 0; i < strength; i++) {
                segments[i].classList.add(strengthClasses[strength]);
            }
            
            strengthText.textContent = strengthLevels[strength] || 'Muy débil';
        }

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            togglePasswordVisibility('password', this);
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            togglePasswordVisibility('confirmPassword', this);
        });

        function togglePasswordVisibility(inputId, toggleIcon) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            toggleIcon.classList.toggle('fa-eye');
            toggleIcon.classList.toggle('fa-eye-slash');
        }

        // Email/Password Signup
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const termsAgree = document.getElementById('termsAgree').checked;

            console.log('Form submitted with:', { firstName, lastName, email });

            // Validation
            if (!firstName || !lastName) {
                showMessage('Por favor completa tu nombre y apellido', 'error');
                return;
            }

            if (password !== confirmPassword) {
                showMessage('Las contraseñas no coinciden', 'error');
                return;
            }

            if (password.length < 6) {
                showMessage('La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }

            if (!termsAgree) {
                showMessage('Debes aceptar los términos y condiciones', 'error');
                return;
            }

            try {
                showLoading(true);
                console.log('Creating user with email:', email);

                // Create user
                const userCredential = await auth.createUserWithEmailAndPassword(email, password);
                const user = userCredential.user;

                console.log('User created successfully:', user.uid);

                // Update profile with name
                await user.updateProfile({
                    displayName: `${firstName} ${lastName}`
                });

                console.log('Profile updated with name:', `${firstName} ${lastName}`);

                // Send email verification
                await user.sendEmailVerification();
                console.log('Verification email sent');

                // Sync with backend
                await syncUserWithBackend(user, firstName, lastName);

                showMessage('¡Cuenta creada exitosamente! Revisa tu email para verificar tu cuenta.', 'success');
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);

            } catch (error) {
                console.error('Signup error:', error);
                showMessage(getAuthErrorMessage(error.code), 'error');
            } finally {
                showLoading(false);
            }
        });

        // Google Signup
        googleSignupBtn.addEventListener('click', async () => {
            try {
                showLoading(true);
                console.log('Starting Google signup');

                const result = await auth.signInWithPopup(googleProvider);
                const user = result.user;

                console.log('Google signup successful:', user.uid);
                console.log('User info:', {
                    email: user.email,
                    displayName: user.displayName,
                    photoURL: user.photoURL
                });

                // Extract name parts
                const displayName = user.displayName || '';
                const nameParts = displayName.split(' ');
                const firstName = nameParts[0] || '';
                const lastName = nameParts.slice(1).join(' ') || '';

                // Sync with backend
                await syncUserWithBackend(user, firstName, lastName);

                showMessage('¡Registro exitoso con Google!', 'success');
                
                // Redirect after 1 second
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);

            } catch (error) {
                console.error('Google signup error:', error);
                console.error('Error code:', error.code);
                console.error('Error message:', error.message);
                
                if (error.code === 'auth/popup-closed-by-user') {
                    showMessage('Registro cancelado', 'error');
                } else if (error.code === 'auth/popup-blocked') {
                    showMessage('Popup bloqueado. Permite popups para este sitio.', 'error');
                } else {
                    showMessage('Error al registrarse con Google: ' + error.message, 'error');
                }
            } finally {
                showLoading(false);
            }
        });

        // Sync user with backend
        async function syncUserWithBackend(user, firstName = '', lastName = '') {
            try {
                console.log('Syncing user with backend:', user.uid);
                
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'login',
                        uid: user.uid,
                        email: user.email,
                        displayName: user.displayName || `${firstName} ${lastName}`.trim(),
                        photoURL: user.photoURL || ''
                    })
                });

                const result = await response.json();
                console.log('Backend sync result:', result);

                if (!response.ok) {
                    console.error('Failed to sync user with backend:', result);
                }
            } catch (error) {
                console.error('Error syncing user:', error);
            }
        }

        // Utility functions
        function showMessage(message, type) {
            authMessage.innerHTML = `<div class="${type}-message">${message}</div>`;
            authMessage.style.display = 'block';
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                authMessage.style.display = 'none';
            }, 5000);
        }

        function showLoading(show) {
            if (show) {
                loadingOverlay.classList.add('show');
            } else {
                loadingOverlay.classList.remove('show');
            }
        }

        function getAuthErrorMessage(errorCode) {
            const errorMessages = {
                'auth/email-already-in-use': 'Ya existe una cuenta con este email',
                'auth/weak-password': 'La contraseña debe tener al menos 6 caracteres',
                'auth/invalid-email': 'Email inválido',
                'auth/operation-not-allowed': 'Operación no permitida',
                'auth/too-many-requests': 'Demasiados intentos. Intenta más tarde'
            };

            return errorMessages[errorCode] || 'Error al crear la cuenta: ' + errorCode;
        }

        // Check if user is already logged in
        auth.onAuthStateChanged((user) => {
            if (user) {
                console.log('User is already logged in:', user.email);
                // Optionally redirect to dashboard
                // window.location.href = 'index.php';
            }
        });
    </script>
</body>
</html>