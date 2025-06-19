<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use Exception;

class Playlist {
    private $conn;
    private $table_name = "playlists";
    
    public $id;
    public $title;
    public $description;
    public $thumbnail;
    public $price;
    public $created_at;
    public $updated_at;
    
    public function __construct($db = null) {
        if ($db === null) {
            $database = new Database();
            $this->conn = $database->getConnection();
        } else {
            $this->conn = $db;
        }
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, description=:description, thumbnail=:thumbnail, price=:price";
        
        $stmt = $this->conn->prepare($query);
        
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->thumbnail = htmlspecialchars(strip_tags($this->thumbnail));
        $this->price = htmlspecialchars(strip_tags($this->price));
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":thumbnail", $this->thumbnail);
        $stmt->bindParam(":price", $this->price);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function readAll() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en readAll de Playlist: " . $e->getMessage());
            return [];
        }
    }
    
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->thumbnail = $row['thumbnail'];
            $this->price = $row['price'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, description=:description, thumbnail=:thumbnail, price=:price 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->thumbnail = htmlspecialchars(strip_tags($this->thumbnail));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":thumbnail", $this->thumbnail);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }
    
    public function getStats() {
        try {
            $stats = [];
            
            // Total de playlists
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_playlists'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Playlist más popular (con más inscripciones)
            $query = "SELECT p.title, COUNT(uc.playlist_id) as enrollments 
                      FROM " . $this->table_name . " p 
                      LEFT JOIN user_courses uc ON p.id = uc.playlist_id 
                      GROUP BY p.id, p.title 
                      ORDER BY enrollments DESC 
                      LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $popular = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['most_popular'] = $popular ? $popular : ['title' => 'N/A', 'enrollments' => 0];
            
            // Ingresos totales por playlists
            $query = "SELECT SUM(p.price) as total_revenue 
                      FROM " . $this->table_name . " p 
                      INNER JOIN user_courses uc ON p.id = uc.playlist_id 
                      INNER JOIN orders o ON uc.order_id = o.id 
                      WHERE o.status = 'completed'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_revenue'] = $result['total_revenue'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de Playlist: " . $e->getMessage());
            return [
                'total_playlists' => 0,
                'most_popular' => ['title' => 'N/A', 'enrollments' => 0],
                'total_revenue' => 0
            ];
        }
    }
    
    public function findById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en findById de Playlist: " . $e->getMessage());
            return false;
        }
    }
    
    public function getVideosCount($playlistId) {
        try {
            $query = "SELECT COUNT(*) as count FROM videos WHERE playlist_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$playlistId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error obteniendo conteo de videos: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getEnrollmentsCount($playlistId) {
        try {
            $query = "SELECT COUNT(*) as count FROM user_courses WHERE playlist_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$playlistId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error obteniendo conteo de inscripciones: " . $e->getMessage());
            return 0;
        }
    }
}
