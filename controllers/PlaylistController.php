<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Playlist.php';

class PlaylistController {
    private $db;
    private $playlistModel;

    public function __construct() {
        $this->db = new Database();
        $this->playlistModel = new Playlist($this->db->getConnection());
    }

    public function index() {
        $playlists = $this->playlistModel->readAll();
        require_once __DIR__ . '/../views/admin/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->playlistModel->name = $_POST['name'];
            $this->playlistModel->description = $_POST['description'] ?? '';

            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                // Usamos una ruta absoluta desde la raíz del proyecto
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/E-commerce/uploads/images/';
                $original_filename = basename($_FILES["cover_image"]["name"]);
                $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

                if ($file_extension !== 'jpeg' && $file_extension !== 'jpg') {
                    die("Solo se permiten archivos JPEG.");
                }

                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $unique_filename;

                if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                    $this->playlistModel->cover_image = 'uploads/images/' . $unique_filename; // Ruta relativa para la base de datos
                } else {
                    die("Error al subir la imagen. Verifica permisos en uploads/images/.");
                }
            } else {
                $this->playlistModel->cover_image = null;
            }

            if ($this->playlistModel->create()) {
                header('Location: courses.php?controller=playlist&action=index');
                exit();
            } else {
                die("Error al crear la lista.");
            }
        }
    }
}
?>