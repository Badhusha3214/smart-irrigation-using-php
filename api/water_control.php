<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'));
        $id = uniqid();
        $action = $data->action;
        $performed_at = date('Y-m-d H:i:s');

        // Update sensor_data with pump control flag
        $control_query = "INSERT INTO water_control (id, action, performed_at) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($control_query);
        $stmt->bind_param("sss", $id, $action, $performed_at);
        
        if ($stmt->execute()) {
            // Send response with pump control flag
            echo json_encode([
                'success' => true,
                'action' => $action,
                'performed_at' => $performed_at,
                'pump_control' => ($action === 'ON')
            ]);
        } else {
            throw new Exception('Failed to update water control');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    // GET request - return water control history
    try {
        $result = $conn->query("SELECT * FROM water_control ORDER BY performed_at DESC LIMIT 10");
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        echo json_encode($history);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>