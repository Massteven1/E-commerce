<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Video.php';
require_once __DIR__ . '/../models/Playlist.php';

class VideoController {
    private $db;
    private $videoModel;
    private $playlistModel;

    public function __construct() {
        $this->db = new Database();
        $this->videoModel = new Video($this->db->getConnection());
        $this->playlistModel = new Playlist($this->db->getConnection());
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
            $this->videoModel->title = $_POST['title'];
            $this->videoModel->description = $_POST['description'] ?? '';
            $this->videoModel->playlist_id = $_POST['playlist_id'] ?? null;

            $target_dir = "uploads/videos/";
            $original_filename = basename($_FILES["video"]["name"]);
            $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

            if ($file_extension !== 'mp4') {
                die("Solo se permiten archivos MP4.");
            }

            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $unique_filename;

            if (move_uploaded_file($_FILES["video"]["tmp_name"], $target_file)) {
                $this->videoModel->file_path = $target_file;
                if ($this->videoModel->create()) {
                    header('Location: courses.php?controller=playlist&action=index');
                    exit();
                } else {
                    unlink($target_file);
                    die("Error al guardar el video en la base de datos.");
                }
            } else {
                die("Error al subir el archivo. Verifica permisos en uploads/videos/.");
            }
        }
    }

    public function viewPlaylist($id) {
        $this->videoModel->playlist_id = $id;
        $videos = $this->videoModel->readByPlaylist($id);
        $this->playlistModel->id = $id;
        $playlist = $this->playlistModel->readOne($id);
        require_once __DIR__ . '/../views/admin/view_playlist.php';
    }
}
?>