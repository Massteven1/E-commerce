<?php
class Video {
    private $conn;
    private $table = "videos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} (title, description, file_path, thumbnail_image, playlist_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['file_path'],
            $data['thumbnail_image'],
            $data['playlist_id']
        ]);
    }

    public function readByPlaylist($playlist_id, $exclude_id = null) {
        if ($exclude_id) {
            $query = "SELECT * FROM {$this->table} WHERE playlist_id = ? AND id != ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$playlist_id, $exclude_id]);
        } else {
            $query = "SELECT * FROM {$this->table} WHERE playlist_id = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$playlist_id]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readOne($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET title = ?, description = ?, file_path = ?, thumbnail_image = ?, playlist_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['file_path'],
            $data['thumbnail_image'],
            $data['playlist_id'],
            $id
        ]);
    }

    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function deleteByPlaylist($playlist_id) {
        $query = "DELETE FROM {$this->table} WHERE playlist_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$playlist_id]);
    }
}
?>
