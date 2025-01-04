<?php
header('Content-Type: application/json');
require_once 'db_config.php';

class Schedules {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getAllSchedules() {
        $query = "SELECT * FROM irrigation_schedule ORDER BY start_time";
        $result = $this->conn->query($query);
        
        $schedules = [];
        while($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }
        return $schedules;
    }
    
    public function addSchedule($data) {
        $query = "INSERT INTO irrigation_schedule 
                 (id, schedule_name, start_time, end_time, days_of_week, active)
                 VALUES (?, ?, ?, ?, ?, ?)";
                 
        $stmt = $this->conn->prepare($query);
        $id = uniqid(); // Generate unique ID
        $stmt->bind_param("ssssss", 
            $id,
            $data->schedule_name,
            $data->start_time,
            $data->end_time,
            $data->days_of_week,
            $data->active
        );
        
        if($stmt->execute()) {
            return ["success" => true, "id" => $id];
        }
        return ["success" => false, "error" => $stmt->error];
    }
    
    public function updateSchedule($id, $data) {
        $query = "UPDATE irrigation_schedule 
                 SET schedule_name = ?, 
                     start_time = ?,
                     end_time = ?,
                     days_of_week = ?,
                     active = ?
                 WHERE id = ?";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssss",
            $data->schedule_name,
            $data->start_time,
            $data->end_time,
            $data->days_of_week,
            $data->active,
            $id
        );
        
        if($stmt->execute()) {
            return ["success" => true];
        }
        return ["success" => false, "error" => $stmt->error];
    }
    
    public function deleteSchedule($id) {
        $query = "DELETE FROM irrigation_schedule WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $id);
        
        if($stmt->execute()) {
            return ["success" => true];
        }
        return ["success" => false, "error" => $stmt->error];
    }
    
    public function getScheduleById($id) {
        $query = "SELECT * FROM irrigation_schedule WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
}

// Handle incoming requests
$database = new Database();
$db = $database->getConnection();
$schedules = new Schedules($db);

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? $_GET['id'] : null;

try {
    switch($method) {
        case 'GET':
            if($id) {
                $result = $schedules->getScheduleById($id);
            } else {
                $result = $schedules->getAllSchedules();
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents("php://input"));
            $result = $schedules->addSchedule($data);
            break;
            
        case 'PUT':
            if(!$id) {
                throw new Exception("ID is required for update");
            }
            $data = json_decode(file_get_contents("php://input"));
            $result = $schedules->updateSchedule($id, $data);
            break;
            
        case 'DELETE':
            if(!$id) {
                throw new Exception("ID is required for deletion");
            }
            $result = $schedules->deleteSchedule($id);
            break;
            
        default:
            throw new Exception("Invalid request method");
    }
    
    echo json_encode($result);
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>