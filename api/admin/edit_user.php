<?php
header('Content-Type: application/json');
session_start();
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

try {
    $data = json_decode(file_get_contents('php://input'));
    
    if (!isset($data->id)) {
        throw new Exception('User ID is required');
    }

    $database = new Database();
    $conn = $database->getConnection();

    $query = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $data->username, $data->email, $data->role, $data->id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update user');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
