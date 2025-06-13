<?php
class Video {
    private $conn;
    private $table_name = "videos";

    public $id;
    public $title;
    public $description;
    public $file_path;
    public $playlist_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (title, description, file_path, playlist_id) VALUES (:title, :description, :file_path, :playlist_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':file_path', $this->file_path);
        $stmt->bindParam(':playlist_id', $this->playlist_id);
        return $stmt->execute();
    }

    public function readByPlaylist($playlist_id, $exclude_id = null) {
        if ($exclude_id) {
            // Si se proporciona un ID para excluir, lo excluimos de los resultados
            $query = "SELECT * FROM " . $this->table_name . " WHERE playlist_id = :playlist_id AND id != :exclude_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':playlist_id', $playlist_id);
            $stmt->bindParam(':exclude_id', $exclude_id);
        } else {
            // Consulta original sin exclusión
            $query = "SELECT * FROM " . $this->table_name . " WHERE playlist_id = :playlist_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':playlist_id', $playlist_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nuevo método para leer un video específico
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
