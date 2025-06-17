<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar dependencias para mostrar algunos cursos
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Playlist.php';

$database = new Database();
$db = $database->getConnection();
$playlistModel = new Playlist($db);

// Obtener algunos cursos para mostrar (máximo 6)
$playlists = $playlistModel->readAll();
$featured_playlists = array_slice($playlists, 0, 6);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Profesor Hernán - Aprende Inglés Online</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="public/img/logo-profe-hernan.png" alt="El Profesor Hernán" style="height: 40px;">
                <span>El Profesor Hernán</span>
            </div>
            
            <nav class="nav">
                <ul>
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#cursos">Cursos</a></li>
                    <li><a href="#sobre-nosotros">Sobre Nosotros</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ul>
            </nav>
            
            <div class="auth-links">
                <a href="login.php" class="btn-login">Iniciar Sesión</a>
                <a href="signup.php" class="btn-signup">Registrarse</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="inicio" class="banner">
        <div class="container">
            <div class="banner-content">
                <div class="banner-text">
                    <h1>Aprende Inglés con el Profesor Hernán</h1>
                    <p>Domina el inglés con nuestros cursos especializados. Desde nivel básico hasta avanzado, te acompañamos en tu camino hacia la fluidez.</p>
                    <div class="banner-buttons">
                        <a href="login.php" class="btn-primary">COMENZAR AHORA</a>
                        <a href="#cursos" class="btn-secondary">VER CURSOS</a>
                    </div>
                    <div class="banner-stats">
                        <div class="stat-item">
                            <h3>1000+</h3>
                            <p>Estudiantes</p>
                        </div>
                        <div class="stat-item">
                            <h3>50+</h3>
                            <p>Lecciones</p>
                        </div>
                        <div class="stat-item">
                            <h3>5</h3>
                            <p>Niveles</p>
                        </div>
                    </div>
                </div>
                <div class="banner-image">
                    <div class="image-container">
                        <img src="public/img/hero-image.png?height=400&width=400" alt="Profesor enseñando inglés">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses Section -->
    <section id="cursos" class="best-sellers">
        <div class="container">
            <h2>Cursos Destacados</h2>
            <p class="section-subtitle">Descubre nuestros cursos más populares y comienza tu viaje de aprendizaje</p>

            <div class="products-grid">
                <?php if (!empty($featured_playlists)): ?>
                    <?php foreach ($featured_playlists as $playlist): ?>
                        <div class="product-card">
                            <div class="product-tumb">
                                <?php if (!empty($playlist['cover_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="<?php echo htmlspecialchars($playlist['name']); ?>">
                                <?php else: ?>
                                    <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                                <?php endif; ?>
                                <div class="course-overlay">
                                    <a href="login.php" class="btn-overlay">Ver Curso</a>
                                </div>
                            </div>
                            <div class="product-details">
                                <span class="product-catagory">Nivel <?php echo htmlspecialchars($playlist['level']); ?></span>
                                <h4><?php echo htmlspecialchars($playlist['name']); ?></h4>
                                <p><?php echo htmlspecialchars($playlist['description'] ?: 'Curso completo de inglés'); ?></p>
                                <div class="product-bottom-details">
                                    <div class="product-price">$<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?></div>
                                    <a href="login.php" class="add-to-cart-btn">Acceder</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>Próximamente nuevos cursos disponibles.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="view-more">
                <a href="login.php">Ver Todos los Cursos <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Levels Section -->
    <section class="courses">
        <div class="container">
            <h2>Niveles de Inglés</h2>
            <p class="section-subtitle">Encuentra el nivel perfecto para ti</p>

            <div class="courses-grid">
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--orange-color); color: white;">A1</div>
                    <div class="course-icon"><i class="fas fa-seedling"></i></div>
                    <h3 class="course-title">BÁSICO</h3>
                    <p class="course-subtitle">Primeros pasos en inglés</p>
                    <ul class="course-features">
                        <li>Vocabulario básico</li>
                        <li>Gramática fundamental</li>
                        <li>Conversaciones simples</li>
                    </ul>
                </div>
                
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--red-color); color: white;">A2</div>
                    <div class="course-icon"><i class="fas fa-comments"></i></div>
                    <h3 class="course-title">PRE INTERMEDIO</h3>
                    <p class="course-subtitle">Construye tu base</p>
                    <ul class="course-features">
                        <li>Expresiones cotidianas</li>
                        <li>Tiempos verbales</li>
                        <li>Comprensión auditiva</li>
                    </ul>
                </div>
                
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--blue-color); color: white;">B1</div>
                    <div class="course-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3 class="course-title">INTERMEDIO</h3>
                    <p class="course-subtitle">Desarrolla fluidez</p>
                    <ul class="course-features">
                        <li>Conversaciones fluidas</li>
                        <li>Escritura estructurada</li>
                        <li>Comprensión de textos</li>
                    </ul>
                </div>
                
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--teal-color); color: white;">B2</div>
                    <div class="course-icon"><i class="fas fa-trophy"></i></div>
                    <h3 class="course-title">INTERMEDIO ALTO</h3>
                    <p class="course-subtitle">Perfecciona tu inglés</p>
                    <ul class="course-features">
                        <li>Debates y discusiones</li>
                        <li>Escritura avanzada</li>
                        <li>Comprensión compleja</li>
                    </ul>
                </div>
                
                <div class="course-card">
                    <div class="level-badge neon-glow" style="background-color: var(--purple-color); color: white;">C1</div>
                    <div class="course-icon"><i class="fas fa-crown"></i></div>
                    <h3 class="course-title">AVANZADO</h3>
                    <p class="course-subtitle">Dominio del idioma</p>
                    <ul class="course-features">
                        <li>Inglés profesional</li>
                        <li>Literatura y cultura</li>
                        <li>Expresión sofisticada</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="sobre-nosotros" class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Sobre el Profesor Hernán</h2>
                    <p>Con más de 10 años de experiencia enseñando inglés, el Profesor Hernán ha ayudado a miles de estudiantes a alcanzar sus metas lingüísticas.</p>
                    <div class="about-features">
                        <div class="feature-item">
                            <i class="fas fa-certificate"></i>
                            <div>
                                <h4>Certificado Internacional</h4>
                                <p>Certificaciones TESOL y CELTA</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <h4>Experiencia Comprobada</h4>
                                <p>Más de 1000 estudiantes exitosos</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-heart"></i>
                            <div>
                                <h4>Método Personalizado</h4>
                                <p>Adaptado a tu ritmo de aprendizaje</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="img/profesor-hernan.jpg?height=400&width=400" alt="Profesor Hernán">
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="promo-box">
        <div class="container">
            <h2 class="promo-title">¿Listo para Comenzar?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 30px; opacity: 0.9;">Únete a nuestra comunidad de estudiantes y comienza tu viaje hacia la fluidez en inglés</p>
            <div class="banner-buttons">
                <a href="signup.php" class="promo-link">REGISTRARSE GRATIS</a>
                <a href="login.php" class="btn-secondary" style="background-color: transparent; border: 2px solid white;">YA TENGO CUENTA</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contacto" class="contact">
        <div class="container">
            <div class="contact-form">
                <h2>¿Tienes Preguntas?</h2>
                <p>Contáctanos y te ayudaremos a elegir el curso perfecto para ti</p>
                <form>
                    <div class="form-group">
                        <label for="name">Tu nombre</label>
                        <input type="text" id="name" placeholder="Nombre completo" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Tu correo electrónico</label>
                        <input type="email" id="email" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Tu mensaje</label>
                        <textarea id="message" placeholder="¿En qué podemos ayudarte?" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Enviar Mensaje</button>
                </form>
            </div>
            <div class="contact-info">
                <div class="info-item">
                    <div class="icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="text">
                        <h3>CORREO ELECTRÓNICO</h3>
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
                    </div>
                </div>
                <div class="info-item">
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="text">
                        <h3>HORARIO</h3>
                        <p>Lun - Vie: 8:00 AM - 6:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <img src="public/img/logo-profe-hernan.png" alt="El Profesor Hernán" style="height: 40px;">
                        <span>El Profesor Hernán</span>
                    </div>
                    <p>Tu mejor opción para aprender inglés online. Cursos diseñados para todos los niveles.</p>
                </div>
                <div class="footer-section">
                    <h4>Enlaces Rápidos</h4>
                    <ul>
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#cursos">Cursos</a></li>
                        <li><a href="#sobre-nosotros">Sobre Nosotros</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Cursos</h4>
                    <ul>
                        <li><a href="login.php">Nivel Básico (A1)</a></li>
                        <li><a href="login.php">Pre Intermedio (A2)</a></li>
                        <li><a href="login.php">Intermedio (B1)</a></li>
                        <li><a href="login.php">Avanzado (C1)</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Síguenos</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 El Profesor Hernán. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Smooth scrolling para los enlaces del nav
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animación del header al hacer scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
                header.style.backdropFilter = 'blur(10px)';
            } else {
                header.style.backgroundColor = 'white';
                header.style.backdropFilter = 'none';
            }
        });
    </script>
</body>
</html>
