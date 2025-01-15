<?php
// Database configuration
$host = 'localhost';  // or your database host
$dbname = 'hospital_referral';  // your database name
$username = 'root';  // your database username
$password = '';  // your database password

try {
    // Create PDO connection
    $db = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    return $db;
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    return false;
}
