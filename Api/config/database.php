<?php
class Database {
    private $host = "mysql-200-132.mysql.prositehosting.net";
    private $db_name = "nigerland_conference";
    private $username = "Nigerland";
    private $password = "Homeland2024";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo json_encode([
                "success" => false,
                "message" => "Database connection error: " . $exception->getMessage()
            ]);
            exit;
        }
        return $this->conn;
    }
}
?>