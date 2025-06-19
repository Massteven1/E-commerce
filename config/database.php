<?php
namespace Config;

class Database {
    private $host = "127.0.0.1";
    private $db_name = "ecommerce_cursos";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        if ($this->conn === null) {
            try {
                // Usar \PDO para referenciar la clase global PDO
                $this->conn = new \PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                    $this->username, 
                    $this->password,
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
            } catch (\PDOException $exception) { // Usar \PDOException
                error_log("Error de conexión a la base de datos: " . $exception->getMessage());
                die("Error de conexión: No se pudo conectar a la base de datos. Por favor, contacta al administrador.");
            }
        }
        return $this->conn;
    }

    // Método para verificar la conexión
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query("SELECT 1");
            return true;
        } catch (\Exception $e) { // Usar \Exception
            error_log("Test de conexión falló: " . $e->getMessage());
            return false;
        }
    }

    // Método para crear las tablas necesarias si no existen
    public function createTablesIfNotExist() {
        try {
            $conn = $this->getConnection();
            
            // Crear tabla users
            $conn->exec("CREATE TABLE IF NOT EXISTS users (
                id INT(11) NOT NULL AUTO_INCREMENT,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                role ENUM('user', 'admin') DEFAULT 'user',
                is_active TINYINT(1) DEFAULT 1,
                email_verified TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_email (email),
                INDEX idx_role (role),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Crear tabla playlists
            $conn->exec("CREATE TABLE IF NOT EXISTS playlists (
                id INT(11) NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                level VARCHAR(50),
                cover_image VARCHAR(255),
                price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_level (level),
                INDEX idx_price (price)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Crear tabla orders
            $conn->exec("CREATE TABLE IF NOT EXISTS orders (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                transaction_id VARCHAR(255) NOT NULL UNIQUE,
                amount DECIMAL(10,2) NOT NULL,
                currency VARCHAR(3) NOT NULL DEFAULT 'USD',
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_user_id (user_id),
                INDEX idx_transaction_id (transaction_id),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Crear tabla user_courses
            $conn->exec("CREATE TABLE IF NOT EXISTS user_courses (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                playlist_id INT(11) NOT NULL,
                order_id INT(11) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY user_playlist (user_id, playlist_id),
                INDEX idx_user_id (user_id),
                INDEX idx_playlist_id (playlist_id),
                INDEX idx_order_id (order_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            return true;
        } catch (\PDOException $e) {
            error_log("Error creando tablas: " . $e->getMessage());
            return false;
        }
    }
}
?>
