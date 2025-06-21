<?php
// Configuración de uploads
class UploadConfig {
    public static function createDirectories() {
        $directories = [
            __DIR__ . '/../uploads',
            __DIR__ . '/../uploads/thumbnails',
            __DIR__ . '/../uploads/video_thumbnails',
            __DIR__ . '/../uploads/videos'
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
                // Crear archivo .htaccess para seguridad
                file_put_contents($dir . '/.htaccess', "Options -Indexes\nDeny from all\n");
            }
        }
    }
    
    public static function handleImageUpload($file, $uploadDir = 'thumbnails') {
        self::createDirectories();
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validar que se subió un archivo
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se subió ningún archivo o hubo un error en la subida');
        }
        
        // Validar tipo de archivo
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten: JPG, PNG, GIF');
        }
        
        // Validar tamaño
        if ($file['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Tamaño máximo: 5MB');
        }
        
        // Validar que sea una imagen real
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new Exception('El archivo no es una imagen válida');
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../uploads/' . $uploadDir . '/';
        $targetFile = $uploadPath . $filename;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return 'uploads/' . $uploadDir . '/' . $filename;
        } else {
            throw new Exception('Error al mover el archivo subido');
        }
    }
    
    public static function handleVideoUpload($file, $uploadDir = 'videos') {
        self::createDirectories();
        
        $allowedTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo'];
        $allowedExtensions = ['mp4', 'avi', 'mov', 'wmv'];
        $maxSize = 100 * 1024 * 1024; // 100MB
        
        // Validar que se subió un archivo
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se subió ningún archivo de video o hubo un error en la subida');
        }
        
        // Obtener extensión del archivo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validar extensión
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten: MP4, AVI, MOV, WMV');
        }
        
        // Validar tamaño
        if ($file['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Tamaño máximo: 100MB');
        }
        
        // Generar nombre único
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../uploads/' . $uploadDir . '/';
        $targetFile = $uploadPath . $filename;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return 'uploads/' . $uploadDir . '/' . $filename;
        } else {
            throw new Exception('Error al mover el archivo de video');
        }
    }
}
?>
