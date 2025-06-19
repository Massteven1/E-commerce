-- Script para crear datos de ejemplo para testing

-- Insertar usuario administrador de prueba
INSERT IGNORE INTO users (email, password, first_name, last_name, role, is_active, email_verified) 
VALUES ('admin@profesorhernan.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Sistema', 'admin', 1, 1);

-- Insertar usuario de prueba
INSERT IGNORE INTO users (email, password, first_name, last_name, role, is_active, email_verified) 
VALUES ('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usuario', 'Prueba', 'user', 1, 1);

-- Insertar cursos de ejemplo
INSERT IGNORE INTO playlists (id, name, description, level, cover_image, price) VALUES
(1, 'Inglés Básico A1', 'Curso completo de inglés para principiantes. Aprende vocabulario básico, gramática fundamental y conversaciones simples.', 'A1', 'img/courses/basic-a1.jpg', 49.99),
(2, 'Inglés Pre-Intermedio A2', 'Desarrolla tu base de inglés con expresiones cotidianas, tiempos verbales y comprensión auditiva.', 'A2', 'img/courses/pre-intermediate-a2.jpg', 69.99),
(3, 'Inglés Intermedio B1', 'Alcanza fluidez en conversaciones, escritura estructurada y comprensión de textos complejos.', 'B1', 'img/courses/intermediate-b1.jpg', 89.99),
(4, 'Inglés Intermedio Alto B2', 'Perfecciona tu inglés con debates, discusiones, escritura avanzada y comprensión compleja.', 'B2', 'img/courses/upper-intermediate-b2.jpg', 109.99),
(5, 'Inglés Avanzado C1', 'Domina el inglés profesional, literatura, cultura y expresión sofisticada.', 'C1', 'img/courses/advanced-c1.jpg', 129.99),
(6, 'Inglés de Negocios', 'Especialízate en inglés corporativo, presentaciones, negociaciones y comunicación profesional.', 'B2', 'img/courses/business-english.jpg', 149.99);

-- Insertar pedido de ejemplo (opcional, para testing)
-- INSERT IGNORE INTO orders (user_id, transaction_id, amount, currency, status) 
-- VALUES (2, 'test_transaction_123', 49.99, 'USD', 'completed');

-- Insertar acceso a curso de ejemplo (opcional, para testing)
-- INSERT IGNORE INTO user_courses (user_id, playlist_id, order_id) 
-- VALUES (2, 1, 1);
