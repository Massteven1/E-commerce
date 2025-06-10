<?php

require_once dirname(__DIR__) . '/config/database.php';

class Video {
    private $conn;
    private $table_name = "videos";

    public $id;
    public $title;
    public $description;
    public $file_path;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create video
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET title=:title, description=:description, file_path=:file_path, created_at=NOW(), updated_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->file_path=htmlspecialchars(strip_tags($this->file_path));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":file_path", $this->file_path);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read all videos
    public function read() {
        $query = "SELECT id, title, description, file_path, created_at, updated_at FROM " . $this->table_name . " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

    // Read one video
    public function readOne() {
        $query = "SELECT id, title, description, file_path, created_at, updated_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->title = $row['title'];
        $this->description = $row['description'];
        $this->file_path = $row['file_path'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }

    // Update video
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET title=:title, description=:description, file_path=:file_path, updated_at=NOW() WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->file_path=htmlspecialchars(strip_tags($this->file_path));
        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete video
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
