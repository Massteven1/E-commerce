<?php

require_once dirname(__DIR__) . '/config/database.php';

class Order {
    private $conn;
    private $table_name = "orders";

    public $id;
    public $customer_id;
    public $order_date;
    public $total_amount;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new order record
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    customer_id = :customer_id,
                    order_date = :order_date,
                    total_amount = :total_amount";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->customer_id=htmlspecialchars(strip_tags($this->customer_id));
        $this->order_date=htmlspecialchars(strip_tags($this->order_date));
        $this->total_amount=htmlspecialchars(strip_tags($this->total_amount));

        // Bind values
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":order_date", $this->order_date);
        $stmt->bindParam(":total_amount", $this->total_amount);

        // Execute query
        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read all order records
    public function read() {
        $query = "SELECT
                    id, customer_id, order_date, total_amount
                FROM
                    " . $this->table_name . "
                ORDER BY
                    order_date DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

    // Read single order record
    public function readOne() {
        $query = "SELECT
                    id, customer_id, order_date, total_amount
                FROM
                    " . $this->table_name . "
                WHERE
                    id = ?
                LIMIT
                    0,1";

        $stmt = $this->conn->prepare( $query );

        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->customer_id = $row['customer_id'];
        $this->order_date = $row['order_date'];
        $this->total_amount = $row['total_amount'];
    }

    // Update order record
    public function update() {
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                    customer_id = :customer_id,
                    order_date = :order_date,
                    total_amount = :total_amount
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->customer_id=htmlspecialchars(strip_tags($this->customer_id));
        $this->order_date=htmlspecialchars(strip_tags($this->order_date));
        $this->total_amount=htmlspecialchars(strip_tags($this->total_amount));
        $this->id=htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":order_date", $this->order_date);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":id", $this->id);

        // Execute the query
        if($stmt->execute()){
            return true;
        }

        return false;
    }

    // Delete order record
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(1, $this->id);

        if($stmt->execute()){
            return true;
        }

        return false;
    }
}
?>
