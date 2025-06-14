<?php
// Este archivo contendrá el contenido principal de la página de inicio.
// Las variables como $playlists deben pasarse desde el router.
?>
<!-- Banner Section -->
<section class="banner">
    <div class="container">
        <div class="banner-content">
            <h1>Learn English as a Second Language</h1>
            <p>education, bilingualism and communication</p>
            <div class="banner-buttons">
                <button class="btn-primary">SHOP ALL</button>
                <button class="btn-secondary">ALL PRODUCTS</button>
            </div>
        </div>
        <div class="banner-image">
            <div class="image-container">
                <img src="img/hero-image.png?height=300&width=300" alt="Person teaching">
            </div>
        </div>
    </div>
</section>

<!-- Best Sellers Section -->
<section class="best-sellers">
    <div class="container">
        <h2>Nuestras Listas de Reproducción</h2>

        <!-- Products Grid: Product Cards (Ahora dinámico con playlists) -->
        <div class="products-grid">
            <?php if (!empty($playlists)): ?>
                <?php foreach ($playlists as $playlist): ?>
                    <div class="product-card">
                        <a href="index.php?controller=playlist&action=view_detail&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="product-tumb">
                            <?php if (!empty($playlist['cover_image'])): ?>
                                <img src="<?php echo htmlspecialchars($playlist['cover_image']); ?>" alt="<?php echo htmlspecialchars($playlist['name']); ?>">
                            <?php else: ?>
                                <img src="https://i.imgur.com/xdbHo4E.png" alt="Imagen por defecto">
                            <?php endif; ?>
                        </a>
                        <div class="product-details">
                            <span class="product-catagory">Nivel <?php echo htmlspecialchars($playlist['level']); ?></span>
                            <h4><a href="index.php?controller=playlist&action=view_detail&id=<?php echo htmlspecialchars($playlist['id']); ?>"><?php echo htmlspecialchars($playlist['name']); ?></a></h4>
                            <p><?php echo htmlspecialchars($playlist['description'] ?: 'Sin descripción'); ?></p>
                            <div class="product-bottom-details">
                                <div class="product-price">$<?php echo htmlspecialchars(number_format($playlist['price'], 2)); ?></div>
                                <div class="product-links">
                                    <?php if (!isset($_SESSION['cart'][$playlist['id']])): ?>
                                        <a href="index.php?controller=cart&action=add&id=<?php echo htmlspecialchars($playlist['id']); ?>" class="btn-primary add-to-cart-btn">Añadir al Carrito</a>
                                    <?php else: ?>
                                        <button class="btn-primary add-to-cart-btn" disabled style="opacity: 0.6;">Ya en el Carrito</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <p>No hay listas de reproducción disponibles en este momento.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sort Options -->
        <div class="sort-options">
            <span>Ordenar por:</span>
            <button class="sort-btn active">Novedades</button>
            <button class="sort-btn">Ofertas</button>
            <button class="sort-btn">Todos los artículos</button>
        </div>

        <!-- View More Button -->
        <div class="view-more">
            <a href="#">Ver Todos los Productos <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Courses Section: Course Packs -->
