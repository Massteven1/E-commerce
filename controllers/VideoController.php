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
            $unique_filename = time() . '_' . uniqid() . '.' . $file_extension; // Nombre único
            $target_file = $target_dir . $unique_filename;

            $allowed_types = ['mp4'];
            if (!in_array($file_extension, $allowed_types)) {
                die("Solo se permiten archivos MP4.");
            }

            // Verificar si el directorio existe, si no, crearlo
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Mover el archivo al directorio de uploads con nombre único
            if (move_uploaded_file($_FILES["video"]["tmp_name"], $target_file)) {
                $this->videoModel->file_path = $target_file;
                if ($this->videoModel->create()) {
                    header('Location: index.php?controller=playlist&action=index');
                    exit();
                } else {
                    unlink($target_file); // Eliminar el archivo si falla la inserción en la base de datos
                    die("Error al guardar el video en la base de datos.");
                }
            } else {
                die("Error al subir el archivo.");
            }
        } else {
            die("Método no permitido o archivo no enviado.");
        }
    }

    public function viewPlaylist($id) {
        $this->videoModel->playlist_id = $id;
        $videos = $this->videoModel->readByPlaylist();
        $this->playlistModel->id = $id;
        $playlist = $this->playlistModel->readOne();
        require_once __DIR__ . '/../views/admin/view_playlist.php';
    }
}
?>