<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Video.php';
require_once __DIR__ . '/../models/Playlist.php';

class VideoController {
    private $db;
    private $videoModel;
    private $playlistModel;
    private $upload_dir_videos;
    private $upload_dir_thumbnails;

    public function __construct() {
        $this->db = new Database();
        $this->videoModel = new Video($this->db->getConnection());
        $this->playlistModel = new Playlist($this->db->getConnection());
        // Corregir las rutas: deben apuntar a la raíz del proyecto
        $this->upload_dir_videos = __DIR__ . '/../uploads/videos/';
        $this->upload_dir_thumbnails = __DIR__ . '/../uploads/thumbnails/';
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
            $this->videoModel->title = $_POST['title'];
            $this->videoModel->description = $_POST['description'] ?? '';
            $this->videoModel->playlist_id = $_POST['playlist_id'] ?? null;
            $this->videoModel->thumbnail_image = null;

            // Manejo de la subida del video
            $original_video_filename = basename($_FILES["video"]["name"]);
            $video_file_extension = strtolower(pathinfo($original_video_filename, PATHINFO_EXTENSION));

            if ($video_file_extension !== 'mp4') {
                die("Solo se permiten archivos MP4 para videos.");
            }

            // Crear directorio de videos si no existe
            if (!file_exists($this->upload_dir_videos)) {
                if (!mkdir($this->upload_dir_videos, 0777, true)) {
                    die("Error: No se pudo crear el directorio de subida de videos: " . $this->upload_dir_videos);
                }
            }

            $unique_video_filename = time() . '_' . uniqid() . '.' . $video_file_extension;
            $target_video_file = $this->upload_dir_videos . $unique_video_filename;

            if (!move_uploaded_file($_FILES["video"]["tmp_name"], $target_video_file)) {
                die("Error al subir el archivo de video. Ruta: " . $target_video_file);
            }
            
            // Guardar la ruta relativa desde la raíz del proyecto
            $this->videoModel->file_path = 'uploads/videos/' . $unique_video_filename;
            echo "Video subido correctamente: " . $target_video_file . "<br>";

            // Manejo de la subida de la miniatura
            if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
                $original_thumbnail_filename = basename($_FILES["thumbnail_image"]["name"]);
                $thumbnail_file_extension = strtolower(pathinfo($original_thumbnail_filename, PATHINFO_EXTENSION));

                if (!in_array($thumbnail_file_extension, ['jpeg', 'jpg', 'png'])) {
                    unlink($target_video_file);
                    die("Solo se permiten archivos JPEG, JPG o PNG para miniaturas.");
                }

                // Crear directorio de miniaturas si no existe
                if (!file_exists($this->upload_dir_thumbnails)) {
                    if (!mkdir($this->upload_dir_thumbnails, 0777, true)) {
                        unlink($target_video_file);
                        die("Error: No se pudo crear el directorio de miniaturas: " . $this->upload_dir_thumbnails);
                    }
                }

                $unique_thumbnail_filename = time() . '_' . uniqid() . '.' . $thumbnail_file_extension;
                $target_thumbnail_file = $this->upload_dir_thumbnails . $unique_thumbnail_filename;

                if (move_uploaded_file($_FILES["thumbnail_image"]["tmp_name"], $target_thumbnail_file)) {
                    $this->videoModel->thumbnail_image = 'uploads/thumbnails/' . $unique_thumbnail_filename;
                    echo "Miniatura subida correctamente: " . $target_thumbnail_file . "<br>";
                } else {
                    unlink($target_video_file);
                    die("Error al subir la imagen de miniatura. Ruta: " . $target_thumbnail_file);
                }
            }

            if ($this->videoModel->create()) {
                header('Location: courses.php?controller=playlist&action=index');
                exit();
            } else {
                // Eliminar archivos si falla la inserción en la DB
                if (file_exists($target_video_file)) unlink($target_video_file);
                if (isset($target_thumbnail_file) && file_exists($target_thumbnail_file)) unlink($target_thumbnail_file);
                die("Error al guardar el video en la base de datos.");
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

    public function viewVideo($id) {
        $video = $this->videoModel->readOne($id);
        if (!$video) {
            die("Video no encontrado.");
        }
        
        $this->playlistModel->id = $video['playlist_id'];
        $playlist = $this->playlistModel->readOne($video['playlist_id']);
        
        $related_videos = $this->videoModel->readByPlaylist($video['playlist_id'], $id);
        
        require_once __DIR__ . '/../views/admin/view_video.php';
    }

    public function editVideo($id) {
        $video = $this->videoModel->readOne($id);
        if ($video) {
            $playlists = $this->playlistModel->readAll();
            require_once __DIR__ . '/../views/admin/edit_video.php';
        } else {
            die("Video no encontrado para editar.");
        }
    }

    public function updateVideo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->videoModel->id = $_POST['id'];
            $this->videoModel->title = $_POST['title'];
            $this->videoModel->description = $_POST['description'] ?? '';
            $this->videoModel->playlist_id = $_POST['playlist_id'] ?? null;

            // Obtener datos actuales del video
            $current_video_data = $this->videoModel->readOne($_POST['id']);
            $this->videoModel->file_path = $current_video_data['file_path'];
            $this->videoModel->thumbnail_image = $current_video_data['thumbnail_image'];

            // Manejo de nueva miniatura
            if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
                $original_thumbnail_filename = basename($_FILES["thumbnail_image"]["name"]);
                $thumbnail_file_extension = strtolower(pathinfo($original_thumbnail_filename, PATHINFO_EXTENSION));

                if (!in_array($thumbnail_file_extension, ['jpeg', 'jpg', 'png'])) {
                    die("Solo se permiten archivos JPEG, JPG o PNG para miniaturas.");
                }

                if (!file_exists($this->upload_dir_thumbnails)) {
                    if (!mkdir($this->upload_dir_thumbnails, 0777, true)) {
                        die("Error: No se pudo crear el directorio de miniaturas.");
                    }
                }

                $unique_thumbnail_filename = time() . '_' . uniqid() . '.' . $thumbnail_file_extension;
                $target_thumbnail_file = $this->upload_dir_thumbnails . $unique_thumbnail_filename;

                if (move_uploaded_file($_FILES["thumbnail_image"]["tmp_name"], $target_thumbnail_file)) {
                    // Eliminar miniatura anterior si existe
                    if ($current_video_data['thumbnail_image'] && file_exists(__DIR__ . '/../' . $current_video_data['thumbnail_image'])) {
                        unlink(__DIR__ . '/../' . $current_video_data['thumbnail_image']);
                    }
                    $this->videoModel->thumbnail_image = 'uploads/thumbnails/' . $unique_thumbnail_filename;
                } else {
                    die("Error al subir la nueva imagen de miniatura.");
                }
            }

            if ($this->videoModel->update()) {
                header('Location: courses.php?controller=video&action=view_playlist&id=' . $this->videoModel->playlist_id);
                exit();
            } else {
                die("Error al actualizar el video.");
            }
        }
    }
}
?>
