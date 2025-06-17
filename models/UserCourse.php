<?php
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
    }
    
    // Verificar si un usuario tiene acceso a un curso específico
    public function hasAccess($user_id, $playlist_id) {
        try {
            // Verificar si la tabla existe
            $tableCheck = $this->conn->prepare("SHOW TABLES LIKE :table_name");
            $tableCheck->bindParam(':table_name', $this->table_name);
            $tableCheck->execute();
            
            if ($tableCheck->rowCount() == 0) {
                // La tabla no existe, crear la tabla
                $this->createTable();
            }
            
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                      WHERE user_id = :user_id AND playlist_id = :playlist_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':playlist_id', $playlist_id);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $row['count'] > 0;
        } catch (PDOException $e) {
            // Si hay un error, registrarlo y devolver false
            error_log("Error en hasAccess: " . $e->getMessage());
            return false;
        }
    }
    
    // Otorgar acceso a un usuario para un curso específico
    public function grantAccess($user_id, $playlist_id, $order_id) {
        try {
            // Verificar si la tabla existe
            $tableCheck = $this->conn->prepare("SHOW TABLES LIKE :table_name");
            $tableCheck->bindParam(':table_name', $this->table_name);
            $tableCheck->execute();
            
            if ($tableCheck->rowCount() == 0) {
                // La tabla no existe, crear la tabla
                $this->createTable();
            }
            
            // Verificar si ya existe el acceso
            if ($this->hasAccess($user_id, $playlist_id)) {
                return true; // El usuario ya tiene acceso
            }
            
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, playlist_id, order_id, created_at) 
                      VALUES (:user_id, :playlist_id, :order_id, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':playlist_id', $playlist_id);
            $stmt->bindParam(':order_id', $order_id);
            
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            // Si hay un error, registrarlo y devolver false
            error_log("Error en grantAccess: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener todos los cursos a los que un usuario tiene acceso
    public function getUserCourses($user_id) {
        try {
            // Verificar si la tabla existe
            $tableCheck = $this->conn->prepare("SHOW TABLES LIKE :table_name");
            $tableCheck->bindParam(':table_name', $this->table_name);
            $tableCheck->execute();
            
            if ($tableCheck->rowCount() == 0) {
                // La tabla no existe, crear la tabla
                $this->createTable();
                return []; // No hay cursos si la tabla no existía
            }
            
            $query = "SELECT uc.*, p.name, p.description, p.price, p.cover_image, p.level
                      FROM " . $this->table_name . " uc
                      JOIN playlists p ON uc.playlist_id = p.id
                      WHERE uc.user_id = :user_id
                      ORDER BY uc.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si hay un error, registrarlo y devolver un array vacío
            error_log("Error en getUserCourses: " . $e->getMessage());
            return [];
        }
    }

    // Método alias para compatibilidad
    public function getPurchasedPlaylistsByUserId($user_id) {
        return $this->getUserCourses($user_id);
    }
    
    // Crear la tabla si no existe
    private function createTable() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                playlist_id INT(11) NOT NULL,
                order_id INT(11) NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY user_playlist (user_id, playlist_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al crear la tabla user_courses: " . $e->getMessage());
            return false;
        }
    }
}
?>
