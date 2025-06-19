<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use Exception;

class UserCourse {
    private $conn;
    private $table_name = "user_courses";
    
    public $id;
    public $user_id;
    public $playlist_id;
    public $order_id;
    public $access_granted_at;
    public $created_at;
    
    public function __construct($db = null) {
        if ($db === null) {
            $database = new Database();
            $this->conn = $database->getConnection();
        } else {
            $this->conn = $db;
        }
        $this->createTableIfNotExists();
    }
    
    // Otorgar acceso a un curso
    public function grantAccess($user_id, $playlist_id, $order_id = null) {
        try {
            // Verificar si ya tiene acceso
            if ($this->hasAccess($user_id, $playlist_id)) {
                return true; // Ya tiene acceso
            }
            
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, playlist_id, order_id, access_granted_at, created_at) 
                      VALUES (:user_id, :playlist_id, :order_id, NOW(), NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':playlist_id', $playlist_id, PDO::PARAM_INT);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en UserCourse::grantAccess: " . $e->getMessage());
            return false;
        }
    }
    
    // Verificar si un usuario tiene acceso a un curso
    public function hasAccess($user_id, $playlist_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                      WHERE user_id = :user_id AND playlist_id = :playlist_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':playlist_id', $playlist_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Error en UserCourse::hasAccess: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener cursos de un usuario
    public function readByUserId($user_id) {
        try {
            $query = "SELECT uc.*, p.name, p.description, p.price, p.cover_image, p.level
                      FROM " . $this->table_name . " uc
                      INNER JOIN playlists p ON uc.playlist_id = p.id
                      WHERE uc.user_id = :user_id
                      ORDER BY uc.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en UserCourse::readByUserId: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtener usuarios de un curso
    public function readByPlaylistId($playlist_id) {
        try {
            $query = "SELECT uc.*, u.first_name, u.last_name, u.email
                      FROM " . $this->table_name . " uc
                      INNER JOIN users u ON uc.user_id = u.id
                      WHERE uc.playlist_id = :playlist_id
                      ORDER BY uc.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':playlist_id', $playlist_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en UserCourse::readByPlaylistId: " . $e->getMessage());
            return [];
        }
    }
    
    // Revocar acceso a un curso
    public function revokeAccess($user_id, $playlist_id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " 
                      WHERE user_id = :user_id AND playlist_id = :playlist_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':playlist_id', $playlist_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en UserCourse::revokeAccess: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener estadísticas de inscripciones
    public function getStats() {
        try {
            $stats = [];
            
            // Total de inscripciones
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_enrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Inscripciones este mes
            $query = "SELECT COUNT(*) as monthly FROM " . $this->table_name . " 
                      WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                      AND YEAR(created_at) = YEAR(CURRENT_DATE())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['monthly_enrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['monthly'];
            
            // Curso más popular
            $query = "SELECT p.name, COUNT(uc.playlist_id) as enrollments
                      FROM " . $this->table_name . " uc
                      INNER JOIN playlists p ON uc.playlist_id = p.id
                      GROUP BY uc.playlist_id, p.name
                      ORDER BY enrollments DESC
                      LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $popular = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['most_popular_course'] = $popular ?: ['name' => 'N/A', 'enrollments' => 0];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de UserCourse: " . $e->getMessage());
            return [
                'total_enrollments' => 0,
                'monthly_enrollments' => 0,
                'most_popular_course' => ['name' => 'N/A', 'enrollments' => 0]
            ];
        }
    }
    
    // Crear la tabla si no existe
    private function createTableIfNotExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                playlist_id INT(11) NOT NULL,
                order_id INT(11) NULL,
                access_granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY unique_user_playlist (user_id, playlist_id),
                INDEX idx_user_id (user_id),
                INDEX idx_playlist_id (playlist_id),
                INDEX idx_order_id (order_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return true;
        } catch (Exception $e) {
            error_log("Error al crear la tabla user_courses: " . $e->getMessage());
            return false;
        }
    }
}
?>
