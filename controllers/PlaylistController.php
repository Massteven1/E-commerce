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
            if ($this->playlistModel->create()) {
                header('Location: courses.php?controller=playlist&action=index');
                exit();
            } else {
                die("Error al crear la lista. Verifica la base de datos o los datos ingresados.");
            }
        } else {
            die("Método no permitido. Usa POST.");
        }
    }
}
?>