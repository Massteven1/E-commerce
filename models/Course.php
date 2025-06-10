<?php
require_once dirname(__DIR__) . '/config/database.php';

class Course {
    private $conn;
    private $table = 'courses';
    
    // Propiedades
    public $id;
    public $title;
    public $description;
    public $price;
    public $image_url;
    public $level;
    public $is_active;
    public $created_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Obtener todos los cursos activos
     */
    public function getAll() {
        $query = "SELECT c.*, 
                         (SELECT COUNT(*) FROM videos v WHERE v.course_id = c.id AND v.is_active = 1) as video_count,
                         (SELECT AVG(rating) FROM course_ratings cr WHERE cr.course_id = c.id) as average_rating,
                         (SELECT COUNT(*) FROM course_ratings cr WHERE cr.course_id = c.id) as rating_count
                  FROM " . $this->table . " c
                  WHERE c.is_active = 1
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener cursos por nivel
     */
    public function getByLevel($level) {
        $query = "SELECT c.*, 
                         (SELECT COUNT(*) FROM videos v WHERE v.course_id = c.id AND v.is_active = 1) as video_count,
                         (SELECT AVG(rating) FROM course_ratings cr WHERE cr.course_id = c.id) as average_rating,
                         (SELECT COUNT(*) FROM course_ratings cr WHERE cr.course_id = c.id) as rating_count
                  FROM " . $this->table . " c
                  WHERE c.level = ? AND c.is_active = 1
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$level]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un curso por ID
     */
    public function getById($id) {
        $query = "SELECT c.*, 
                         (SELECT AVG(rating) FROM course_ratings cr WHERE cr.course_id = c.id) as average_rating,
                         (SELECT COUNT(*) FROM course_ratings cr WHERE cr.course_id = c.id) as rating_count
                  FROM " . $this->table . " c
                  WHERE c.id = ? AND c.is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear un nuevo curso
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (title, description, price, image_url, level, is_active) 
                  VALUES (?, ?, ?, ?, ?, 1)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $this->title,
            $this->description,
            $this->price,
            $this->image_url,
            $this->level
        ]);
    }
    
    /**
     * Actualizar un curso
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title = ?, description = ?, price = ?, image_url = ?, level = ? 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $this->title,
            $this->description,
            $this->price,
            $this->image_url,
            $this->level,
            $this->id
        ]);
    }
    
    /**
     * Eliminar un curso (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET is_active = 0 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>
