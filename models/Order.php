<?php
namespace Models;

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
        $this->createTableIfNotExists();
    }
    
    // Crear un nuevo pedido
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, transaction_id, amount, currency, status, created_at) 
                      VALUES (:user_id, :transaction_id, :amount, :currency, :status, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y sanitizar datos
            $this->user_id = intval($this->user_id);
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
        } catch (\PDOException $e) {
            error_log("Error en Order::create: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener pedido por ID
    public function getOrderById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Order::getOrderById: " . $e->getMessage());
            return null;
        }
    }
    
    // Obtener items de un pedido
    public function getOrderItems($order_id) {
        try {
            $query = "SELECT uc.*, p.name, p.description, p.price, p.cover_image, p.level
                      FROM user_courses uc
                      JOIN playlists p ON uc.playlist_id = p.id
                      WHERE uc.order_id = :order_id
                      ORDER BY uc.created_at ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Order::getOrderItems: " . $e->getMessage());
            return [];
        }
    }
    
    // Leer un pedido por ID
    public function read($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Order::read: " . $e->getMessage());
            return null;
        }
    }
    
    // Obtener todos los pedidos de un usuario
    public function readByUserId($user_id) {
        try {
            $query = "SELECT o.*, 
                             GROUP_CONCAT(p.name SEPARATOR ', ') as courses_purchased,
                             COUNT(uc.playlist_id) as course_count
                      FROM " . $this->table_name . " o
                      LEFT JOIN user_courses uc ON o.id = uc.order_id
                      LEFT JOIN playlists p ON uc.playlist_id = p.id
                      WHERE o.user_id = :user_id
                      GROUP BY o.id
                      ORDER BY o.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Order::readByUserId: " . $e->getMessage());
            return [];
        }
    }
    
    // Buscar pedido por transaction_id
    public function findByTransactionId($transaction_id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE transaction_id = :transaction_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':transaction_id', $transaction_id);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Order::findByTransactionId: " . $e->getMessage());
            return null;
        }
    }
    
    // Actualizar el estado de un pedido
    public function updateStatus($transaction_id, $status) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET status = :status 
                      WHERE transaction_id = :transaction_id";
            
            $stmt = $this->conn->prepare($query);
            
            $status = htmlspecialchars(strip_tags($status));
            $transaction_id = htmlspecialchars(strip_tags($transaction_id));
            
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':transaction_id', $transaction_id);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error en Order::updateStatus: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener estadísticas de pedidos
    public function getOrderStats($user_id = null) {
        try {
            $whereClause = $user_id ? "WHERE user_id = :user_id" : "";
            
            $query = "SELECT 
                        COUNT(*) as total_orders,
                        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_orders
                      FROM " . $this->table_name . " " . $whereClause;
            
            $stmt = $this->conn->prepare($query);
            
            if ($user_id) {
                $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en Order::getOrderStats: " . $e->getMessage());
            return null;
        }
    }
    
    // Crear la tabla si no existe
    private function createTableIfNotExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                transaction_id VARCHAR(255) NOT NULL UNIQUE,
                amount DECIMAL(10,2) NOT NULL,
                currency VARCHAR(3) NOT NULL DEFAULT 'USD',
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_user_id (user_id),
                INDEX idx_transaction_id (transaction_id),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return true;
        } catch (\PDOException $e) {
            error_log("Error al crear la tabla orders: " . $e->getMessage());
            return false;
        }
    }
}
?>
