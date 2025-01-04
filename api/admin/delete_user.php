a<?php
header('Content-Type: application/json');
session_start();
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

try {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('User ID is required');
    }

    $database = new Database();
    $conn = $database->getConnection();

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("s", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete user');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
