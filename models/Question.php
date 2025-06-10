<?php

require_once dirname(__DIR__) . '/config/database.php';

class Question {
    private $conn;
    private $table_name = "questions";

    public $id;
    public $question_text;
    public $correct_answer;
    public $incorrect_answer1;
    public $incorrect_answer2;
    public $incorrect_answer3;
    public $category_id;
    public $difficulty;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Used when filling out the update product form
    function readOne() {
        $query = "SELECT
                    question_text, correct_answer, incorrect_answer1, incorrect_answer2, incorrect_answer3, category_id, difficulty
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

        $this->question_text = $row['question_text'];
        $this->correct_answer = $row['correct_answer'];
        $this->incorrect_answer1 = $row['incorrect_answer1'];
        $this->incorrect_answer2 = $row['incorrect_answer2'];
        $this->incorrect_answer3 = $row['incorrect_answer3'];
        $this->category_id = $row['category_id'];
        $this->difficulty = $row['difficulty'];
    }
}
?>
