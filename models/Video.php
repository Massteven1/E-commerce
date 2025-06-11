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
        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVideosByPlaylist($playlistId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE playlist_id = :playlist_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':playlist_id', $playlistId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>