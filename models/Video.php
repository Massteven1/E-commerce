<?php
class Video {
    private $conn;
    private $table_name = "videos";

    public $id;
    public $title;
    public $description;
    public $file_path;
    public $playlist_id;

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
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Excepción PDO: " . $e->getMessage());
        }
    }

    public function readByPlaylist() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE playlist_id = :playlist_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':playlist_id', $this->playlist_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>