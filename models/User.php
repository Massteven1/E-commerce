<?php
namespace Models;

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $role;
    public $is_active;
    public $email_verified;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
        $this->createTableIfNotExists();
    }

    // Crear usuario
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      SET email=:email, password=:password, first_name=:first_name, 
                          last_name=:last_name, role=:role, created_at=NOW()";

            $stmt = $this->conn->prepare($query);

            // Limpiar datos
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->password = htmlspecialchars(strip_tags($this->password));
            $this->first_name = htmlspecialchars(strip_tags($this->first_name));
            $this->last_name = htmlspecialchars(strip_tags($this->last_name));
            $this->role = htmlspecialchars(strip_tags($this->role));

            // Bind valores
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $this->password);
            $stmt->bindParam(":first_name", $this->first_name);
            $stmt->bindParam(":last_name", $this->last_name);
            $stmt->bindParam(":role", $this->role);

            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Error en User::create: " . $e->getMessage());
            return false;
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
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $this->populateFromArray($row);
                return true;
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Error en User::findByEmail: " . $e->getMessage());
            return false;
        }
    }

    // Buscar usuario por ID
    public function findById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND is_active = 1 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $this->populateFromArray($row);
                return true;
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Error en User::findById: " . $e->getMessage());
            return false;
        }
    }

    // Poblar propiedades desde array
    private function populateFromArray($row) {
        $this->id = $row['id'];
        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->first_name = $row['first_name'];
        $this->last_name = $row['last_name'];
        $this->role = $row['role'];
        $this->is_active = $row['is_active'];
        $this->email_verified = $row['email_verified'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }

    // Verificar contraseña
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    // Hash de contraseña
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Verificar si el email existe
    public function emailExists($email) {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("Error en User::emailExists: " . $e->getMessage());
            return false;
        }
    }

    // Actualizar último login
    public function updateLastLogin() {
        try {
            $query = "UPDATE " . $this->table_name . " SET updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error en User::updateLastLogin: " . $e->getMessage());
            return false;
        }
    }

    // Actualizar información del usuario
    public function update() {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET first_name=:first_name, last_name=:last_name, 
                          email=:email, updated_at=CURRENT_TIMESTAMP 
                      WHERE id=:id";

            $stmt = $this->conn->prepare($query);

            // Limpiar datos
            $this->first_name = htmlspecialchars(strip_tags($this->first_name));
            $this->last_name = htmlspecialchars(strip_tags($this->last_name));
            $this->email = htmlspecialchars(strip_tags($this->email));

            // Bind valores
            $stmt->bindParam(":first_name", $this->first_name);
            $stmt->bindParam(":last_name", $this->last_name);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error en User::update: " . $e->getMessage());
            return false;
        }
    }

    // Cambiar contraseña
    public function changePassword($new_password) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET password=:password, updated_at=CURRENT_TIMESTAMP 
                      WHERE id=:id";

            $stmt = $this->conn->prepare($query);
            
            $hashed_password = $this->hashPassword($new_password);
            
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error en User::changePassword: " . $e->getMessage());
            return false;
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

    // Obtener array con datos del usuario (sin contraseña)
    public function toArray($includePassword = false) {
        $data = [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'email_verified' => $this->email_verified,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'full_name' => $this->getFullName()
        ];
        
        if ($includePassword) {
            $data['password'] = $this->password;
        }
        
        return $data;
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
                email_verified TINYINT(1) DEFAULT 0,
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
        } catch (\PDOException $e) {
            error_log("Error al crear la tabla users: " . $e->getMessage());
            return false;
        }
    }
}
?>
