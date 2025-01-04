<?php
header('Content-Type: application/json');
session_start();
require_once '../db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'));
    
    if (!$data || !isset($data->username) || !isset($data->password)) {
        throw new Exception('Missing credentials');
    }

    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // First, check if it's the admin
    if ($data->username === 'admin' && $data->password === 'admin') {
        $_SESSION['is_admin'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        echo json_encode(['success' => true, 'role' => 'admin']);
        exit;
    }

    // For regular users
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $data->username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($data->password, $user['password'])) {
        // Password matches
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['success' => true, 'role' => $user['role']]);
    } else {
        // Invalid credentials
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Login failed: ' . $e->getMessage()
    ]);
}
?>