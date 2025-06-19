<?php
namespace Models; // Añadir namespace

class Playlist {
    private $conn;
    private $table = "playlists";

    public function __construct($db) {
        $this->conn = $db;
        $this->createTableIfNotExists();
    }

    public function create($data) {
        try {
            $query = "INSERT INTO {$this->table} (name, description, level, cover_image, price, created_at) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                $data['name'],
                $data['description'],
                $data['level'],
                $data['cover_image'],
                $data['price']
            ]);
        } catch (\PDOException $e) { // Usar \PDOException
            error_log("Error en Playlist::create: " . $e->getMessage());
            return false;
        }
    }

    public function readAll() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC); // Usar \PDO::FETCH_ASSOC
        } catch (\PDOException $e) {
            error_log("Error en Playlist::readAll: " . $e->getMessage());
            return [];
        }
    }

    public function readOne($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Playlist::readOne: " . $e->getMessage());
            return null;
        }
    }

    public function update($id, $data) {
        try {
            $query = "UPDATE {$this->table} 
                      SET name = ?, description = ?, level = ?, cover_image = ?, price = ?, updated_at = NOW() 
                      WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                $data['name'],
                $data['description'],
                $data['level'],
                $data['cover_image'],
                $data['price'],
                $id
            ]);
        } catch (\PDOException $e) {
            error_log("Error en Playlist::update: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Error en Playlist::delete: " . $e->getMessage());
            return false;
        }
    }

    // Obtener cursos por nivel
    public function getByLevel($level) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE level = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$level]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Playlist::getByLevel: " . $e->getMessage());
            return [];
        }
    }

    // Obtener cursos en rango de precio
    public function getByPriceRange($minPrice, $maxPrice) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE price BETWEEN ? AND ? ORDER BY price ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$minPrice, $maxPrice]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Playlist::getByPriceRange: " . $e->getMessage());
            return [];
        }
    }

    // Buscar cursos por nombre
    public function search($searchTerm) {
        try {
            $query = "SELECT * FROM {$this->table} 
                      WHERE name LIKE ? OR description LIKE ? 
                      ORDER BY created_at DESC";
            $searchPattern = '%' . $searchTerm . '%';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$searchPattern, $searchPattern]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Playlist::search: " . $e->getMessage());
            return [];
        }
    }

    // Obtener estadísticas de cursos
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_courses,
                        AVG(price) as average_price,
                        MIN(price) as min_price,
                        MAX(price) as max_price,
                        COUNT(DISTINCT level) as total_levels
                      FROM {$this->table}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Playlist::getStats: " . $e->getMessage());
            return null;
        }
    }

    // Crear tabla si no existe
    private function createTableIfNotExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
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
                INDEX idx_price (price),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return true;
        } catch (\PDOException $e) {
            error_log("Error al crear la tabla playlists: " . $e->getMessage());
            return false;
        }
    }
}
?>
