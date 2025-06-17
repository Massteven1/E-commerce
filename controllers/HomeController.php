<?php
// Cargar el modelo de Playlist
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Playlist.php';

class HomeController {
    private $db;
    private $playlistModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->playlistModel = new Playlist($this->db);
    }

    public function index() {
        // Obtener todas las playlists para mostrarlas en la página de inicio
        $playlists = $this->playlistModel->readAll();
        // Incluir la vista de inicio del cliente
        require_once __DIR__ . '/../views/client/home.php';
    }
}
?>
