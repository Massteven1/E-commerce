<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Video.php';
require_once __DIR__ . '/../models/Playlist.php';

class VideoController {
    private $db;
    private $videoModel;
    private $playlistModel;
    private $upload_dir_videos;

    public function __construct() {
        $this->db = new Database();
        $this->videoModel = new Video($this->db->getConnection());
        $this->playlistModel = new Playlist($this->db->getConnection());
        $this->upload_dir_videos = __DIR__ . '/../../uploads/videos/';
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
            $this->videoModel->title = $_POST['title'];
            $this->videoModel->description = $_POST['description'] ?? '';
            $this->videoModel->playlist_id = $_POST['playlist_id'] ?? null;

            $original_filename = basename($_FILES["video"]["name"]);
            $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

            if ($file_extension !== 'mp4') {
                die("Solo se permiten archivos MP4.");
            }

            if (!file_exists($this->upload_dir_videos)) {
                if (!mkdir($this->upload_dir_videos, 0777, true)) {
                    die("Error: No se pudo crear el directorio de subida de videos. Verifica los permisos de la carpeta padre: " . $this->upload_dir_videos);
                }
            }

            $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $this->upload_dir_videos . $unique_filename;

            if (move_uploaded_file($_FILES["video"]["tmp_name"], $target_file)) {
                $this->videoModel->file_path = 'uploads/videos/' . $unique_filename;
                if ($this->videoModel->create()) {
                    header('Location: courses.php?controller=playlist&action=index');
                    exit();
                } else {
                    unlink($target_file);
                    die("Error al guardar el video en la base de datos.");
                }
            } else {
                die("Error al subir el archivo. Verifica permisos en " . $this->upload_dir_videos . ". Ruta tentativa: " . $target_file);
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

    // Nuevo método para ver un video individual
    public function viewVideo($id) {
        $video = $this->videoModel->readOne($id);
        if (!$video) {
            die("Video no encontrado.");
        }
        
        // Obtener la playlist a la que pertenece el video
        $this->playlistModel->id = $video['playlist_id'];
        $playlist = $this->playlistModel->readOne($video['playlist_id']);
        
        // Obtener videos relacionados (otros videos de la misma playlist)
        $related_videos = $this->videoModel->readByPlaylist($video['playlist_id'], $id);
        
        require_once __DIR__ . '/../views/admin/view_video.php';
    }
}
?>
