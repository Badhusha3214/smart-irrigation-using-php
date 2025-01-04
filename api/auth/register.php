<?php
header('Content-Type: application/json');
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

$data = json_decode(file_get_contents('php://input'));
$database = new Database();
$conn = $database->getConnection();

$password_hash = password_hash($data->password, PASSWORD_DEFAULT);
$id = uniqid();

$stmt = $conn->prepare("INSERT INTO users (id, username, password, email) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $id, $data->username, $password_hash, $data->email);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

