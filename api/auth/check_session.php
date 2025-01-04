<?php
header('Content-Type: application/json');
session_start();

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'authenticated' => true,
        'role' => $_SESSION['role']
    ]);
} else {
    echo json_encode([
        'authenticated' => false
    ]);
}
?>