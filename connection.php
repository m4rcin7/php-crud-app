<?php

$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = ''; 
$dbName = 'php-crud-app';

$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "Database connection failed.";
    exit(); 
}

?>
