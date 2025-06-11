<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Video.php';

class VideoController {
    private $db;
    private $videoModel;

    public function __construct() {
        $this->db = new Database();
        $this->videoModel = new Video($this->db->getConnection());
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $video = $_FILES['video'];
            $playlistId = $_POST['playlist_id'] ?? null;

            if ($video['error'] === UPLOAD_ERR_OK && $video['type'] === 'video/mp4') {
                $uploadDir = '../uploads/videos/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = basename($video['name']);
                $filePath = $uploadDir . $fileName;

                if (file_exists($filePath)) {
                    die("Error: Ya existe un archivo con ese nombre.");
                }

                if (move_uploaded_file($video['tmp_name'], $filePath)) {
                    $this->videoModel->title = $title;
                    $this->videoModel->description = $description;
                    $this->videoModel->file_path = '/testing/uploads/videos/' . $fileName;
                    $this->videoModel->playlist_id = $playlistId;

                    if ($this->videoModel->create()) {
                        header('Location: ../admin/index.php');
                        exit();
                    } else {
                        die("Error al guardar en la base de datos.");
                    }
                } else {
                    die("Error al mover el archivo.");
                }
            } else {
                die("Error al subir el archivo o formato no válido (solo MP4).");
            }
        }
    }

    public function viewPlaylist($playlistId) {
        if (!isset($playlistId) || !is_numeric($playlistId)) {
            die("ID de lista inválido.");
        }
        $videos = $this->videoModel->getVideosByPlaylist($playlistId);
        $playlist = $this->getPlaylistName($playlistId);
        require_once __DIR__ . '/../views/admin/view_playlist.php';
    }

    private function getPlaylistName($playlistId) {
        $query = "SELECT name, description FROM playlists WHERE id = :id";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bindParam(':id', $playlistId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>