<?php
namespace Models; // Añadir namespace

class UserCourse {
    private $conn;
    private $table_name = "user_courses";
    
    public $id;
    public $user_id;
    public $playlist_id;
    public $order_id;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
        // Crear tabla si no existe
        $this->createTableIfNotExists();
    }
    
    // Verificar si un usuario tiene acceso a un curso específico
    public function hasAccess($user_id, $playlist_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                      WHERE user_id = :user_id AND playlist_id = :playlist_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT); // Usar \PDO::PARAM_INT
            $stmt->bindParam(':playlist_id', $playlist_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(\PDO::FETCH_ASSOC); // Usar \PDO::FETCH_ASSOC
            
            return $row['count'] > 0;
        } catch (\PDOException $e) { // Usar \PDOException
            error_log("Error en UserCourse::hasAccess: " . $e->getMessage());
            return false;
        }
    }
    
    // Otorgar acceso a un usuario para un curso específico
    public function grantAccess($user_id, $playlist_id, $order_id) {
        try {
            // Verificar si ya existe el acceso
            if ($this->hasAccess($user_id, $playlist_id)) {
                return true; // El usuario ya tiene acceso
            }
            
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, playlist_id, order_id, created_at) 
                      VALUES (:user_id, :playlist_id, :order_id, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
            $stmt->bindParam(':playlist_id', $playlist_id, \PDO::PARAM_INT);
            $stmt->bindParam(':order_id', $order_id, \PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        } catch (\PDOException $e) {
            error_log("Error en UserCourse::grantAccess: " . $e->getMessage());
            return false;
        }
    }
    
    // Revocar acceso a un curso
    public function revokeAccess($user_id, $playlist_id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " 
                      WHERE user_id = :user_id AND playlist_id = :playlist_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
            $stmt->bindParam(':playlist_id', $playlist_id, \PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error en UserCourse::revokeAccess: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener todos los cursos a los que un usuario tiene acceso
    public function getPurchasedPlaylistsByUserId($user_id) {
        try {
            $query = "SELECT uc.*, p.name, p.description, p.price, p.cover_image, p.level,
                             o.created_at as purchase_date, o.amount as paid_amount
                      FROM " . $this->table_name . " uc
                      JOIN playlists p ON uc.playlist_id = p.id
                      LEFT JOIN orders o ON uc.order_id = o.id
                      WHERE uc.user_id = :user_id
                      ORDER BY uc.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en UserCourse::getPurchasedPlaylistsByUserId: " . $e->getMessage());
            return [];
        }
    }
    
    // Método alias para compatibilidad
    public function getUserCourses($user_id) {
        return $this->getPurchasedPlaylistsByUserId($user_id);
    }
    
    // Obtener cursos comprados en un pedido específico
    public function getCoursesByOrderId($order_id) {
        try {
            $query = "SELECT uc.*, p.name, p.description, p.price, p.cover_image, p.level
                      FROM " . $this->table_name . " uc
                      JOIN playlists p ON uc.playlist_id = p.id
                      WHERE uc.order_id = :order_id
                      ORDER BY uc.created_at ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en UserCourse::getCoursesByOrderId: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtener estadísticas de cursos de usuario
    public function getUserCourseStats($user_id) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_courses,
                        COUNT(DISTINCT p.level) as different_levels,
                        SUM(o.amount) as total_spent,
                        MIN(uc.created_at) as first_purchase,
                        MAX(uc.created_at) as last_purchase
                      FROM " . $this->table_name . " uc
                      JOIN playlists p ON uc.playlist_id = p.id
                      LEFT JOIN orders o ON uc.order_id = o.id
                      WHERE uc.user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en UserCourse::getUserCourseStats: " . $e->getMessage());
            return null;
        }
    }
    
    // Crear la tabla si no existe
    private function createTableIfNotExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return true;
        } catch (\PDOException $e) {
            // Si falla por las foreign keys, crear sin ellas
            try {
                $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                
                return true;
            } catch (\PDOException $e2) {
                error_log("Error al crear la tabla user_courses: " . $e2->getMessage());
                return false;
            }
        }
    }
}
?>
