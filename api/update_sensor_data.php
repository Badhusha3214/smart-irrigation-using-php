<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

require_once 'db_config.php';

class SensorUpdate {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function updateSensorData($data) {
        if (!isset($data->sensor_id) || !isset($data->moisture_level)) {
            return ["success" => false, "error" => "Missing required fields"];
        }

        // Get latest water control status
        $control_query = "SELECT action FROM water_control ORDER BY performed_at DESC LIMIT 1";
        $result = $this->conn->query($control_query);
        $pump_control = false;
        
        if ($row = $result->fetch_assoc()) {
            $pump_control = ($row['action'] === 'ON');
        }

        $id = uniqid();
        $recorded_at = date('Y-m-d H:i:s');

        $query = "INSERT INTO sensor_data (id, sensor_id, moisture_level, recorded_at) 
                 VALUES (?, ?, ?, ?)";
                 
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return ["success" => false, "error" => $this->conn->error];
        }

        $stmt->bind_param("ssis", $id, $data->sensor_id, $data->moisture_level, $recorded_at);
        
        if($stmt->execute()) {
            return [
                "success" => true,
                "message" => "Data saved successfully",
                "pump_control" => $pump_control,
                "data" => [
                    "id" => $id,
                    "sensor_id" => $data->sensor_id,
                    "moisture_level" => $data->moisture_level,
                    "recorded_at" => $recorded_at
                ]
            ];
        }
        return ["success" => false, "error" => $stmt->error];
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $input = file_get_contents("php://input");
    error_log("Received data: " . $input);
    
    $data = json_decode($input);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }
    
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    $sensorUpdate = new SensorUpdate($db);
    $result = $sensorUpdate->updateSensorData($data);
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>