<section class="courses">
    <div class="container">
        <h2>Cursos por Nivel</h2>

        <div class="courses-grid">
            
            <!-- A1 -->
            <div class="course-card">
                <div class="level-badge neon-glow orange" style="background-color: var(--orange-color); color: white;">A1</div>
                <div class="course-icon"><i class="fas fa-book"></i></div>
                <h3 class="course-title">BÁSICO</h3>
                <p class="course-subtitle">Nivel Básico</p>
                <p class="course-price">$55 <span class="original-price">$70</span> <span class="discount">-22%</span></p>
            </div>
            
            <!-- A2 -->
            <div class="course-card">
                <div class="level-badge neon-glow red" style="background-color: var(--red-color); color: white;">A2</div>
                <div class="course-icon"><i class="fas fa-comments"></i></div>
                <h3 class="course-title">PRE INTERMEDIO</h3>
                <p class="course-subtitle">Nivel Pre Intermedio</p>
                <p class="course-price">$55</p>
            </div>
            
            <!-- B1 -->
            <div class="course-card">
                <div class="level-badge neon-glow blue" style="background-color: var(--blue-color); color: white;">B1</div>
                <div class="course-icon"><i class="fas fa-pen"></i></div>
                <h3 class="course-title">INTERMEDIO</h3>
                <p class="course-subtitle">Nivel Intermedio</p>
                <p class="course-price">$55</p>
            </div>
            
            <!-- B2 -->
            <div class="course-card">
                <div class="level-badge neon-glow teal" style="background-color: var(--teal-color); color: white;">B2</div>
                <div class="course-icon"><i class="fas fa-microphone"></i></div>
                <h3 class="course-title">INTERMEDIO ALTO</h3>
                <p class="course-subtitle">Nivel Intermedio Alto</p>
                <p class="course-price">$55</p>
            </div>
            
            <!-- C1 -->
            <div class="course-card">
                <div class="level-badge neon-glow purple" style="background-color: var(--purple-color); color: white;">C1</div>
                <div class="course-icon"><i class="fas fa-graduation-cap"></i></div>
                <h3 class="course-title">AVANZADO</h3>
                <p class="course-subtitle">Nivel Avanzado</p>
                <p class="course-price">$55</p>
            </div>
        </div>
        
        <!-- View More Button -->
        <div class="view-more">
            <a href="#">EXPLORAR TODOS LOS PRODUCTOS <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Promo Box Section -->
<section class="promo-box">
    <div class="container">
        <p class="promo-label">OFERTA</p>
        <h2 class="promo-title">30% DE DESCUENTO</h2>
        <div class="promo-levels">
            <div class="promo-level orange">
                <div class="level-badge neon-glow" style="background-color: var(--orange-color); color: white;">A1</div>
                <span>Nivel Básico</span>
            </div>
            <div class="promo-level red">
                <div class="level-badge neon-glow" style="background-color: var(--red-color); color: white;">A2</div>
                <span>Pre Intermedio</span>
            </div>
            <div class="promo-level blue">
                <div class="level-badge neon-glow" style="background-color: var(--blue-color); color: white;">B1</div>
                <span>Intermedio</span>
            </div>
            <div class="promo-level teal">
                <div class="level-badge neon-glow" style="background-color: var(--teal-color); color: white;">B2</div>
                <span>Intermedio Alto</span>
            </div>
            <div class="promo-level purple">
                <div class="level-badge neon-glow" style="background-color: var(--purple-color); color: white;">C1</div>
                <span>Avanzado</span>
            </div>
        </div>

        <!-- Shop Now Button -->
        <a href="#" class="promo-link">COMPRAR AHORA</a>
    </div>
</section>

<!-- Contact Form Section -->
<section class="contact">
    <div class="container">
        <div class="contact-form">
            <h2>ENVÍA TU CONSULTA</h2>
            <form>
                <div class="form-group">
                    <label for="name">Tu nombre</label>
                    <input type="text" id="name" placeholder="Nombre">
                </div>
                <div class="form-group">
                    <label for="email">Tu correo electrónico</label>
                    <input type="email" id="email" placeholder="Correo electrónico">
                </div>
                <div class="form-group">
                    <label for="message">Tu mensaje</label>
                    <textarea id="message" placeholder="Mensaje"></textarea>
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
                    <h3>CORREO ELECTRÓNICO</h3>
                    <p>info@professionalcomunidad.com</p>
                </div>
            </div>
            <div class="info-item">
                <div class="icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="text">
                    <h3>LLAMAR</h3>
                    <p>+57 123 456 789</p>
                    <p>+57 234 567 890</p>
                    <p>+57 345 678 901</p>
                </div>
            </div>
        </div>
    </div>
</section>
