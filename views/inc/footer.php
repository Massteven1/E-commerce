</main>
    
    <footer class="main-footer bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-graduation-cap"></i> Cursos de Inglés</h5>
                    <p>La mejor plataforma para aprender inglés online con cursos estructurados por niveles.</p>
                </div>
                <div class="col-md-3">
                    <h6>Enlaces Rápidos</h6>
                    <ul class="list-unstyled">
                        <li><a href="/index.php" class="text-light">Inicio</a></li>
                        <li><a href="/views/client/all-courses.php" class="text-light">Todos los Cursos</a></li>
                        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                            <li><a href="/views/client/purchase-history.php" class="text-light">Mis Cursos</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Contacto</h6>
                    <p><i class="fas fa-envelope"></i> info@cursosdeingles.com</p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                </div>
            </div>
            <hr class="my-3">
            <div class="row">
                <div class="col-12 text-center">
                    <p>&copy; <?php echo date('Y'); ?> Cursos de Inglés. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personalizado -->
    <script>
        // Función para mostrar mensajes flash
        <?php if (isset($_SESSION['flash_message'])): ?>
            alert('<?php echo addslashes($_SESSION['flash_message']); ?>');
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
