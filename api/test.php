<?php
require_once 'db_config.php';

$database = new Database();
$conn = $database->getConnection();

if($conn) {
    echo "Database Connection Working!";
} else {
    echo "Connection Failed!";
}
?>