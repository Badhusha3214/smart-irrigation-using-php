<?php
header('Content-Type: application/json');
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$email || !$password) {
        throw new Exception('Missing credentials');
    }

    // Hard-coded admin credentials
    if ($email === 'admin@gmail.com' && $password === 'admin') {
        $_SESSION['is_admin'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Invalid credentials');
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
