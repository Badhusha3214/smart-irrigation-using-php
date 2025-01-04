<?php
class Database {
    private $host = "localhost";
    private $db_name = "smart_irrigation";
    private $username = "root";
    private $password = "";
    private $conn = null;
    
    public function getConnection() {
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            return $this->conn;
        } catch(Exception $e) {
            echo "Connection error: " . $e->getMessage();
            return null;
        }
    }
}
?>