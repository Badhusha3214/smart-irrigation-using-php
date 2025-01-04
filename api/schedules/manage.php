<?php
header('Content-Type: application/json');
require_once '../db_config.php';

$database = new Database();
$conn = $database->getConnection();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'));
        $id = uniqid();
        $stmt = $conn->prepare("INSERT INTO irrigation_schedule (id, schedule_name, start_time, end_time, days_of_week, active) VALUES (?, ?, ?, ?, ?, 'yes')");
        $stmt->bind_param("sssss", $id, $data->schedule_name, $data->start_time, $data->end_time, $data->days_of_week);
        $stmt->execute();
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'));
        $stmt = $conn->prepare("UPDATE irrigation_schedule SET schedule_name=?, start_time=?, end_time=?, days_of_week=? WHERE id=?");
        $stmt->bind_param("sssss", $data->schedule_name, $data->start_time, $data->end_time, $data->days_of_week, $data->id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM irrigation_schedule WHERE id=?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;
}
?>
