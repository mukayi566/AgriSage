<?php
class Database {
    private $host = "localhost";
    private $db_name = "agrisage_db";
    private $username = "root"; // Change to your MySQL username
    private $password = ""; // Change to your MySQL password
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            echo json_encode(["success" => false, "message" => "Database connection failed"]);
            exit;
        }
        return $this->conn;
    }
}
?>