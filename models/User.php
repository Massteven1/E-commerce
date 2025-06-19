<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use Exception;

class User {
    private $conn;
    private $table_name = "users";
    
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $role;
    public $is_active;
    public $google_id;
    public $created_at;
    public $updated_at;
    public $last_login;
    
    public function __construct($db = null) {
        if ($db === null) {
            $database = new Database();
            $this->conn = $database->getConnection();
        } else {
            $this->conn = $db;
        }
        $this->createTableIfNotExists();
    }
    
    // Crear usuario
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      SET first_name=:first_name, last_name=:last_name, email=:email, 
                          password=:password, role=:role, google_id=:google_id, created_at=NOW()";
            
            $stmt = $this->conn->prepare($query);
            
            $this->first_name = htmlspecialchars(strip_tags($this->first_name));
            $this->last_name = htmlspecialchars(strip_tags($this->last_name));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->password = htmlspecialchars(strip_tags($this->password));
            $this->role = htmlspecialchars(strip_tags($this->role));
            $this->google_id = htmlspecialchars(strip_tags($this->google_id ?? ''));
            
            $stmt->bindParam(":first_name", $this->first_name);
            $stmt->bindParam(":last_name", $this->last_name);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $this->password);
            $stmt->bindParam(":role", $this->role);
            $stmt->bindParam(":google_id", $this->google_id);
            
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error en User::create: " . $e->getMessage());
            return false;
        }
    }
    
    // Leer todos los usuarios
    public function readAll() {
        try {
            $query = "SELECT id, first_name, last_name, email, role, is_active, created_at, last_login 
                      FROM " . $this->table_name . " 
                      ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en User::readAll: " . $e->getMessage());
            return [];
        }
    }
    
    // Buscar usuario por email
    public function findByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email AND is_active = 1 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en User::findByEmail: " . $e->getMessage());
            return false;
        }
    }
    
    // Buscar usuario por ID
    public function findById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND is_active = 1 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en User::findById: " . $e->getMessage());
            return false;
        }
    }
    
    // Verificar si el email existe
    public function emailExists($email) {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en User::emailExists: " . $e->getMessage());
            return false;
        }
    }
    
    // Verificar contraseña
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
    
    // Hash de contraseña
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Actualizar último login
    public function updateLastLogin() {
        try {
            $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en User::updateLastLogin: " . $e->getMessage());
            return false;
        }
    }
    
    // Actualizar usuario
    public function update() {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET first_name=:first_name, last_name=:last_name, email=:email, updated_at=NOW()
                      WHERE id=:id";
            
            $stmt = $this->conn->prepare($query);
            
            $this->first_name = htmlspecialchars(strip_tags($this->first_name));
            $this->last_name = htmlspecialchars(strip_tags($this->last_name));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->id = htmlspecialchars(strip_tags($this->id));
            
            $stmt->bindParam(":first_name", $this->first_name);
            $stmt->bindParam(":last_name", $this->last_name);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":id", $this->id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en User::update: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener estadísticas de usuarios
    public function getStats() {
        try {
            $stats = [];
            
            // Total de usuarios
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE role = 'user'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Usuarios activos
            $query = "SELECT COUNT(*) as active FROM " . $this->table_name . " WHERE role = 'user' AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
            
            // Nuevos usuarios este mes
            $query = "SELECT COUNT(*) as new_users FROM " . $this->table_name . " 
                      WHERE role = 'user' AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                      AND YEAR(created_at) = YEAR(CURRENT_DATE())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['new_users_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['new_users'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de User: " . $e->getMessage());
            return [
                'total_users' => 0,
                'active_users' => 0,
                'new_users_month' => 0
            ];
        }
    }
    
    // Obtener nombre completo
    public function getFullName() {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    // Verificar si es administrador
    public function isAdmin() {
        return $this->role === 'admin';
    }
    
    // Crear la tabla si no existe
    private function createTableIfNotExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INT(11) NOT NULL AUTO_INCREMENT,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                role ENUM('user', 'admin') DEFAULT 'user',
                is_active TINYINT(1) DEFAULT 1,
                google_id VARCHAR(255) NULL,
                last_login DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_email (email),
                INDEX idx_role (role),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return true;
        } catch (Exception $e) {
            error_log("Error al crear la tabla users: " . $e->getMessage());
            return false;
        }
    }
}
?>
