<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Course.php';

$pageTitle = 'El Profesor Hernan - Aprende Inglés Online';

// Obtener cursos destacados
$courseModel = new Course();
$courses = $courseModel->getAll();
$featuredCourses = array_slice($courses, 0, 6);

// Obtener información del usuario si está logueado
$userDisplayName = 'Usuario';
if (isLoggedIn()) {
    $user = getCurrentUser();
    $userDisplayName = $user['displayName'] ?? $user['email'] ?? 'Usuario';
}
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
            <div class="search-bar">
                <input type="text" placeholder="Buscar cursos..." id="searchInput">
                <i class="fas fa-search"></i>
            </div>
            <nav>
                <ul>
                    <li><a href="/">Inicio</a></li>
                    <li><a href="courses.php">Cursos</a></li>
                    <li><a href="#contact">Contacto</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="profile.php"><?php echo htmlspecialchars($userDisplayName); ?></a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/">Admin</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                    <?php endif; ?>
                </ul>
                <div class="cart" id="cartIcon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge" id="cartBadge">0</span>
                </div>
                <?php if (isLoggedIn()): ?>
                    <div class="logout" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Banner Section -->
    <section class="banner">
        <div class="container">
            <div class="banner-content">
                <h1>Aprende Inglés como Segunda Lengua</h1>
                <p>Educación, bilingüismo y comunicación</p>
                <div class="banner-buttons">
                    <a href="courses.php" class="btn-primary">VER TODOS LOS CURSOS</a>
                    <a href="#courses" class="btn-secondary">EXPLORAR NIVELES</a>
                </div>
            </div>
            <div class="banner-image">
                <div class="image-container">
                    <img src="assets/img/hero-image.png" alt="Profesor enseñando inglés" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses Section -->
    <section class="best-sellers">
        <div class="container">
            <h2>Cursos Más Populares</h2>
            
            <div class="products-grid" id="featuredCourses">
                <?php if (!empty($featuredCourses)): ?>
                    <?php foreach ($featuredCourses as $course): ?>
                        <div class="product-card course-item" data-course-id="<?php echo $course['id']; ?>">
                            <div class="course-image">
                                <?php if (!empty($course['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" onerror="this.parentElement.innerHTML='<div class=\'course-placeholder\'><i class=\'fas fa-book\'></i></div>'">
                                <?php else: ?>
                                    <div class="course-placeholder">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="course-info">
                                <div class="level-badge neon-glow <?php echo strtolower($course['level']); ?>">
                                    <?php echo $course['level']; ?>
                                </div>
                                <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="course-description"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>
                                <div class="course-rating">
                                    <?php 
                                    $rating = round($course['average_rating'] ?? 0);
                                    for ($i = 1; $i <= 5; $i++): 
                                    ?>
                                        <i class="fas fa-star <?php echo $i <= $rating ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                    <span>(<?php echo $course['rating_count'] ?? 0; ?>)</span>
                                </div>
                                <div class="course-price">
                                    $<?php echo number_format($course['price'], 2); ?>
                                </div>
                                <div class="course-actions">
                                    <a href="course.php?id=<?php echo $course['id']; ?>" class="btn-secondary">Ver Detalles</a>
                                    <button class="btn-primary add-to-cart-btn" data-course-id="<?php echo $course['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Añadir al Carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-courses">
                        <i class="fas fa-book-open"></i>
                        <h3>No hay cursos disponibles</h3>
                        <p>Pronto tendremos cursos increíbles para ti.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="view-more">
                <a href="courses.php">Ver Todos los Cursos <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <section class="courses" id="courses">
        <div class="container">
            <h2>Niveles de Inglés</h2>

            <div class="courses-grid">
                <div class="course-card" data-level="A1" onclick="window.location.href='courses.php?level=A1'">
                    <div class="level-badge neon-glow orange">A1</div>
                    <div class="course-icon"><i class="fas fa-book"></i></div>
                    <h3 class="course-title">BÁSICO</h3>
                    <p class="course-subtitle">Nivel Básico</p>
                    <p class="course-price">Desde $55</p>
                </div>
                
                <div class="course-card" data-level="A2" onclick="window.location.href='courses.php?level=A2'">
                    <div class="level-badge neon-glow red">A2</div>
                    <div class="course-icon"><i class="fas fa-comments"></i></div>
                    <h3 class="course-title">PRE INTERMEDIO</h3>
                    <p class="course-subtitle">Nivel Pre Intermedio</p>
                    <p class="course-price">Desde $55</p>
                </div>
                
                <div class="course-card" data-level="B1" onclick="window.location.href='courses.php?level=B1'">
                    <div class="level-badge neon-glow blue">B1</div>
                    <div class="course-icon"><i class="fas fa-pen"></i></div>
                    <h3 class="course-title">INTERMEDIO</h3>
                    <p class="course-subtitle">Nivel Intermedio</p>
                    <p class="course-price">Desde $55</p>
                </div>
                
                <div class="course-card" data-level="B2" onclick="window.location.href='courses.php?level=B2'">
                    <div class="level-badge neon-glow teal">B2</div>
                    <div class="course-icon"><i class="fas fa-microphone"></i></div>
                    <h3 class="course-title">INTERMEDIO ALTO</h3>
                    <p class="course-subtitle">Nivel Intermedio Alto</p>
                    <p class="course-price">Desde $55</p>
                </div>
                
                <div class="course-card" data-level="C1" onclick="window.location.href='courses.php?level=C1'">
                    <div class="level-badge neon-glow purple">C1</div>
                    <div class="course-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3 class="course-title">AVANZADO</h3>
                    <p class="course-subtitle">Nivel Avanzado</p>
                    <p class="course-price">Desde $55</p>
                </div>
            </div>
            
            <div class="view-more">
                <a href="courses.php">EXPLORAR TODOS LOS CURSOS <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Promo Box Section -->
    <section class="promo-box">
        <div class="container">
            <p class="promo-label">OFERTA ESPECIAL</p>
            <h2 class="promo-title">30% OFF</h2>
            <div class="promo-levels">
                <div class="promo-level">
                    <div class="level-badge neon-glow orange">A1</div>
                    <span>Nivel Básico</span>
                </div>
                <div class="promo-level">
                    <div class="level-badge neon-glow red">A2</div>
                    <span>Pre Intermedio</span>
                </div>
                <div class="promo-level">
                    <div class="level-badge neon-glow blue">B1</div>
                    <span>Intermedio</span>
                </div>
                <div class="promo-level">
                    <div class="level-badge neon-glow teal">B2</div>
                    <span>Intermedio Alto</span>
                </div>
                <div class="promo-level">
                    <div class="level-badge neon-glow purple">C1</div>
                    <span>Avanzado</span>
                </div>
            </div>

            <a href="courses.php" class="promo-link">COMPRAR AHORA</a>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="contact-form">
                <h2>ENVÍA TU CONSULTA</h2>
                <form id="contactForm">
                    <div class="form-group">
                        <label for="name">Tu nombre</label>
                        <input type="text" id="name" name="name" placeholder="Nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Tu email</label>
                        <input type="email" id="email" name="email" placeholder="E-mail" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Tu mensaje</label>
                        <textarea id="message" name="message" placeholder="Mensaje" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Enviar</button>
                </form>
            </div>
            <div class="contact-info">
                <div class="info-item">
                    <div class="icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="text">
                        <h3>EMAIL</h3>
                        <p>info@profesorhernan.com</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="text">
                        <h3>TELÉFONO</h3>
                        <p>+57 123 456 789</p>
                        <p>+57 234 567 890</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <div class="logo-circle">
                        <span>EH</span>
                    </div>
                    <p>El Profesor Hernan</p>
                </div>
                <div class="footer-links">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="courses.php">Cursos</a></li>
                        <li><a href="#contact">Contacto</a></li>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contacto</h3>
                    <p><i class="fas fa-envelope"></i> info@profesorhernan.com</p>
                    <p><i class="fas fa-phone"></i> +57 123 456 789</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-newsletter">
                    <h3>Suscríbete</h3>
                    <p>Recibe noticias y ofertas especiales</p>
                    <form id="newsletterForm">
                        <div class="newsletter-input">
                            <input type="email" placeholder="Tu email" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> El Profesor Hernan. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <div class="back-to-top">
        <a href="#"><i class="fas fa-arrow-up"></i></a>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer"></div>

    <!-- Scripts -->
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
        // Elementos del DOM
        const cartBadge = document.getElementById('cartBadge');
        const cartIcon = document.getElementById('cartIcon');
        const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
        const logoutBtn = document.getElementById('logoutBtn');
        const contactForm = document.getElementById('contactForm');
        const newsletterForm = document.getElementById('newsletterForm');
        const backToTopButton = document.querySelector('.back-to-top');
        
        // Función para mostrar toast
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) return;

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <div class="toast-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;

            toastContainer.appendChild(toast);

            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('fade-out');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
        
        // Botón de logout
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                firebase.auth().signOut().then(() => {
                    // Eliminar sesión en el servidor
                    fetch('api/auth.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'logout'
                        })
                    })
                    .then(() => {
                        showToast('Sesión cerrada correctamente', 'success');
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 1000);
                    })
                    .catch(error => {
                        console.error('Error al cerrar sesión:', error);
                        showToast('Error al cerrar sesión', 'error');
                    });
                }).catch((error) => {
                    console.error('Error al cerrar sesión de Firebase:', error);
                    showToast('Error al cerrar sesión', 'error');
                });
            });
        }
        
        // Añadir al carrito
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const courseId = this.getAttribute('data-course-id');
                
                // Verificar si el usuario está autenticado
                const user = firebase.auth().currentUser;
                if (!user) {
                    showToast('Debes iniciar sesión para añadir productos al carrito', 'error');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                    return;
                }
                
                // Mostrar loading
                document.getElementById('loadingOverlay').classList.add('show');
                
                // Simular añadir al carrito (aquí integrarías con tu API)
                setTimeout(() => {
                    document.getElementById('loadingOverlay').classList.remove('show');
                    showToast('¡Curso añadido al carrito!', 'success');
                    
                    // Actualizar contador del carrito
                    const currentCount = parseInt(cartBadge.textContent) || 0;
                    cartBadge.textContent = currentCount + 1;
                }, 1000);
            });
        });
        
        // Ir al carrito
        if (cartIcon) {
            cartIcon.addEventListener('click', function() {
                window.location.href = 'cart.php';
            });
        }
        
        // Formulario de contacto
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const message = document.getElementById('message').value;
                
                if (!name || !email || !message) {
                    showToast('Por favor completa todos los campos', 'error');
                    return;
                }
                
                // Mostrar loading
                document.getElementById('loadingOverlay').classList.add('show');
                
                // Simular envío
                setTimeout(() => {
                    document.getElementById('loadingOverlay').classList.remove('show');
                    showToast('¡Mensaje enviado correctamente!', 'success');
                    contactForm.reset();
                }, 2000);
            });
        }
        
        // Newsletter
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = this.querySelector('input[type="email"]').value;
                
                if (!email) {
                    showToast('Por favor ingresa tu email', 'error');
                    return;
                }
                
                // Mostrar loading
                document.getElementById('loadingOverlay').classList.add('show');
                
                // Simular suscripción
                setTimeout(() => {
                    document.getElementById('loadingOverlay').classList.remove('show');
                    showToast('¡Te has suscrito correctamente!', 'success');
                    newsletterForm.reset();
                }, 2000);
            });
        }
        
        // Botón volver arriba
        if (backToTopButton) {
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.style.display = 'block';
                } else {
                    backToTopButton.style.display = 'none';
                }
            });
            
            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // Actualizar contador del carrito
        function updateCartCount() {
            const user = firebase.auth().currentUser;
            if (user && cartBadge) {
                // Aquí integrarías con tu API para obtener el count real
                // Por ahora mantenemos el valor actual
                console.log('Usuario autenticado, manteniendo count del carrito');
            } else if (cartBadge) {
                cartBadge.textContent = '0';
            }
        }
        
        // Verificar estado de autenticación
        firebase.auth().onAuthStateChanged(function(user) {
            if (user) {
                console.log('Usuario autenticado:', user.email);
                updateCartCount();
            } else {
                console.log('Usuario no autenticado');
                updateCartCount();
            }
        });
    });
    </script>

    <!-- Estilos adicionales para toast -->
    <style>
    #toastContainer {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 3000;
        max-width: 350px;
    }

    .toast {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-bottom: 10px;
        overflow: hidden;
        transform: translateX(100%);
        animation: slideInToast 0.3s ease forwards;
        border-left: 4px solid;
    }

    .toast.success {
        border-left-color: var(--success-color);
    }

    .toast.error {
        border-left-color: var(--error-color);
    }

    .toast.info {
        border-left-color: var(--blue-color);
    }

    .toast-content {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .toast-content i {
        font-size: 18px;
    }

    .toast.success .toast-content i {
        color: var(--success-color);
    }

    .toast.error .toast-content i {
        color: var(--error-color);
    }

    .toast.info .toast-content i {
        color: var(--blue-color);
    }

    .toast-content span {
        flex: 1;
        font-weight: 500;
        color: var(--text-color);
    }

    .toast.fade-out {
        animation: slideOutToast 0.3s ease forwards;
    }

    @keyframes slideInToast {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutToast {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    </style>
</body>
</html>