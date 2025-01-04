<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Update path to use api/db_config.php
require_once __DIR__ . '/../db_config.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Update query to handle potential different database name
    $query = "SELECT * FROM users";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode($users);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>