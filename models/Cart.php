<?php

require_once dirname(__DIR__) . '/config/database.php';

class Cart {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getCartItems($userId) {
        $this->db->query('SELECT * FROM cart_items WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);

        return $this->db->resultSet();
    }

    public function addItem($userId, $productId, $quantity) {
        $this->db->query('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);
        $this->db->bind(':quantity', $quantity);

        return $this->db->execute();
    }

    public function updateQuantity($userId, $productId, $quantity) {
        $this->db->query('UPDATE cart_items SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id');
        $this->db->bind(':quantity', $quantity);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);

        return $this->db->execute();
    }

    public function removeItem($userId, $productId) {
        $this->db->query('DELETE FROM cart_items WHERE user_id = :user_id AND product_id = :product_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':product_id', $productId);

        return $this->db->execute();
    }

    public function clearCart($userId) {
        $this->db->query('DELETE FROM cart_items WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);

        return $this->db->execute();
    }
}
