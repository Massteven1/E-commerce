<?php
class Video {
    private $conn;
    private $table_name = "videos";

    public $id;
    public $title;
    public $description;
    public $file_path;
    public $thumbnail_image; // Nuevo: Propiedad para la imagen de miniatura
    public $playlist_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (title, description, file_path, thumbnail_image, playlist_id) VALUES (:title, :description, :file_path, :thumbnail_image, :playlist_id)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));
        $this->thumbnail_image = htmlspecialchars(strip_tags($this->thumbnail_image)); // Limpiar la miniatura
        $this->playlist_id = htmlspecialchars(strip_tags($this->playlist_id));

        // Vincular parámetros
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':file_path', $this->file_path);
        $stmt->bindParam(':thumbnail_image', $this->thumbnail_image); // Vincular la miniatura
        $stmt->bindParam(':playlist_id', $this->playlist_id);

        return $stmt->execute();
    }

    public function readByPlaylist($playlist_id, $exclude_id = null) {
        if ($exclude_id) {
            $query = "SELECT * FROM " . $this->table_name . " WHERE playlist_id = :playlist_id AND id != :exclude_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':playlist_id', $playlist_id);
            $stmt->bindParam(':exclude_id', $exclude_id);
        } else {
            $query = "SELECT * FROM " . $this->table_name . " WHERE playlist_id = :playlist_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':playlist_id', $playlist_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Nuevo método para actualizar un video
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET title = :title, description = :description, file_path = :file_path, thumbnail_image = :thumbnail_image, playlist_id = :playlist_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));
        $this->thumbnail_image = htmlspecialchars(strip_tags($this->thumbnail_image));
        $this->playlist_id = htmlspecialchars(strip_tags($this->playlist_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Vincular parámetros
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':file_path', $this->file_path);
        $stmt->bindParam(':thumbnail_image', $this->thumbnail_image);
        $stmt->bindParam(':playlist_id', $this->playlist_id);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }
}
?>
