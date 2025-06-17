<?php
class Order {
    private $conn;
    private $table_name = "orders";
    
    public $id;
    public $user_id;
    public $transaction_id;
    public $amount;
    public $currency;
    public $status;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Crear un nuevo pedido
    public function create() {
        try {
            // Verificar si la tabla existe
            $tableCheck = $this->conn->prepare("SHOW TABLES LIKE :table_name");
            $tableCheck->bindParam(':table_name', $this->table_name);
            $tableCheck->execute();
            
            if ($tableCheck->rowCount() == 0) {
                // La tabla no existe, crear la tabla
                $this->createTable();
            }
            
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, transaction_id, amount, currency, status, created_at) 
                      VALUES (:user_id, :transaction_id, :amount, :currency, :status, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y sanitizar datos
            $this->user_id = htmlspecialchars(strip_tags($this->user_id));
            $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));
            $this->amount = floatval($this->amount);
            $this->currency = htmlspecialchars(strip_tags($this->currency));
            $this->status = htmlspecialchars(strip_tags($this->status));
            
            // Vincular valores
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':transaction_id', $this->transaction_id);
            $stmt->bindParam(':amount', $this->amount);
            $stmt->bindParam(':currency', $this->currency);
            $stmt->bindParam(':status', $this->status);
            
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            // Si hay un error, registrarlo y devolver false
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }
    
    // Leer un pedido por ID
    public function read($id) {
        try {
            // Verificar si la tabla existe
            $tableCheck = $this->conn->prepare("SHOW TABLES LIKE :table_name");
            $tableCheck->bindParam(':table_name', $this->table_name);
            $tableCheck->execute();
            
            if ($tableCheck->rowCount() == 0) {
                // La tabla no existe, crear la tabla
                $this->createTable();
                return null; // No hay pedido si la tabla no existía
            }
            
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si hay un error, registrarlo y devolver null
            error_log("Error en read: " . $e->getMessage());
            return null;
        }
    }
    
    // Obtener todos los pedidos de un usuario
    public function getOrdersByUserId($user_id) {
        try {
            // Verificar si la tabla existe
            $tableCheck = $this->conn->prepare("SHOW TABLES LIKE :table_name");
            $tableCheck->bindParam(':table_name', $this->table_name);
            $tableCheck->execute();
            
            if ($tableCheck->rowCount() == 0) {
                // La tabla no existe, crear la tabla
                $this->createTable();
                return []; // No hay pedidos si la tabla no existía
            }
            
            $query = "SELECT o.*, 
                             GROUP_CONCAT(p.name SEPARATOR ', ') as courses_purchased
                      FROM " . $this->table_name . " o
                      LEFT JOIN user_courses uc ON o.id = uc.order_id
                      LEFT JOIN playlists p ON uc.playlist_id = p.id
                      WHERE o.user_id = :user_id
                      GROUP BY o.id
                      ORDER BY o.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si hay un error, registrarlo y devolver un array vacío
            error_log("Error en getOrdersByUserId: " . $e->getMessage());
            return [];
        }
    }

    // Método alias para compatibilidad
    public function readByUserId($user_id) {
        return $this->getOrdersByUserId($user_id);
    }
    
    // Actualizar el estado de un pedido
    public function updateStatus($transaction_id, $status) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET status = :status 
                      WHERE transaction_id = :transaction_id";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y sanitizar datos
            $status = htmlspecialchars(strip_tags($status));
            $transaction_id = htmlspecialchars(strip_tags($transaction_id));
            
            // Vincular valores
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':transaction_id', $transaction_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Si hay un error, registrarlo y devolver false
            error_log("Error en updateStatus: " . $e->getMessage());
            return false;
        }
    }
    
    // Crear la tabla si no existe
    private function createTable() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                transaction_id VARCHAR(255) NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                currency VARCHAR(3) NOT NULL,
                status VARCHAR(20) NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY transaction_id (transaction_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al crear la tabla orders: " . $e->getMessage());
            return false;
        }
    }
}
?>
