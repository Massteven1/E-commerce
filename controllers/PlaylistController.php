<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Playlist.php';

class PlaylistController {
    private $db;
    private $playlistModel;
    private $upload_dir_images;

    public function __construct() {
        $this->db = new Database();
        $this->playlistModel = new Playlist($this->db->getConnection());
        $this->upload_dir_images = __DIR__ . '/../../uploads/images/';
    }

    public function index() {
        $playlists = $this->playlistModel->readAll();
        require_once __DIR__ . '/../views/admin/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->playlistModel->name = $_POST['name'];
            $this->playlistModel->description = $_POST['description'] ?? '';
            $this->playlistModel->price = $_POST['price'] ?? 0.00; // Captura el precio

            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $original_filename = basename($_FILES["cover_image"]["name"]);
                $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

                if ($file_extension !== 'jpeg' && $file_extension !== 'jpg') {
                    die("Solo se permiten archivos JPEG.");
                }

                if (!file_exists($this->upload_dir_images)) {
                    if (!mkdir($this->upload_dir_images, 0777, true)) {
                        die("Error: No se pudo crear el directorio de subida de imágenes. Verifica los permisos de la carpeta padre: " . $this->upload_dir_images);
                    }
                }

                $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
                $target_file = $this->upload_dir_images . $unique_filename;

                if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                    $this->playlistModel->cover_image = 'uploads/images/' . $unique_filename;
                } else {
                    die("Error al subir la imagen. Verifica permisos en " . $this->upload_dir_images . ". Ruta tentativa: " . $target_file);
                }
            } else {
                $this->playlistModel->cover_image = null;
            }

            if ($this->playlistModel->create()) {
                header('Location: courses.php?controller=playlist&action=index');
                exit();
            } else {
                die("Error al crear la lista en la base de datos.");
            }
        }
    }

    // Nuevo: Acción para mostrar el formulario de edición
    public function edit($id) {
        $playlist = $this->playlistModel->readOne($id);
        if ($playlist) {
            require_once __DIR__ . '/../views/admin/edit_playlist.php';
        } else {
            die("Lista de reproducción no encontrada.");
        }
    }

    // Nuevo: Acción para procesar la actualización de una lista de reproducción
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->playlistModel->id = $_POST['id'];
            $this->playlistModel->name = $_POST['name'];
            $this->playlistModel->description = $_POST['description'] ?? '';
            $this->playlistModel->price = $_POST['price'] ?? 0.00; // Captura el precio

            // Manejo de la imagen de portada
            $current_playlist = $this->playlistModel->readOne($_POST['id']);
            $this->playlistModel->cover_image = $current_playlist['cover_image']; // Por defecto, mantiene la imagen actual

            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $original_filename = basename($_FILES["cover_image"]["name"]);
                $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

                if ($file_extension !== 'jpeg' && $file_extension !== 'jpg') {
                    die("Solo se permiten archivos JPEG.");
                }

                if (!file_exists($this->upload_dir_images)) {
                    if (!mkdir($this->upload_dir_images, 0777, true)) {
                        die("Error: No se pudo crear el directorio de subida de imágenes. Verifica los permisos de la carpeta padre: " . $this->upload_dir_images);
                    }
                }

                $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
                $target_file = $this->upload_dir_images . $unique_filename;

                if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                    // Elimina la imagen antigua si existe y es diferente a la nueva
                    if ($current_playlist['cover_image'] && file_exists(__DIR__ . '/../../' . $current_playlist['cover_image'])) {
                        unlink(__DIR__ . '/../../' . $current_playlist['cover_image']);
                    }
                    $this->playlistModel->cover_image = 'uploads/images/' . $unique_filename;
                } else {
                    die("Error al subir la nueva imagen. Verifica permisos.");
                }
            }

            if ($this->playlistModel->update()) {
                header('Location: courses.php?controller=playlist&action=index');
                exit();
            } else {
                die("Error al actualizar la lista.");
            }
        }
    }
}
?>
