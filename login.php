<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: index.php');
    exit();
}
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
    <!-- Header Section -->
    <header>
        <div class="container">
            <div class="logo">
                <div class="logo-circle">
                    <span>ht</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Courses</a></li>
                    <li><a href="#">Sales</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Login Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Welcome Back</h1>
                    <p>Sign in to continue your learning journey</p>
                </div>
                
                <div class="auth-form">
                    <form id="loginForm">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                            <div class="error-message" id="emailError"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                                <i class="fas fa-eye toggle-password"></i>
                            </div>
                            <div class="error-message" id="passwordError"></div>
                        </div>
                        
                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="rememberMe" name="rememberMe">
                                <label for="rememberMe">Remember me</label>
                            </div>
                            <a href="#" class="forgot-password" id="forgotPassword">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="btn-primary btn-full">Sign In</button>
                    </form>
                    
                    <div class="auth-divider">
                        <span>or sign in with</span>
                    </div>
                    
                    <div class="social-auth">
                        <button class="social-btn google" id="googleLogin">
                            <i class="fab fa-google"></i>
                            <span>Google</span>
                        </button>
                        <button class="social-btn facebook" id="facebookLogin">
                            <i class="fab fa-facebook-f"></i>
                            <span>Facebook</span>
                        </button>
                    </div>
                    
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Password Reset Modal -->
    <div class="modal" id="passwordResetModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reset Password</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Enter your email address and we'll send you a link to reset your password.</p>
                <form id="resetPasswordForm">
                    <div class="form-group">
                        <label for="resetEmail">Email</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="resetEmail" name="resetEmail" placeholder="Enter your email" required>
                        </div>
                        <div class="error-message" id="resetEmailError"></div>
                    </div>
                    <button type="submit" class="btn-primary btn-full">Send Reset Link</button>
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
    <script src="auth/auth.js"></script>
</body>
</html>
