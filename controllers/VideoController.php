<?php
class VideoController {
    private $db;
    private $videoModel;
    private $playlistModel;
    private $uploadDirs;

    public function __construct() {
        // Cargar dependencias
        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../models/Video.php';
        require_once __DIR__ . '/../models/Playlist.php';

        $database = new Database();
        $this->db = $database->getConnection();
        $this->videoModel = new Video($this->db);
        $this->playlistModel = new Playlist($this->db);

        // Configurar directorios de subida
        $this->uploadDirs = [
            'videos' => __DIR__ . '/../uploads/videos/',
            'thumbnails' => __DIR__ . '/../uploads/thumbnails/'
        ];
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['video'])) {
            return;
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'playlist_id' => $_POST['playlist_id'] ?? null,
            'file_path' => null,
            'thumbnail_image' => null
        ];

        // Subir video
        $data['file_path'] = $this->handleVideoUpload($_FILES['video']);

        // Subir miniatura si existe
        if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
            $data['thumbnail_image'] = $this->handleImageUpload($_FILES['thumbnail_image']);
        }

        if ($this->videoModel->create($data)) {
            $this->redirect('playlist', 'index');
        } else {
            // Limpiar archivos si falla la BD
            if ($data['file_path']) $this->deleteFile($data['file_path']);
            if ($data['thumbnail_image']) $this->deleteFile($data['thumbnail_image']);
            die("Error al guardar el video en la base de datos.");
        }
    }

    public function viewPlaylist($id) {
        $videos = $this->videoModel->readByPlaylist($id);
        $playlist = $this->playlistModel->readOne($id);
        require_once __DIR__ . '/../views/admin/view_playlist.php';
    }

    public function viewVideo($id) {
        $video = $this->videoModel->readOne($id);
        if (!$video) {
            die("Video no encontrado.");
        }
        
        $playlist = $this->playlistModel->readOne($video['playlist_id']);
        $related_videos = $this->videoModel->readByPlaylist($video['playlist_id'], $id);
        
        require_once __DIR__ . '/../views/admin/view_video.php';
    }

    public function editVideo($id) {
        $video = $this->videoModel->readOne($id);
        if (!$video) {
            die("Video no encontrado para editar.");
        }
        $playlists = $this->playlistModel->readAll();
        require_once __DIR__ . '/../views/admin/edit_video.php';
    }

    public function updateVideo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $id = $_POST['id'];
        $current_video = $this->videoModel->readOne($id);
        
        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'playlist_id' => $_POST['playlist_id'] ?? null,
            'file_path' => $current_video['file_path'],
            'thumbnail_image' => $current_video['thumbnail_image']
        ];

        // Actualizar miniatura si se subió nueva
        if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
            if ($current_video['thumbnail_image']) {
                $this->deleteFile($current_video['thumbnail_image']);
            }
            $data['thumbnail_image'] = $this->handleImageUpload($_FILES['thumbnail_image']);
        }

        if ($this->videoModel->update($id, $data)) {
            $this->redirect('video', 'view_playlist', $data['playlist_id']);
        } else {
            die("Error al actualizar el video.");
        }
    }

    public function deleteVideo($id) {
        $video = $this->videoModel->readOne($id);
        if (!$video) {
            die("Video no encontrado.");
        }

        // Eliminar archivos
        if ($video['file_path']) $this->deleteFile($video['file_path']);
        if ($video['thumbnail_image']) $this->deleteFile($video['thumbnail_image']);

        if ($this->videoModel->delete($id)) {
            $this->redirect('video', 'view_playlist', $video['playlist_id']);
        } else {
            die("Error al eliminar el video.");
        }
    }

    private function handleVideoUpload($file) {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'mp4') {
            die("Solo se permiten archivos MP4 para videos.");
        }

        $uploadDir = $this->uploadDirs['videos'];
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = time() . '_' . uniqid() . '.mp4';
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/videos/' . $filename;
        } else {
            die("Error al subir el archivo de video.");
        }
    }

    private function handleImageUpload($file) {
        $allowedTypes = ['jpeg', 'jpg', 'png'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedTypes)) {
            die("Solo se permiten archivos JPEG, JPG o PNG para miniaturas.");
        }

        $uploadDir = $this->uploadDirs['thumbnails'];
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/thumbnails/' . $filename;
        } else {
            die("Error al subir la imagen de miniatura.");
        }
    }

    private function deleteFile($filePath) {
        $fullPath = __DIR__ . '/../' . $filePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    private function redirect($controller, $action, $id = null) {
        $url = "index.php?controller={$controller}&action={$action}";
        if ($id) $url .= "&id={$id}";
        header("Location: {$url}");
        exit();
    }
}
?>
