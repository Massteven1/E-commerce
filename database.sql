-- Base de datos para el ecommerce de cursos
CREATE DATABASE IF NOT EXISTS ecommerce_cursos;
USE ecommerce_cursos;

-- NO CREAR TABLA DE USUARIOS - Solo Firebase

-- Tabla de cursos
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(500),
    level ENUM('A1', 'A2', 'B1', 'B2', 'C1') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de videos
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_url VARCHAR(500) NOT NULL,
    duration INT DEFAULT 0,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Tabla de calificaciones (usando firebase_uid en lugar de user_id)
CREATE TABLE IF NOT EXISTS course_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    firebase_uid VARCHAR(255) NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_course (firebase_uid, course_id)
);

-- Tabla de preguntas (usando firebase_uid en lugar de user_id)
CREATE TABLE IF NOT EXISTS video_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    firebase_uid VARCHAR(255) NOT NULL,
    question TEXT NOT NULL,
    answer TEXT,
    answered_by VARCHAR(255), -- firebase_uid del admin que responde
    answered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Tabla de carrito (usando firebase_uid en lugar de user_id)
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firebase_uid VARCHAR(255) NOT NULL,
    course_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_course (firebase_uid, course_id)
);

-- Tabla de órdenes (usando firebase_uid en lugar de user_id)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firebase_uid VARCHAR(255) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de items de órdenes
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    course_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Insertar cursos de ejemplo
INSERT IGNORE INTO courses (title, description, price, level, is_active) VALUES 
('Inglés Básico A1', 'Curso completo de inglés nivel básico para principiantes', 55.00, 'A1', 1),
('Inglés Pre-Intermedio A2', 'Curso de inglés pre-intermedio con ejercicios prácticos', 55.00, 'A2', 1),
('Inglés Intermedio B1', 'Curso de inglés intermedio con conversaciones reales', 55.00, 'B1', 1),
('Inglés Intermedio Alto B2', 'Curso avanzado de inglés intermedio alto', 55.00, 'B2', 1),
('Inglés Avanzado C1', 'Curso de inglés avanzado para dominio del idioma', 55.00, 'C1', 1);
