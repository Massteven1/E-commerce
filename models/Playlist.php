<?php
class Playlist {
    private $conn;
    private $table_name = "playlists";

    public $id;
    public $name;
    public $description;
    public $cover_image;
    public $price; // Nuevo: Propiedad para el precio
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, description, cover_image, price) VALUES (:name, :description, :cover_image, :price)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->cover_image = htmlspecialchars(strip_tags($this->cover_image));
        $this->price = htmlspecialchars(strip_tags($this->price)); // Limpiar el precio

        // Vincular parámetros
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cover_image', $this->cover_image);
        $stmt->bindParam(':price', $this->price); // Vincular el precio

        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
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

    // Nuevo método para actualizar una lista de reproducción
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = :name, description = :description, cover_image = :cover_image, price = :price WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->cover_image = htmlspecialchars(strip_tags($this->cover_image));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Vincular parámetros
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cover_image', $this->cover_image);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }
}
?>
