<?php
class Database {
    private $host = "mysql-200-132.mysql.prositehosting.net";
    private $db_name = "nigerland_conference";
    private $username = "Nigerland";
    private $password = "Homeland2024";
    public $pdo;

    public function getConnection() {
        $this->pdo = null;

        try {
            $this->pdo = new PDO(
                "mysql:host=".$this->host.";dbname=".$this->db_name,
                $this->username,
                $this->password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
        }

        return $this->pdo;
    }
}
?>
