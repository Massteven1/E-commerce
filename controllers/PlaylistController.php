<?php
class PlaylistController {
  private $db;
  private $playlistModel;
  private $videoModel;
  private $uploadDirs;

  public function __construct() {
      // Cargar dependencias
      require_once __DIR__ . '/../config/Database.php';
      require_once __DIR__ . '/../models/Playlist.php';
      require_once __DIR__ . '/../models/Video.php';

      $database = new Database();
      $this->db = $database->getConnection();
      $this->playlistModel = new Playlist($this->db);
      $this->videoModel = new Video($this->db);

      // Configurar directorios de subida
      $this->uploadDirs = [
          'images' => __DIR__ . '/../uploads/images/',
          'videos' => __DIR__ . '/../uploads/videos/',
          'thumbnails' => __DIR__ . '/../uploads/thumbnails/'
      ];
  }

  public function index() {
      $playlists = $this->playlistModel->readAll();
      require_once __DIR__ . '/../views/admin/courses.php'; // Cambiado de index.php a courses.php
  }

  public function create() {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          return;
      }

      $data = [
          'name' => $_POST['name'] ?? '',
          'description' => $_POST['description'] ?? '',
          'level' => $_POST['level'] ?? 'A1',
          'price' => $_POST['price'] ?? 0.00,
          'cover_image' => null
      ];

      // Manejar subida de imagen
      if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
          $data['cover_image'] = $this->handleImageUpload($_FILES['cover_image'], 'images');
      }

      if ($this->playlistModel->create($data)) {
          $this->redirect('playlist', 'index', null, true);
      } else {
          die("Error al crear la lista en la base de datos.");
      }
  }

  public function edit($id) {
      $playlist = $this->playlistModel->readOne($id);
      if (!$playlist) {
          die("Lista de reproducción no encontrada.");
      }
      require_once __DIR__ . '/../views/admin/edit_playlist.php';
  }

  public function update() {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          return;
      }

      $id = $_POST['id'];
      $current_playlist = $this->playlistModel->readOne($id);
      
      $data = [
          'name' => $_POST['name'] ?? '',
          'description' => $_POST['description'] ?? '',
          'level' => $_POST['level'] ?? 'A1',
          'price' => $_POST['price'] ?? 0.00,
          'cover_image' => $current_playlist['cover_image']
      ];

      // Manejar nueva imagen si se subió
      if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
          // Eliminar imagen anterior
          if ($current_playlist['cover_image']) {
              $this->deleteFile($current_playlist['cover_image']);
          }
          $data['cover_image'] = $this->handleImageUpload($_FILES['cover_image'], 'images');
      }

      if ($this->playlistModel->update($id, $data)) {
          $this->redirect('playlist', 'index', null, true);
      } else {
          die("Error al actualizar la lista.");
      }
  }

  public function delete($id) {
      $playlist = $this->playlistModel->readOne($id);
      if (!$playlist) {
          die("Lista de reproducción no encontrada.");
      }

      // Eliminar archivos asociados
      if ($playlist['cover_image']) {
          $this->deleteFile($playlist['cover_image']);
      }

      // Eliminar videos de la playlist
      $videos = $this->videoModel->readByPlaylist($id);
      foreach ($videos as $video) {
          if ($video['file_path']) {
              $this->deleteFile($video['file_path']);
          }
          if ($video['thumbnail_image']) {
              $this->deleteFile($video['thumbnail_image']);
          }
      }

      $this->videoModel->deleteByPlaylist($id);

      if ($this->playlistModel->delete($id)) {
          $this->redirect('playlist', 'index', null, true);
      } else {
          die("Error al eliminar la lista de reproducción.");
      }
  }

  // Método para la vista de detalles del cliente
  public function viewClientDetail($id) {
      $playlist = $this->playlistModel->readOne($id);
      if (!$playlist) {
          // Redirigir a una página de error o a la página principal si la playlist no existe
          header('Location: index.php'); // O a una página 404
          exit();
      }
      require_once __DIR__ . '/../views/client/course-detail.php';
  }

  private function handleImageUpload($file, $type) {
      $allowedTypes = ['jpeg', 'jpg'];
      if ($type === 'thumbnails') {
          $allowedTypes[] = 'png';
      }

      $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($extension, $allowedTypes)) {
          die("Tipo de archivo no permitido. Solo: " . implode(', ', $allowedTypes));
      }

      $uploadDir = $this->uploadDirs[$type];
      if (!file_exists($uploadDir)) {
          mkdir($uploadDir, 0777, true);
      }

      $filename = time() . '_' . uniqid() . '.' . $extension;
      $targetPath = $uploadDir . $filename;

      if (move_uploaded_file($file['tmp_name'], $targetPath)) {
          return "uploads/{$type}/" . $filename;
      } else {
          die("Error al subir el archivo.");
      }
  }

  private function deleteFile($filePath) {
      $fullPath = __DIR__ . '/../' . $filePath;
      if (file_exists($fullPath)) {
          unlink($fullPath);
      }
  }

  private function redirect($controller, $action, $id = null, $isAdmin = false) {
      $base_url = $isAdmin ? "index.php" : "index.php";
      $url = "{$base_url}?controller={$controller}&action={$action}";
      if ($id) $url .= "&id={$id}";
      header("Location: {$url}");
      exit();
  }
}
?>
