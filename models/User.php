<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;

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
        } catch (\Exception $e) {
            error_log("Error al crear la tabla users: " . $e->getMessage());
            return false;
        }
    }
    
    // Crear usuario - SIMPLIFICADO
    public function create() {
        try {
            // Query simplificada sin google_id para evitar problemas
            $query = "INSERT INTO " . $this->table_name . " 
                      (first_name, last_name, email, password, role, is_active, created_at) 
                      VALUES (?, ?, ?, ?, ?, 1, NOW())";
        
            $stmt = $this->conn->prepare($query);
            
            // Limpiar datos
            $first_name = htmlspecialchars(strip_tags(trim($this->first_name)));
            $last_name = htmlspecialchars(strip_tags(trim($this->last_name)));
            $email = htmlspecialchars(strip_tags(trim($this->email)));
            $password = $this->password; // Ya viene hasheada
            $role = $this->role ?? 'user';
            
            // Log para debugging
            error_log("User::create - Intentando insertar: $first_name, $last_name, $email, role: $role");
            
            // Ejecutar con parámetros posicionales
            $result = $stmt->execute([$first_name, $last_name, $email, $password, $role]);
            
            if ($result) {
                $this->id = $this->conn->lastInsertId();
                error_log("User::create - Usuario creado exitosamente con ID: " . $this->id);
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("User::create - Error en execute(): " . print_r($errorInfo, true));
                return false;
            }
            
        } catch (\Exception $e) {
            error_log("User::create - Excepción: " . $e->getMessage());
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
        } catch (\Exception $e) {
            error_log("Error en User::readAll: " . $e->getMessage());
            return [];
        }
    }
    
    // Buscar usuario por email
    public function findByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return false;
        } catch (\Exception $e) {
            error_log("Error en User::findByEmail: " . $e->getMessage());
            return false;
        }
    }
    
    // Verificar si el email existe
    public function emailExists($email) {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
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
            $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$this->id]);
        } catch (\Exception $e) {
            error_log("Error en User::updateLastLogin: " . $e->getMessage());
            return false;
        }
    }
    
    // Actualizar usuario
    public function update() {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET first_name=?, last_name=?, email=?, updated_at=NOW()
                      WHERE id=?";
            
            $stmt = $this->conn->prepare($query);
            
            $first_name = htmlspecialchars(strip_tags($this->first_name));
            $last_name = htmlspecialchars(strip_tags($this->last_name));
            $email = htmlspecialchars(strip_tags($this->email));
            
            return $stmt->execute([$first_name, $last_name, $email, $this->id]);
        } catch (\Exception $e) {
            error_log("Error en User::update: " . $e->getMessage());
            return false;
        }
    }

    // Cambiar el estado de is_active
    public function toggleStatus() {
        try {
            $query = "UPDATE " . $this->table_name . " SET is_active = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$this->is_active, $this->id]);
        } catch (\Exception $e) {
            error_log("Error en User::toggleStatus: " . $e->getMessage());
            return false;
        }
    }

    // Desactivar (soft delete) un usuario
    public function deactivate() {
        try {
            $query = "UPDATE " . $this->table_name . " SET is_active = 0, updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$this->id]);
        } catch (\Exception $e) {
            error_log("Error en User::deactivate: " . $e->getMessage());
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
        } catch (\Exception $e) {
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
}
?>
