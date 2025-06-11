<?php
class Playlist {
    private $conn;
    private $table_name = "playlists";

    public $id;
    public $name;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, description) VALUES (:name, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        try {
            $result = $stmt->execute();
            if (!$result) {
                $errorInfo = $this->conn->errorInfo();
                die("Error en la consulta: " . $errorInfo[2]);
            }
            return $result;
        } catch (PDOException $e) {
            die("Excepción PDO: " . $e->getMessage());
        }
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>