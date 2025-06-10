<?php

require_once dirname(__DIR__) . '/config/database.php';

class Rating {
    private $conn;
    private $table_name = "ratings";

    public $id;
    public $user_id;
    public $product_id;
    public $rating;
    public $comment;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // create rating
    function create() {
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    user_id=:user_id, product_id=:product_id, rating=:rating, comment=:comment, created_at=NOW(), updated_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->user_id=htmlspecialchars(strip_tags($this->user_id));
        $this->product_id=htmlspecialchars(strip_tags($this->product_id));
        $this->rating=htmlspecialchars(strip_tags($this->rating));
        $this->comment=htmlspecialchars(strip_tags($this->comment));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":comment", $this->comment);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // read ratings
    function read() {
        $query = "SELECT
                    id, user_id, product_id, rating, comment, created_at, updated_at
                FROM
                    " . $this->table_name . "
                ORDER BY
                    created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

    // read one rating
    function readOne() {
        $query = "SELECT
                    id, user_id, product_id, rating, comment, created_at, updated_at
                FROM
                    " . $this->table_name . "
                WHERE
                    id = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare( $query );

        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->user_id = $row['user_id'];
        $this->product_id = $row['product_id'];
        $this->rating = $row['rating'];
        $this->comment = $row['comment'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }

    // update rating
    function update() {
        $query = "UPDATE
                    " . $this->table_name . "
                SET
                    user_id=:user_id, product_id=:product_id, rating=:rating, comment=:comment, updated_at=NOW()
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $this->user_id=htmlspecialchars(strip_tags($this->user_id));
        $this->product_id=htmlspecialchars(strip_tags($this->product_id));
        $this->rating=htmlspecialchars(strip_tags($this->rating));
        $this->comment=htmlspecialchars(strip_tags($this->comment));
        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":comment", $this->comment);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // delete rating
    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // read ratings by product id
    function readByProductId($product_id) {
        $query = "SELECT
                    id, user_id, product_id, rating, comment, created_at, updated_at
                FROM
                    " . $this->table_name . "
                WHERE
                    product_id = ?
                ORDER BY
                    created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $product_id);

        $stmt->execute();

        return $stmt;
    }

    // read ratings by user id
    function readByUserId($user_id) {
        $query = "SELECT
                    id, user_id, product_id, rating, comment, created_at, updated_at
                FROM
                    " . $this->table_name . "
                WHERE
                    user_id = ?
                ORDER BY
                    created_at DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $user_id);

        $stmt->execute();

        return $stmt;
    }
}
?>
