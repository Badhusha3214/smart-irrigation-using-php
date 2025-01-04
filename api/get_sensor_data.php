<?php
header('Content-Type: application/json');
require_once 'db_config.php';

class SensorData {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getLatestSensorData() {
        $query = "SELECT * FROM sensor_data ORDER BY recorded_at DESC LIMIT 1";
        $result = $this->conn->query($query);
        
        if($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
}

$database = new Database();
$db = $database->getConnection();
$sensorData = new SensorData($db);

$result = $sensorData->getLatestSensorData();
echo json_encode($result);
?>